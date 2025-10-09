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

        // Group requirements by course and then by root folders
        $pendingFoldersByCourse = $this->groupRequirementsByCourseAndFolders($pendingRequirementsWithCourse);

        return view('livewire.user.dashboard.pending', [
            'pendingFoldersByCourse' => $pendingFoldersByCourse,
            'pendingRequirementsCount' => $pendingRequirementsWithCourse->count()
        ]);
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