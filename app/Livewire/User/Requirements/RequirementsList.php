<?php

namespace App\Livewire\User\Requirements;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Requirement;
use App\Models\RequirementType;
use App\Models\Semester;
use App\Models\SubmittedRequirement;
use App\Models\RequirementSubmissionIndicator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class RequirementsList extends Component
{
    use WithFileUploads;

    public $activeSemester;
    public $selectedCourse = null;
    public $courseRequirements = [];
    public $organizedRequirements = [];
    public $selectedFolder = null;
    public $folderRequirements = [];
    
    // Properties for file upload and modals
    public $file;
    public $submissionNotes = '';
    public $activeTabs = [];
    
    // Properties for delete modal
    public $showDeleteModal = false;
    public $submissionToDelete = null;


    public function mount()
    {
        $this->activeSemester = Semester::getActiveSemester();
    }

    public function selectCourse($courseId)
    {
        $this->selectedCourse = $courseId;
        $this->loadCourseRequirements();
    }

    public function backToCourses()
    {
        if ($this->selectedFolder) {
            // If in folder view, go back to course requirements
            $this->backToCourseRequirements();
        } else {
            // If in course requirements view, go back to courses list
            $this->selectedCourse = null;
            $this->courseRequirements = [];
            $this->organizedRequirements = [];
            $this->reset(['file', 'submissionNotes', 'activeTabs']);
        }
    }

    /**
     * Check if a file type is previewable
     */
    public function isPreviewable($mimeType)
    {
        $previewableMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'text/plain',
        ];
        
        return in_array($mimeType, $previewableMimes);
    }

    /**
     * Set active tab for a requirement
     */
    public function setActiveTab($requirementId, $tabName)
    {
        $this->activeTabs[$requirementId] = $tabName;
    }

    /**
     * Check if a tab is active for a requirement
     */
    public function isTabActive($requirementId, $tabName)
    {
        return isset($this->activeTabs[$requirementId]) && $this->activeTabs[$requirementId] === $tabName;
    }

    /**
     * Confirm deletion of a submission
     */
    public function confirmDelete($submissionId)
    {
        $this->submissionToDelete = $submissionId;
        $this->showDeleteModal = true;
    }

    /**
     * Cancel deletion
     */
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->submissionToDelete = null;
    }

    /**
     * Delete submission
     */
    public function deleteSubmission()
    {
        try {
            if (!$this->submissionToDelete) {
                return;
            }
            
            $submission = SubmittedRequirement::findOrFail($this->submissionToDelete);
            
            // Check if user can delete this submission
            if ($submission->user_id !== Auth::id()) {
                $this->dispatch('showNotification',
                    type: 'error',
                    content: 'You are not authorized to delete this submission.'
                );
                return;
            }
            
            // Check if submission can be deleted (not approved)
            if ($submission->status === SubmittedRequirement::STATUS_APPROVED) {
                $this->dispatch('showNotification',
                    type: 'error',
                    content: 'Approved submissions cannot be deleted.'
                );
                return;
            }
            
            // Delete associated file
            if ($submission->submissionFile) {
                $submission->submissionFile->delete();
            }
            
            // Delete the submission
            $submission->delete();
            
            $this->showDeleteModal = false;
            $this->submissionToDelete = null;
            
            // Reload requirements
            $this->loadCourseRequirements();
            
            $this->dispatch('showNotification',
                type: 'success',
                content: 'Submission deleted successfully.'
            );
            
        } catch (\Exception $e) {
            $this->dispatch('showNotification',
                type: 'error',
                content: 'Failed to delete submission: ' . $e->getMessage()
            );
        }
    }

    /**
     * Submit a requirement
     */
    public function submitRequirement($requirementId)
    {
        $this->validate([
            'file' => 'required|file|max:10240',
            'submissionNotes' => 'nullable|string|max:500',
        ]);

        try {
            $requirement = Requirement::findOrFail($requirementId);
            
            // Create the submission with course_id
            $submittedRequirement = SubmittedRequirement::create([
                'requirement_id' => $requirementId,
                'user_id' => Auth::id(),
                'course_id' => $this->selectedCourse, // Add this line
                'submitted_at' => now(),
                'admin_notes' => $this->submissionNotes,
                'status' => SubmittedRequirement::STATUS_UNDER_REVIEW,
            ]);

            // Add the file
            $submittedRequirement
                ->addMedia($this->file->getRealPath())
                ->usingName($this->file->getClientOriginalName())
                ->usingFileName($this->file->getClientOriginalName())
                ->toMediaCollection('submission_files');

            // Reset form
            $this->reset(['file', 'submissionNotes']);
            
            // Switch to submissions tab
            $this->setActiveTab($requirementId, 'submissions');
            
            // Reload requirements
            $this->loadCourseRequirements();
            
            // Show success message
            $this->dispatch('showNotification', 
                type: 'success',
                content: 'Requirement submitted successfully!'
            );
            
        } catch (\Exception $e) {
            $this->dispatch('showNotification',
                type: 'error',
                content: 'Failed to submit requirement: ' . $e->getMessage()
            );
        }
    }

    /**
     * Load course requirements
     */
    private function loadCourseRequirements()
    {
        if (!$this->selectedCourse || !$this->activeSemester) {
            $this->courseRequirements = [];
            $this->organizedRequirements = [];
            return;
        }

        $userId = Auth::id();
        $user = Auth::user();
        
        // Get all requirements for the active semester
        $allRequirements = Requirement::where('semester_id', $this->activeSemester->id)
            ->with(['userSubmissions' => function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->with('submissionFile')
                      ->orderBy('submitted_at', 'desc');
            }])
            ->with('guides')
            ->orderBy('due', 'asc')
            ->get();

        // Filter requirements where user's college AND department are both present in assigned_to
        $this->courseRequirements = $allRequirements->filter(function ($requirement) use ($user) {
            return $this->isUserAssignedToRequirement($requirement, $user);
        })
        ->map(function ($requirement) use ($userId) {
            // Check if current user has submitted this requirement
            $userSubmitted = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->where('user_id', $userId)
                ->exists();
            
            $userMarkedDone = RequirementSubmissionIndicator::where('requirement_id', $requirement->id)
                ->where('user_id', $userId)
                ->exists();
            
            $requirement->user_has_submitted = $userSubmitted || $userMarkedDone;
            $requirement->user_marked_done = $userMarkedDone;
            
            return $requirement;
        });

        // Organize requirements by folders and standalone requirements
        $this->organizedRequirements = $this->organizeRequirementsByFolders($this->courseRequirements);
    }

    /**
     * Organize requirements into folder structure
     */
    private function organizeRequirementsByFolders($requirements)
    {
        $organized = [
            'folders' => [],
            'standalone' => []
        ];

        foreach ($requirements as $requirement) {
            $hasFolderAssignment = false;
            
            // Check if requirement has requirement_type_ids
            if (!empty($requirement->requirement_type_ids)) {
                $requirementTypes = RequirementType::whereIn('id', $requirement->requirement_type_ids)->get();
                
                foreach ($requirementTypes as $type) {
                    if ($type->is_folder) {
                        // This requirement is directly assigned to a folder
                        if (!isset($organized['folders'][$type->id])) {
                            $organized['folders'][$type->id] = [
                                'folder' => $type,
                                'requirements' => []
                            ];
                        }
                        $organized['folders'][$type->id]['requirements'][] = $requirement;
                        $hasFolderAssignment = true;
                    } else if ($type->parent_id) {
                        // This requirement type has a parent (folder)
                        $parentFolder = RequirementType::find($type->parent_id);
                        if ($parentFolder && $parentFolder->is_folder) {
                            if (!isset($organized['folders'][$parentFolder->id])) {
                                $organized['folders'][$parentFolder->id] = [
                                    'folder' => $parentFolder,
                                    'requirements' => []
                                ];
                            }
                            $organized['folders'][$parentFolder->id]['requirements'][] = $requirement;
                            $hasFolderAssignment = true;
                        }
                    }
                }
            }
            
            // If requirement wasn't assigned to any folder, put it in standalone
            if (!$hasFolderAssignment) {
                $organized['standalone'][] = $requirement;
            }
        }

        return $organized;
    }

    /**
     * Check if user is assigned to a requirement based on college AND department
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

        $colleges = $assignedTo['colleges'] ?? [];
        $departments = $assignedTo['departments'] ?? [];
        $selectAllColleges = $assignedTo['selectAllColleges'] ?? false;
        $selectAllDepartments = $assignedTo['selectAllDepartments'] ?? false;

        // Check if user has college and department
        if (!$user->college_id || !$user->department_id) {
            return false;
        }

        // Convert user IDs to string for comparison
        $userCollegeId = (string)$user->college_id;
        $userDepartmentId = (string)$user->department_id;

        // Check college assignment
        $collegeAssigned = $selectAllColleges || 
                          (is_array($colleges) && in_array($userCollegeId, $colleges));

        // Check department assignment
        $departmentAssigned = $selectAllDepartments ||
                            (is_array($departments) && in_array($userDepartmentId, $departments));

        return $collegeAssigned && $departmentAssigned;
    }

    /**
     * Get assigned courses property
     */
    public function getAssignedCoursesProperty()
    {
        if (!$this->activeSemester) {
            return collect();
        }

        $userId = Auth::id();
        
        return CourseAssignment::where('professor_id', $userId)
            ->where('semester_id', $this->activeSemester->id)
            ->with('course')
            ->get()
            ->pluck('course')
            ->unique('id')
            ->values();
    }

    /**
     * Toggle mark as done/undone for a requirement
     */
    public function toggleMarkAsDone($requirementId)
    {
        try {
            $user = Auth::user();
            
            // Check if user has submitted this requirement
            $submissionExists = SubmittedRequirement::where('requirement_id', $requirementId)
                ->where('user_id', $user->id)
                ->exists();
                
            if (!$submissionExists) {
                $this->dispatch('showNotification',
                    type: 'error',
                    content: 'You need to submit a file first before marking as done.'
                );
                return;
            }
            
            // Check if already marked as done
            $existingIndicator = RequirementSubmissionIndicator::where('requirement_id', $requirementId)
                ->where('user_id', $user->id)
                ->first();
            
            if ($existingIndicator) {
                // Toggle off - mark as undone
                $existingIndicator->delete();
                $message = 'Requirement marked as undone!';
                
                // Delete notification for admins
                $this->deleteNotificationForRequirement($requirementId, $user->id);
                
            } else {
                // Toggle on - mark as done
                RequirementSubmissionIndicator::create([
                    'requirement_id' => $requirementId,
                    'user_id' => $user->id,
                    'submitted_at' => now(),
                ]);
                $message = 'Requirement marked as done!';
                
                // Get the requirement
                $requirement = Requirement::findOrFail($requirementId);
                
                // Get ALL submissions for this requirement by this user
                $submissions = SubmittedRequirement::where('requirement_id', $requirementId)
                    ->where('user_id', $user->id)
                    ->with('media')
                    ->get();
                
                if ($submissions->count() > 0) {
                    // Notify all admins with ALL submissions
                    $admins = User::role('admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\NewSubmissionNotification($requirement, $submissions));
                    }
                }
            }
            
            // Reload requirements to reflect changes
            $this->loadCourseRequirements();
            
            $this->dispatch('showNotification',
                type: 'success',
                content: $message
            );
            
        } catch (\Exception $e) {
            $this->dispatch('showNotification',
                type: 'error',
                content: 'Failed to update status: ' . $e->getMessage()
            );
        }
    }

    /**
     * Delete notification for requirement
     */
    protected function deleteNotificationForRequirement($requirementId, $userId)
    {
        try {
            // Get all admin users
            $admins = User::role('admin')->get();
            
            foreach ($admins as $admin) {
                // Get all notifications for this admin
                $notifications = $admin->notifications()
                    ->where('type', 'App\Notifications\NewSubmissionNotification')
                    ->get();
                
                // Find notifications that match this requirement and user
                foreach ($notifications as $notification) {
                    $data = $notification->data;
                    
                    // Check if this notification is for the same requirement and user
                    if (isset($data['requirement_id']) && 
                        $data['requirement_id'] == $requirementId &&
                        isset($data['user_id']) && 
                        $data['user_id'] == $userId) {
                        
                        // Delete the notification
                        $notification->delete();
                    }
                }
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has any assigned requirements across all courses
     */
    public function getHasAssignedRequirementsProperty()
    {
        if (!$this->activeSemester) {
            return false;
        }

        $user = Auth::user();
        
        $allRequirements = Requirement::where('semester_id', $this->activeSemester->id)->get();
        
        return $allRequirements->contains(function ($requirement) use ($user) {
            return $this->isUserAssignedToRequirement($requirement, $user);
        });
    }

    public function selectFolder($folderId)
    {
        $this->selectedFolder = RequirementType::find($folderId);
        $this->loadFolderRequirements();
    }

    /**
     * Get current course for breadcrumb
     */
    public function getCurrentCourseProperty()
    {
        if (!$this->selectedCourse) {
            return null;
        }
        
        return $this->assignedCourses->firstWhere('id', $this->selectedCourse);
    }

    /**
     * Navigate back to course requirements from folder view
     */
    public function backToCourseRequirements()
    {
        if ($this->selectedFolder) {
            $this->selectedFolder = null;
            $this->folderRequirements = [];
        }
        $this->reset(['file', 'submissionNotes', 'activeTabs']);
    }

    private function loadFolderRequirements()
    {
        if (!$this->selectedFolder || !$this->activeSemester) {
            $this->folderRequirements = [];
            return;
        }

        $userId = Auth::id();
        $user = Auth::user();
        
        // Get all requirements for the active semester
        $allRequirements = Requirement::where('semester_id', $this->activeSemester->id)
            ->with(['userSubmissions' => function($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->with('submissionFile')
                    ->orderBy('submitted_at', 'desc');
            }])
            ->with('guides')
            ->orderBy('due', 'asc')
            ->get();

        // Filter requirements that belong to this folder
        $this->folderRequirements = $allRequirements->filter(function ($requirement) use ($user, $userId) {
            // Check if user is assigned to requirement
            if (!$this->isUserAssignedToRequirement($requirement, $user)) {
                return false;
            }
            
            // Check if requirement belongs to the selected folder
            if (!empty($requirement->requirement_type_ids)) {
                $requirementTypes = RequirementType::whereIn('id', $requirement->requirement_type_ids)->get();
                
                foreach ($requirementTypes as $type) {
                    // Check if requirement is directly in folder or has folder as parent
                    if ($type->id === $this->selectedFolder->id || 
                        ($type->parent_id === $this->selectedFolder->id)) {
                        return true;
                    }
                }
            }
            
            return false;
        })
        ->map(function ($requirement) use ($userId) {
            // Check if current user has submitted this requirement
            $userSubmitted = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->where('user_id', $userId)
                ->exists();
            
            $userMarkedDone = RequirementSubmissionIndicator::where('requirement_id', $requirement->id)
                ->where('user_id', $userId)
                ->exists();
            
            $requirement->user_has_submitted = $userSubmitted || $userMarkedDone;
            $requirement->user_marked_done = $userMarkedDone;
            
            return $requirement;
        });
    }

    public function render()
    {
        return view('livewire.user.requirements.requirements-list', [
            'assignedCourses' => $this->assignedCourses,
            'activeSemester' => $this->activeSemester,
            'hasAssignedRequirements' => $this->hasAssignedRequirements,
        ]);
    }
}