<?php

namespace App\Livewire\user\Dashboard;

use Livewire\Component;
use App\Models\Requirement;
use App\Models\RequirementType;
use App\Models\RequirementSubmissionIndicator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CourseAssignment;

class Pending extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        // Get all pending requirements for active semester
        $allPendingRequirements = Requirement::where('status', 'pending')
            ->whereHas('semester', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('due', 'asc')
            ->get();
        
        // Filter requirements that are assigned to the user based on program assignment
        $assignedRequirements = $allPendingRequirements->filter(function($requirement) use ($user) {
            return $this->isUserAssignedToRequirement($requirement, $user);
        });

        // Get courses assigned to this user (professor)
        $userCourseAssignments = CourseAssignment::where('professor_id', $user->id)
            ->with('course')
            ->whereHas('semester', function($query) {
                $query->where('is_active', true);
            })
            ->get();

        // Get all submission indicators for this user to check which requirements are already submitted per course
        $submittedRequirements = RequirementSubmissionIndicator::where('user_id', $user->id)
            ->get()
            ->keyBy(function($item) {
                return $item->requirement_id . '_' . $item->course_id;
            });

        // Build pending requirements list with course context
        $pendingRequirementsWithCourse = collect();
        
        foreach ($assignedRequirements as $requirement) {
            foreach ($userCourseAssignments as $assignment) {
                // Create unique key for requirement + course combination
                $submissionKey = $requirement->id . '_' . $assignment->course_id;
                
                // Skip if this requirement has already been submitted for this specific course
                if ($submittedRequirements->has($submissionKey)) {
                    continue;
                }
                
                // Add the requirement for each assigned course
                $pendingRequirementsWithCourse->push([
                    'requirement' => $requirement,
                    'course' => $assignment->course,
                    'assignment' => $assignment,
                ]);
            }
        }

        // NEW LOGIC: Filter out Midterm/Finals if TOS+Examination partnership OR Rubrics are submitted
        $filteredPendingRequirements = $this->filterCompletedMidtermFinals($pendingRequirementsWithCourse, $user);

        // Group requirements by course and then by root folders
        $pendingFoldersByCourse = $this->groupRequirementsByCourseAndFolders($filteredPendingRequirements);

        return view('livewire.user.dashboard.pending', [
            'pendingFoldersByCourse' => $pendingFoldersByCourse,
            'pendingRequirementsCount' => $filteredPendingRequirements->count()
        ]);
    }

    /**
     * NEW METHOD: Filter out Midterm/Finals folders if their key requirements are completed
     */
    private function filterCompletedMidtermFinals($pendingRequirements, $user)
    {
        $filtered = collect();
        
        // Group by course first
        $requirementsByCourse = $pendingRequirements->groupBy('course.id');
        
        foreach ($requirementsByCourse as $courseId => $courseRequirements) {
            $course = $courseRequirements->first()['course'];
            
            // Check if Midterm/Finals should be excluded for this course
            $excludeMidterm = $this->shouldExcludeMidtermFinals($courseId, $user, 'midterm');
            $excludeFinals = $this->shouldExcludeMidtermFinals($courseId, $user, 'finals');
            
            foreach ($courseRequirements as $requirementData) {
                $requirement = $requirementData['requirement'];
                
                // Skip if this requirement belongs to Midterm and we should exclude Midterm
                if ($excludeMidterm && $this->isMidtermRequirement($requirement)) {
                    continue;
                }
                
                // Skip if this requirement belongs to Finals and we should exclude Finals
                if ($excludeFinals && $this->isFinalsRequirement($requirement)) {
                    continue;
                }
                
                // Keep all other requirements
                $filtered->push($requirementData);
            }
        }
        
        return $filtered;
    }

    /**
     * Check if we should exclude Midterm/Finals for a course
     */
    private function shouldExcludeMidtermFinals($courseId, $user, $type)
    {
        // Get all requirements for this course (not just pending)
        $allCourseRequirements = Requirement::whereHas('semester', function($query) {
                $query->where('is_active', true);
            })
            ->get()
            ->filter(function($requirement) use ($user) {
                return $this->isUserAssignedToRequirement($requirement, $user);
            });
        
        // Get submission indicators for this course
        $courseSubmitted = RequirementSubmissionIndicator::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->get()
            ->keyBy('requirement_id');
        
        if ($type === 'midterm') {
            // Check Midterm conditions
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
            
        } elseif ($type === 'finals') {
            // Check Finals conditions
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
        
        return false;
    }

    /**
     * Check if requirement belongs to Midterm folder hierarchy
     */
    private function isMidtermRequirement($requirement)
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
    private function isFinalsRequirement($requirement)
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
    private function isTosRequirement($requirement)
    {
        // Check by name or requirement group - adjust as needed
        return str_contains(strtolower($requirement->name), 'tos') || 
               str_contains(strtolower($requirement->requirement_group), 'tos') ||
               $this->isRequirementInTosFolder($requirement);
    }

    /**
     * Check if requirement is an Examinations requirement
     */
    private function isExaminationsRequirement($requirement)
    {
        return str_contains(strtolower($requirement->name), 'examination') || 
               str_contains(strtolower($requirement->requirement_group), 'examination') ||
               $this->isRequirementInExaminationsFolder($requirement);
    }

    /**
     * Check if requirement is a Rubrics requirement
     */
    private function isRubricsRequirement($requirement)
    {
        return str_contains(strtolower($requirement->name), 'rubric') || 
               str_contains(strtolower($requirement->requirement_group), 'rubric') ||
               $this->isRequirementInRubricsFolder($requirement);
    }

    /**
     * Check if requirement is in TOS folder (ID 4 for midterm, ID 8 for finals)
     */
    private function isRequirementInTosFolder($requirement)
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
    private function isRequirementInExaminationsFolder($requirement)
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
    private function isRequirementInRubricsFolder($requirement)
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
     * Group pending requirements by course and then by root folders
     * This creates separate folder entries for each course
     */
    private function groupRequirementsByCourseAndFolders($pendingRequirements)
    {
        // First group by course
        $requirementsByCourse = $pendingRequirements->groupBy('course.id');
        
        $folderGroupsByCourse = collect();

        foreach ($requirementsByCourse as $courseId => $courseRequirements) {
            $course = $courseRequirements->first()['course'];
            
            // Get all root folders (where parent_id is null)
            $rootFolders = RequirementType::where('parent_id', null)
                ->where('is_folder', true)
                ->get();

            foreach ($rootFolders as $folder) {
                // Get requirements that belong to this folder or its subfolders FOR THIS COURSE
                $folderRequirements = $courseRequirements->filter(function($item) use ($folder) {
                    $requirement = $item['requirement'];
                    
                    if (empty($requirement->requirement_type_ids)) {
                        return false;
                    }

                    // Check if requirement belongs to this folder or any of its children
                    return $this->requirementBelongsToFolder($requirement, $folder);
                });

                if ($folderRequirements->count() > 0) {
                    $folderGroupsByCourse->push([
                        'folder' => $folder,
                        'course' => $course,
                        'requirements' => $folderRequirements,
                        'requirements_count' => $folderRequirements->count(),
                        // Get the earliest due date for requirements in this folder+course combination
                        'earliest_due' => $folderRequirements->min('requirement.due')
                    ]);
                }
            }

            // Handle custom requirements (without requirement_type_ids) FOR THIS COURSE
            $customRequirements = $courseRequirements->filter(function($item) {
                $requirement = $item['requirement'];
                return empty($requirement->requirement_type_ids) || 
                       (is_array($requirement->requirement_type_ids) && count($requirement->requirement_type_ids) === 0);
            });

            if ($customRequirements->count() > 0) {
                $folderGroupsByCourse->push([
                    'folder' => (object)[
                        'id' => 'custom_requirements',
                        'name' => 'Other Requirements',
                        'parent_id' => null,
                        'is_folder' => true
                    ],
                    'course' => $course,
                    'requirements' => $customRequirements,
                    'requirements_count' => $customRequirements->count(),
                    'earliest_due' => $customRequirements->min('requirement.due')
                ]);
            }
        }

        return $folderGroupsByCourse;
    }

    /**
     * Check if a requirement belongs to a folder or its subfolders
     */
    private function requirementBelongsToFolder($requirement, $folder)
    {
        if (empty($requirement->requirement_type_ids)) {
            return false;
        }

        // Get all folder IDs in this folder hierarchy (folder + all children)
        $folderHierarchyIds = $this->getFolderHierarchyIds($folder);
        
        // Check if requirement has any type ID that matches the folder hierarchy
        foreach ($requirement->requirement_type_ids as $typeId) {
            if (in_array($typeId, $folderHierarchyIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all folder IDs in a folder hierarchy (including the folder itself and all children)
     */
    private function getFolderHierarchyIds($folder)
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

    /**
     * Check if user is assigned to a requirement based on program assignment
     * Same logic as in RequirementsList component
     */
    private function isUserAssignedToRequirement($requirement, $user)
    {
        $rawAssignedTo = $requirement->getRawOriginal('assigned_to');
        
        if (is_string($rawAssignedTo)) {
            $assignedTo = json_decode($rawAssignedTo, true);
        } else {
            $assignedTo = $requirement->assigned_to;
        }
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $assignedTo = [];
        }

        $programs = $assignedTo['programs'] ?? [];
        $selectAllPrograms = $assignedTo['selectAllPrograms'] ?? false;

        // If requirement is assigned to all programs, check if user teaches any course
        if ($selectAllPrograms) {
            return $this->userTeachesAnyCourseInSemester($user);
        }

        // Convert program IDs to integers for comparison
        $assignedProgramIds = array_map('intval', $programs);

        // Check if user teaches any course that belongs to the assigned programs
        return $this->userTeachesCoursesInPrograms($user, $assignedProgramIds);
    }

    /**
     * Check if user teaches any course in the current semester
     */
    private function userTeachesAnyCourseInSemester($user)
    {
        return CourseAssignment::where('professor_id', $user->id)
            ->whereHas('semester', function($query) {
                $query->where('is_active', true);
            })
            ->exists();
    }

    /**
     * Check if user teaches courses that belong to specific programs
     */
    private function userTeachesCoursesInPrograms($user, $programIds)
    {
        if (empty($programIds)) {
            return false;
        }

        return CourseAssignment::where('professor_id', $user->id)
            ->whereHas('semester', function($query) {
                $query->where('is_active', true);
            })
            ->whereHas('course', function($query) use ($programIds) {
                $query->whereIn('program_id', $programIds);
            })
            ->exists();
    }
}