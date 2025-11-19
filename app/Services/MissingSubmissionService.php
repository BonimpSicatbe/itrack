<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\Requirement;
use App\Models\CourseAssignment;
use App\Models\SubmittedRequirement;
use App\Models\User;
use App\Models\RequirementType;
use App\Models\RequirementSubmissionIndicator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MissingSubmissionService
{
    public function checkMissingSubmissionsForEndedSemesters($force = false)
    {
        if ($force) {
            $endedSemesters = Semester::all();
        } else {
            $endedSemesters = Semester::where('end_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now()->subDays(7))
                ->get();
        }

        $notifications = [];

        foreach ($endedSemesters as $semester) {
            Log::info("Checking semester: {$semester->name} (ID: {$semester->id})");
            
            $missingSubmissions = $this->getMissingSubmissionsForSemester($semester);
            
            Log::info("Found {$missingSubmissions->count()} missing submissions for semester: {$semester->name}");
            
            if ($missingSubmissions->isNotEmpty()) {
                $notifications[] = [
                    'semester' => $semester,
                    'missing_submissions' => $missingSubmissions
                ];
            }
        }

        return $notifications;
    }

    public function getMissingSubmissionsForSemester(Semester $semester)
    {
        Log::info("Getting missing submissions for semester: {$semester->name} (ID: {$semester->id})");

        $requirements = Requirement::where('semester_id', $semester->id)->get();
        
        Log::info("Found {$requirements->count()} requirements for semester {$semester->id}");

        $missingSubmissions = collect();

        foreach ($requirements as $requirement) {
            Log::info("Processing requirement: {$requirement->name} (ID: {$requirement->id})");
            
            $courseAssignments = $this->getCourseAssignmentsForRequirement($requirement, $semester);
            
            Log::info("Found {$courseAssignments->count()} course assignments for requirement {$requirement->id}");

            foreach ($courseAssignments as $assignment) {
                // NEW: Apply partnership logic before checking submission
                if ($this->shouldSkipRequirementDueToPartnership($requirement, $assignment, $semester)) {
                    Log::info("Skipping requirement {$requirement->id} due to partnership completion for user {$assignment->professor_id}, course {$assignment->course_id}");
                    continue;
                }

                $submissionExists = SubmittedRequirement::where('requirement_id', $requirement->id)
                    ->where('user_id', $assignment->professor_id)
                    ->where('course_id', $assignment->course_id)
                    ->exists();

                if (!$submissionExists) {
                    $course = $assignment->course;
                    $user = $assignment->professor;
                    
                    Log::info("Missing submission found: Requirement {$requirement->id}, User {$user->id}, Course {$course->id}");

                    $missingSubmissions->push([
                        'requirement_id' => $requirement->id,
                        'requirement_name' => $requirement->name,
                        'user_id' => $user->id,
                        'user_name' => $user->firstname . ' ' . $user->lastname . ($user->extensionname ? ' ' . $user->extensionname : ''),
                        'user_email' => $user->email,
                        'course_id' => $course->id,
                        'course_code' => $course->course_code,
                        'course_name' => $course->course_name,
                        'due_date' => $requirement->due,
                        'semester_id' => $semester->id,
                        'semester_name' => $semester->name,
                    ]);
                }
            }
        }

        return $missingSubmissions;
    }

    /**
     * NEW METHOD: Apply partnership logic to determine if requirement should be skipped
     */
    protected function shouldSkipRequirementDueToPartnership(Requirement $requirement, CourseAssignment $assignment, Semester $semester)
    {
        $user = $assignment->professor;
        $courseId = $assignment->course_id;
        
        // Check if this requirement belongs to Midterm or Finals
        $isMidtermReq = $this->isMidtermRequirement($requirement);
        $isFinalsReq = $this->isFinalsRequirement($requirement);
        
        if (!$isMidtermReq && !$isFinalsReq) {
            return false; // Not a Midterm/Finals requirement, don't skip
        }
        
        // Check submission indicators for this user and course
        $courseSubmitted = RequirementSubmissionIndicator::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->get()
            ->keyBy('requirement_id');
        
        // Get all requirements for this course and semester to check partnerships
        $allCourseRequirements = Requirement::where('semester_id', $semester->id)
            ->get()
            ->filter(function($req) use ($user, $courseId, $semester) {
                return $this->isUserAssignedToRequirementForCourse($req, $user, $courseId, $semester);
            });
        
        if ($isMidtermReq) {
            return $this->shouldExcludeMidtermForCourse($allCourseRequirements, $courseSubmitted);
        }
        
        if ($isFinalsReq) {
            return $this->shouldExcludeFinalsForCourse($allCourseRequirements, $courseSubmitted);
        }
        
        return false;
    }

    /**
     * NEW METHOD: Check if user is assigned to requirement for specific course
     */
    protected function isUserAssignedToRequirementForCourse(Requirement $requirement, User $user, $courseId, Semester $semester)
    {
        $assignedTo = $requirement->getRawOriginal('assigned_to');
        
        if (is_string($assignedTo)) {
            $assignedTo = json_decode($assignedTo, true);
        }
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $assignedTo = [];
        }

        $programs = $assignedTo['programs'] ?? [];
        $selectAllPrograms = $assignedTo['selectAllPrograms'] ?? false;

        // If requirement is assigned to all programs, check if user teaches the specific course
        if ($selectAllPrograms) {
            return CourseAssignment::where('professor_id', $user->id)
                ->where('course_id', $courseId)
                ->where('semester_id', $semester->id)
                ->exists();
        }

        // Check if user teaches the specific course that belongs to assigned programs
        return CourseAssignment::where('professor_id', $user->id)
            ->where('course_id', $courseId)
            ->where('semester_id', $semester->id)
            ->whereHas('course', function($query) use ($programs) {
                $query->whereIn('program_id', $programs);
            })
            ->exists();
    }

    /**
     * NEW METHOD: Check if Midterm should be excluded for specific course
     */
    protected function shouldExcludeMidtermForCourse($allCourseRequirements, $courseSubmitted)
    {
        $midtermTosSubmitted = false;
        $midtermExaminationsSubmitted = false;
        $midtermRubricsSubmitted = false;
        
        foreach ($allCourseRequirements as $requirement) {
            if ($this->isMidtermRequirement($requirement)) {
                if ($this->isTosRequirement($requirement)) {
                    $midtermTosSubmitted = $courseSubmitted->has($requirement->id);
                } elseif ($this->isExaminationsRequirement($requirement)) {
                    $midtermExaminationsSubmitted = $courseSubmitted->has($requirement->id);
                } elseif ($this->isRubricsRequirement($requirement)) {
                    $midtermRubricsSubmitted = $courseSubmitted->has($requirement->id);
                }
            }
        }
        
        // Exclude Midterm if: (TOS AND Examinations submitted) OR (Rubrics submitted)
        return ($midtermTosSubmitted && $midtermExaminationsSubmitted) || $midtermRubricsSubmitted;
    }

    /**
     * NEW METHOD: Check if Finals should be excluded for specific course
     */
    protected function shouldExcludeFinalsForCourse($allCourseRequirements, $courseSubmitted)
    {
        $finalsTosSubmitted = false;
        $finalsExaminationsSubmitted = false;
        $finalsRubricsSubmitted = false;
        
        foreach ($allCourseRequirements as $requirement) {
            if ($this->isFinalsRequirement($requirement)) {
                if ($this->isTosRequirement($requirement)) {
                    $finalsTosSubmitted = $courseSubmitted->has($requirement->id);
                } elseif ($this->isExaminationsRequirement($requirement)) {
                    $finalsExaminationsSubmitted = $courseSubmitted->has($requirement->id);
                } elseif ($this->isRubricsRequirement($requirement)) {
                    $finalsRubricsSubmitted = $courseSubmitted->has($requirement->id);
                }
            }
        }
        
        // Exclude Finals if: (TOS AND Examinations submitted) OR (Rubrics submitted)
        return ($finalsTosSubmitted && $finalsExaminationsSubmitted) || $finalsRubricsSubmitted;
    }

    /**
     * Check if requirement belongs to Midterm folder hierarchy
     */
    protected function isMidtermRequirement($requirement)
    {
        if (empty($requirement->requirement_type_ids)) {
            return false;
        }
        
        // Midterm folder ID is 3, get all its sub-folder IDs
        $midtermFolder = RequirementType::find(3);
        if (!$midtermFolder) {
            return false;
        }
        
        $midtermHierarchyIds = $this->getFolderHierarchyIds($midtermFolder);
        
        foreach ($requirement->requirement_type_ids as $typeId) {
            if (in_array($typeId, $midtermHierarchyIds)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if requirement belongs to Finals folder hierarchy
     */
    protected function isFinalsRequirement($requirement)
    {
        if (empty($requirement->requirement_type_ids)) {
            return false;
        }
        
        // Finals folder ID is 7, get all its sub-folder IDs
        $finalsFolder = RequirementType::find(7);
        if (!$finalsFolder) {
            return false;
        }
        
        $finalsHierarchyIds = $this->getFolderHierarchyIds($finalsFolder);
        
        foreach ($requirement->requirement_type_ids as $typeId) {
            if (in_array($typeId, $finalsHierarchyIds)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if requirement is a TOS requirement
     */
    protected function isTosRequirement($requirement)
    {
        // Check by name or requirement group - adjust as needed
        return str_contains(strtolower($requirement->name), 'tos') || 
               str_contains(strtolower($requirement->requirement_group), 'tos') ||
               $this->isRequirementInTosFolder($requirement);
    }

    /**
     * Check if requirement is an Examinations requirement
     */
    protected function isExaminationsRequirement($requirement)
    {
        return str_contains(strtolower($requirement->name), 'examination') || 
               str_contains(strtolower($requirement->requirement_group), 'examination') ||
               $this->isRequirementInExaminationsFolder($requirement);
    }

    /**
     * Check if requirement is a Rubrics requirement
     */
    protected function isRubricsRequirement($requirement)
    {
        return str_contains(strtolower($requirement->name), 'rubric') || 
               str_contains(strtolower($requirement->requirement_group), 'rubric') ||
               $this->isRequirementInRubricsFolder($requirement);
    }

    /**
     * Check if requirement is in TOS folder (ID 4 for midterm, ID 8 for finals)
     */
    protected function isRequirementInTosFolder($requirement)
    {
        if (empty($requirement->requirement_type_ids)) {
            return false;
        }
        
        $tosFolderIds = [4, 8]; // TOS folder IDs from your database
        foreach ($requirement->requirement_type_ids as $typeId) {
            if (in_array($typeId, $tosFolderIds)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if requirement is in Examinations folder (ID 6 for midterm, ID 10 for finals)
     */
    protected function isRequirementInExaminationsFolder($requirement)
    {
        if (empty($requirement->requirement_type_ids)) {
            return false;
        }
        
        $examinationsFolderIds = [6, 10]; // Examinations folder IDs from your database
        foreach ($requirement->requirement_type_ids as $typeId) {
            if (in_array($typeId, $examinationsFolderIds)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if requirement is in Rubrics folder (ID 5 for midterm, ID 9 for finals)
     */
    protected function isRequirementInRubricsFolder($requirement)
    {
        if (empty($requirement->requirement_type_ids)) {
            return false;
        }
        
        $rubricsFolderIds = [5, 9]; // Rubrics folder IDs from your database
        foreach ($requirement->requirement_type_ids as $typeId) {
            if (in_array($typeId, $rubricsFolderIds)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get all folder IDs in a folder hierarchy (including the folder itself and all children)
     */
    protected function getFolderHierarchyIds($folder)
    {
        $ids = [$folder->id];
        
        // Recursively get all child folder IDs
        $childFolders = RequirementType::where('parent_id', $folder->id)
            ->where('is_folder', true)
            ->get();
            
        foreach ($childFolders as $childFolder) {
            $ids = array_merge($ids, $this->getFolderHierarchyIds($childFolder));
        }
        
        return $ids;
    }

    protected function getCourseAssignmentsForRequirement(Requirement $requirement, Semester $semester)
    {
        $assignedTo = $requirement->assigned_to;
        
        // Handle JSON format in assigned_to column
        if (is_string($assignedTo)) {
            $decoded = json_decode($assignedTo, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $assignedTo = $decoded;
            }
        }

        Log::info("Requirement {$requirement->id} assigned_to:", [$assignedTo]);

        // Determine assignment type based on the JSON structure
        if (is_array($assignedTo)) {
            if (isset($assignedTo['programs']) || isset($assignedTo['selectAllPrograms'])) {
                // This is a program-based assignment
                if (isset($assignedTo['selectAllPrograms']) && $assignedTo['selectAllPrograms'] === true) {
                    Log::info("Requirement {$requirement->id} assigned to all programs");
                    return $this->getAllCourseAssignmentsForSemester($semester);
                } elseif (isset($assignedTo['programs']) && is_array($assignedTo['programs'])) {
                    Log::info("Requirement {$requirement->id} assigned to specific programs: " . implode(', ', $assignedTo['programs']));
                    return $this->getCourseAssignmentsByPrograms($semester, $assignedTo['programs']);
                }
            }
        }

        // Fallback: if we can't determine the assignment type, get all assignments
        Log::info("Requirement {$requirement->id} - using fallback: get all assignments");
        return $this->getAllCourseAssignmentsForSemester($semester);
    }

    protected function getAllCourseAssignmentsForSemester(Semester $semester)
    {
        $assignments = CourseAssignment::with(['professor', 'course'])
            ->where('semester_id', $semester->id)
            ->whereHas('professor', function($query) {
                $query->where('is_active', true);
            })
            ->get();

        Log::info("All course assignments query for semester {$semester->id}: found {$assignments->count()}");

        // Debug: Log each assignment
        foreach ($assignments as $assignment) {
            Log::info("Assignment: Professor {$assignment->professor_id}, Course {$assignment->course_id}, Active: " . ($assignment->professor->is_active ? 'Yes' : 'No'));
        }

        return $assignments;
    }

    protected function getCourseAssignmentsByPrograms(Semester $semester, array $programIds)
    {
        $assignments = CourseAssignment::with(['professor', 'course.program'])
            ->where('semester_id', $semester->id)
            ->whereHas('professor', function($query) {
                $query->where('is_active', true);
            })
            ->whereHas('course', function($query) use ($programIds) {
                $query->whereIn('program_id', $programIds);
            })
            ->get();

        Log::info("Program-based assignments for semester {$semester->id}, programs [" . implode(', ', $programIds) . "]: found {$assignments->count()}");

        return $assignments;
    }

    protected function getCourseAssignmentsByProgram(Semester $semester)
    {
        // This method might not be needed anymore, but keeping it for compatibility
        return $this->getAllCourseAssignmentsForSemester($semester);
    }

    // Enhanced debug method
    public function debugSemesterCheck(Semester $semester)
    {
        $debugInfo = [
            'semester' => $semester->name,
            'semester_id' => $semester->id,
            'start_date' => $semester->start_date,
            'end_date' => $semester->end_date,
            'is_active' => $semester->is_active,
            'requirements_count' => 0,
            'course_assignments_count' => 0,
            'missing_submissions' => 0,
            'details' => []
        ];

        $requirements = Requirement::where('semester_id', $semester->id)->get();
        $debugInfo['requirements_count'] = $requirements->count();

        Log::info("Debug: Found {$requirements->count()} requirements for semester {$semester->id}");

        foreach ($requirements as $requirement) {
            $assignedTo = $requirement->assigned_to;
            if (is_string($assignedTo)) {
                $decoded = json_decode($assignedTo, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $assignedTo = $decoded;
                }
            }

            $courseAssignments = $this->getCourseAssignmentsForRequirement($requirement, $semester);
            $debugInfo['course_assignments_count'] += $courseAssignments->count();

            $requirementInfo = [
                'requirement_id' => $requirement->id,
                'requirement' => $requirement->name,
                'assigned_to_raw' => $requirement->assigned_to,
                'assigned_to_parsed' => $assignedTo,
                'due_date' => $requirement->due,
                'course_assignments_count' => $courseAssignments->count(),
                'missing_for_assignments' => []
            ];

            foreach ($courseAssignments as $assignment) {
                // Apply partnership logic here too for debugging
                $shouldSkip = $this->shouldSkipRequirementDueToPartnership($requirement, $assignment, $semester);
                
                $submissionExists = SubmittedRequirement::where('requirement_id', $requirement->id)
                    ->where('user_id', $assignment->professor_id)
                    ->where('course_id', $assignment->course_id)
                    ->exists();

                if (!$submissionExists && !$shouldSkip) {
                    $requirementInfo['missing_for_assignments'][] = [
                        'assignment_id' => $assignment->assignment_id,
                        'professor' => $assignment->professor->firstname . ' ' . $assignment->professor->lastname,
                        'professor_id' => $assignment->professor_id,
                        'professor_active' => $assignment->professor->is_active,
                        'course' => $assignment->course->course_code . ' ' . $assignment->course->course_name,
                        'course_id' => $assignment->course_id,
                        'program_id' => $assignment->course->program_id ?? null,
                        'program_name' => $assignment->course->program->program_name ?? 'No Program',
                        'skipped_by_partnership' => $shouldSkip
                    ];
                    $debugInfo['missing_submissions']++;
                }
            }

            $debugInfo['details'][] = $requirementInfo;
        }

        return $debugInfo;
    }

    public function getMissingSubmissionsSummary(Semester $semester)
    {
        $missingSubmissions = $this->getMissingSubmissionsForSemester($semester);
        
        return [
            'total_missing' => $missingSubmissions->count(),
            'by_requirement' => $missingSubmissions->groupBy('requirement_name')->map->count(),
            'by_user' => $missingSubmissions->groupBy('user_name')->map->count(),
            'by_course' => $missingSubmissions->groupBy('course_code')->map->count(),
        ];
    }
}