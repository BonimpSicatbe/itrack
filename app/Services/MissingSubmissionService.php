<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\Requirement;
use App\Models\CourseAssignment;
use App\Models\SubmittedRequirement;
use App\Models\User;
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
                $submissionExists = SubmittedRequirement::where('requirement_id', $requirement->id)
                    ->where('user_id', $assignment->professor_id)
                    ->where('course_id', $assignment->course_id)
                    ->exists();

                if (!$submissionExists) {
                    $requirementInfo['missing_for_assignments'][] = [
                        'assignment_id' => $assignment->assignment_id,
                        'professor' => $assignment->professor->firstname . ' ' . $assignment->professor->lastname,
                        'professor_id' => $assignment->professor_id,
                        'professor_active' => $assignment->professor->is_active,
                        'course' => $assignment->course->course_code . ' ' . $assignment->course->course_name,
                        'course_id' => $assignment->course_id,
                        'program_id' => $assignment->course->program_id ?? null,
                        'program_name' => $assignment->course->program->program_name ?? 'No Program'
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