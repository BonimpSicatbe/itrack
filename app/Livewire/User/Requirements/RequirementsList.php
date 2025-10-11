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
use Livewire\Attributes\Url as LivewireUrl;

class RequirementsList extends Component
{
    use WithFileUploads;

    public $activeSemester;
    
    #[LivewireUrl(as: 'course', history: true, keep: false)]
    public $selectedCourse = null;
    
    #[LivewireUrl(as: 'folder', history: true, keep: false)]
    public $selectedFolder = null;
    
    #[LivewireUrl(as: 'subfolder', history: true, keep: false)]
    public $selectedSubFolder = null;

    public $courseRequirements = [];
    public $organizedRequirements = [];
    public $folderRequirements = [];
    public $folderStructure = [];
    
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
        
        // Manually get URL parameters if Livewire URL attributes don't work
        $request = request();
        $this->selectedCourse = $request->query('course', $this->selectedCourse);
        $this->selectedFolder = $request->query('folder', $this->selectedFolder);
        $this->selectedSubFolder = $request->query('subfolder', $this->selectedSubFolder);
        
        \Log::info('RequirementsList mount with manual URL handling', [
            'selectedCourse' => $this->selectedCourse,
            'selectedFolder' => $this->selectedFolder,
            'selectedSubFolder' => $this->selectedSubFolder,
            'query_params' => $request->query()
        ]);
        
        // Handle URL parameters for direct navigation from Pending page
        if ($this->selectedCourse) {
            if ($this->selectedSubFolder) {
                \Log::info('Loading sub-folder requirements from URL parameters');
                $this->loadSubFolderRequirements();
            } else if ($this->selectedFolder) {
                // Check if this folder has children (sub-folders)
                $folder = RequirementType::find($this->selectedFolder);
                $hasChildren = $folder && $folder->children()->where('is_folder', true)->exists();
                
                if ($hasChildren) {
                    \Log::info('Folder has children, loading course requirements to show sub-folders');
                    // For parent folders with children, load course requirements to show the sub-folder grid
                    $this->loadCourseRequirements();
                } else {
                    \Log::info('Folder has no children, loading folder requirements directly');
                    // For folders without children, load requirements directly
                    $this->loadFolderRequirements();
                }
            } else {
                \Log::info('Loading course requirements from URL parameters');
                $this->loadCourseRequirements();
            }
        }
        // If only folder/subfolder is provided without course, ignore them
        else if ($this->selectedFolder || $this->selectedSubFolder) {
            \Log::info('Ignoring folder parameters without course');
            $this->selectedFolder = null;
            $this->selectedSubFolder = null;
        } else {
            \Log::info('No URL parameters found, showing course selection');
        }
    }


    public function selectCourse($courseId)
    {
        $this->selectedCourse = $courseId;
        $this->selectedFolder = null;
        $this->selectedSubFolder = null;
        $this->loadCourseRequirements();
    }

    public function backToCourses()
    {
        // Reset everything to default view
        $this->selectedCourse = null;
        $this->selectedFolder = null;
        $this->selectedSubFolder = null;
        $this->courseRequirements = [];
        $this->organizedRequirements = [];
        $this->folderRequirements = [];
        $this->reset(['file', 'submissionNotes', 'activeTabs']);
    }

    /**
     * Select a root folder
     */
    public function selectFolder($folderId)
    {
        $this->selectedFolder = $folderId;
        $this->selectedSubFolder = null;
        $this->loadFolderRequirements();
    }

    /**
     * Select a sub-folder
     */
    public function selectSubFolder($subFolderId)
    {
        $this->selectedSubFolder = $subFolderId;
        $this->loadSubFolderRequirements();
    }

    /**
     * Navigate back to parent folder from sub-folder view
     */
    public function backToParentFolder()
    {
        if ($this->selectedSubFolder) {
            $this->selectedSubFolder = null;
            $this->loadFolderRequirements(); // This shows the parent folder with its children
        }
        $this->reset(['file', 'submissionNotes', 'activeTabs']);
    }

    /**
     * Navigate back to course requirements from folder view
     */
    public function backToCourseRequirements()
    {
        if ($this->selectedFolder || $this->selectedSubFolder) {
            $this->selectedFolder = null;
            $this->selectedSubFolder = null;
            $this->loadCourseRequirements(); // This shows the folder grid for the course
        }
        $this->reset(['file', 'submissionNotes', 'activeTabs']);
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
        $submission = SubmittedRequirement::find($submissionId);
        
        // Check if requirement is marked as done for this course
        if ($submission) {
            $isMarkedDone = RequirementSubmissionIndicator::where('requirement_id', $submission->requirement_id)
                ->where('user_id', Auth::id())
                ->where('course_id', $this->selectedCourse)
                ->exists();
                
            if ($isMarkedDone) {
                $this->dispatch('showNotification',
                    type: 'error',
                    content: 'Cannot delete submission. Requirement is marked as done.'
                );
                return;
            }
        }
        
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
            
            // Check if requirement is marked as done for this course
            $isMarkedDone = RequirementSubmissionIndicator::where('requirement_id', $submission->requirement_id)
                ->where('user_id', Auth::id())
                ->where('course_id', $this->selectedCourse)
                ->exists();
                
            if ($isMarkedDone) {
                $this->dispatch('showNotification',
                    type: 'error',
                    content: 'Cannot delete submission. Requirement is marked as done.'
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
            
            // Reload requirements based on current view
            if ($this->selectedSubFolder) {
                $this->loadSubFolderRequirements();
            } else if ($this->selectedFolder) {
                $this->loadFolderRequirements();
            } else {
                $this->loadCourseRequirements();
            }
            
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
                'course_id' => $this->selectedCourse, // This is crucial - link to specific course
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
            
            // Reload requirements to reflect changes
            if ($this->selectedSubFolder) {
                $this->loadSubFolderRequirements();
            } else if ($this->selectedFolder) {
                $this->loadFolderRequirements();
            } else {
                $this->loadCourseRequirements();
            }
            
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
     * Check if user is assigned to a requirement based on program assignment
     * through the chain: requirement(programs) → program → course → course_assignment → user
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
        if (!$this->activeSemester) {
            return false;
        }

        return CourseAssignment::where('professor_id', $user->id)
            ->where('semester_id', $this->activeSemester->id)
            ->exists();
    }

    /**
     * Check if user teaches courses that belong to specific programs
     */
    private function userTeachesCoursesInPrograms($user, $programIds)
    {
        if (!$this->activeSemester || empty($programIds)) {
            return false;
        }

        return CourseAssignment::where('professor_id', $user->id)
            ->where('semester_id', $this->activeSemester->id)
            ->whereHas('course', function($query) use ($programIds) {
                $query->whereIn('program_id', $programIds);
            })
            ->exists();
    }

    /**
     * Check if requirement can be marked as done (partnership validation)
     */
    public function canMarkAsDone($requirementId)
    {
        $requirement = Requirement::find($requirementId);
        if (!$requirement) {
            return false;
        }

        $userSubmitted = $this->hasUserSubmittedRequirement($requirementId, Auth::id(), $this->selectedCourse);
        $userMarkedDone = RequirementSubmissionIndicator::where('requirement_id', $requirementId)
            ->where('user_id', Auth::id())
            ->where('course_id', $this->selectedCourse)
            ->exists();

        \Log::info("Checking if requirement can be marked as done - FRESH CHECK", [
            'requirement_id' => $requirementId,
            'requirement_name' => $requirement->name,
            'requirement_group' => $requirement->requirement_group,
            'is_part_of_partnership' => $requirement->isPartOfPartnership(),
            'user_has_submitted' => $userSubmitted,
            'user_marked_done' => $userMarkedDone,
        ]);

        // If requirement is not part of partnership, user can mark as done if they have submitted
        if (!$requirement->isPartOfPartnership()) {
            \Log::info("Requirement is NOT part of partnership, checking user_has_submitted", [
                'requirement_id' => $requirementId,
                'user_has_submitted' => $userSubmitted,
                'result' => $userSubmitted
            ]);
            return $userSubmitted;
        }

        // For partnership requirements (TOS/Examinations), check if all partners are submitted
        $allPartnersSubmitted = $requirement->areAllPartnersSubmitted(Auth::id(), $this->selectedCourse);
        
        \Log::info("Requirement IS part of partnership, checking all partners submitted", [
            'requirement_id' => $requirementId,
            'all_partners_submitted' => $allPartnersSubmitted,
            'result' => $allPartnersSubmitted
        ]);
        
        return $allPartnersSubmitted;
    }

    /**
     * Get partnership status for a requirement
     */
    public function getPartnershipStatus($requirement)
    {
        if (!$requirement->isPartOfPartnership()) {
            return null;
        }

        $partners = $requirement->getPartnerRequirements($this->activeSemester->id);
        
        // Only return partnership status if there are actual partners
        if ($partners->isEmpty()) {
            return null;
        }

        $submittedPartners = $partners->filter(function($partner) {
            return $this->hasUserSubmittedRequirement($partner->id, Auth::id(), $this->selectedCourse);
        });

        return [
            'total_partners' => $partners->count(),
            'submitted_partners' => $submittedPartners->count(),
            'all_submitted' => $submittedPartners->count() === $partners->count(),
            'partners' => $partners->map(function($partner) {
                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'submitted' => $this->hasUserSubmittedRequirement($partner->id, Auth::id(), $this->selectedCourse)
                ];
            })
        ];
    }

    /**
     * Check if user has submitted a specific requirement for a course
     */
    private function hasUserSubmittedRequirement($requirementId, $userId, $courseId)
    {
        $hasSubmission = SubmittedRequirement::where('requirement_id', $requirementId)
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
            
        return $hasSubmission;
    }

    /**
     * Check if user has marked requirement as done
     */
    private function hasUserMarkedDone($requirementId, $userId, $courseId)
    {
        return RequirementSubmissionIndicator::where('requirement_id', $requirementId)
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
    }

    /**
     * Load course requirements and build folder structure
     */
    private function loadCourseRequirements()
    {
        if (!$this->selectedCourse || !$this->activeSemester) {
            $this->courseRequirements = [];
            $this->organizedRequirements = [];
            $this->folderStructure = [];
            return;
        }

        $userId = Auth::id();
        $user = Auth::user();
        
        // Get all requirements for the active semester
        $allRequirements = Requirement::where('semester_id', $this->activeSemester->id)
            ->with(['userSubmissions' => function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('course_id', $this->selectedCourse)
                      ->with('submissionFile')
                      ->orderBy('submitted_at', 'desc');
            }])
            ->with('guides')
            ->orderBy('due', 'asc')
            ->get();

        // Filter requirements where user is assigned via program chain
        $this->courseRequirements = $allRequirements->filter(function ($requirement) use ($user) {
            return $this->isUserAssignedToRequirement($requirement, $user);
        })
        ->map(function ($requirement) use ($userId) {
            $userSubmitted = $this->hasUserSubmittedRequirement($requirement->id, $userId, $this->selectedCourse);
            $userMarkedDone = $this->hasUserMarkedDone($requirement->id, $userId, $this->selectedCourse);
            
            // Create a data array instead of setting dynamic properties
            $requirementData = [
                'requirement' => $requirement,
                'user_has_submitted' => $userSubmitted || $userMarkedDone,
                'user_marked_done' => $userMarkedDone,
                'can_mark_done' => $this->canMarkAsDone($requirement->id),
                'partnership_status' => $this->getPartnershipStatus($requirement),
            ];
            
            \Log::info("Processed requirement for course view", [
                'requirement_id' => $requirement->id,
                'name' => $requirement->name,
                'requirement_group' => $requirement->requirement_group,
                'user_has_submitted' => $requirementData['user_has_submitted'],
                'user_marked_done' => $requirementData['user_marked_done'],
                'can_mark_done' => $requirementData['can_mark_done']
            ]);
            
            return $requirementData;
        });

        // Build folder structure based on new logic
        $this->folderStructure = $this->buildFolderStructure($this->courseRequirements);
    }

    /**
     * Build hierarchical folder structure
     */
    private function buildFolderStructure($requirements)
    {
        // Get all folders (requirement types that are folders)
        $allFolders = RequirementType::where('is_folder', true)
            ->with('children')
            ->get()
            ->keyBy('id');

        // Build the folder tree
        $rootFolders = $allFolders->where('parent_id', null);
        
        $folderStructure = [];
        
        foreach ($rootFolders as $rootFolder) {
            $folderData = [
                'folder' => $rootFolder,
                'requirements' => [],
                'children' => []
            ];
            
            // Check if this root folder has children
            $hasChildren = $rootFolder->children->where('is_folder', true)->isNotEmpty();
            
            if ($hasChildren) {
                // Folder has children - check each child folder for requirements
                $hasRequirementsInChildren = false;
                
                foreach ($rootFolder->children->where('is_folder', true) as $childFolder) {
                    $childRequirements = $this->getRequirementsForFolder($requirements, $childFolder->id);
                    
                    if (count($childRequirements) > 0) {
                        $hasRequirementsInChildren = true;
                        $childFolderData = [
                            'folder' => $childFolder,
                            'requirements' => $childRequirements
                        ];
                        
                        $folderData['children'][] = $childFolderData;
                    }
                }
                
                // Only add this root folder if it has children with requirements
                if ($hasRequirementsInChildren) {
                    $folderStructure[] = $folderData;
                }
            } else {
                // Folder has no children - show requirements directly
                $directRequirements = $this->getRequirementsForFolder($requirements, $rootFolder->id);
                
                // Only add this folder if it has direct requirements
                if (count($directRequirements) > 0) {
                    $folderData['requirements'] = $directRequirements;
                    $folderStructure[] = $folderData;
                }
            }
        }
        
        // Handle custom requirements (without requirement_type_ids or with empty array)
        $customRequirements = $requirements->filter(function($requirementData) {
            $requirement = $requirementData['requirement'];
            return empty($requirement->requirement_type_ids) || 
                (is_array($requirement->requirement_type_ids) && count($requirement->requirement_type_ids) === 0);
        });
        
        // Only add custom folder if it has requirements
        if ($customRequirements->isNotEmpty()) {
            $folderStructure[] = [
                'folder' => (object)[
                    'id' => 'custom_requirements',
                    'name' => 'Other Requirements',
                    'parent_id' => null,
                    'is_folder' => true
                ],
                'requirements' => $customRequirements->values()->all(),
                'children' => []
            ];
        }
        
        return $folderStructure;
    }

    /**
     * Get requirements for a specific folder
     */
    private function getRequirementsForFolder($requirements, $folderId)
    {
        return $requirements->filter(function($requirementData) use ($folderId) {
            $requirement = $requirementData['requirement'];
            if (empty($requirement->requirement_type_ids) || 
                (is_array($requirement->requirement_type_ids) && count($requirement->requirement_type_ids) === 0)) {
                return false;
            }
            
            return in_array($folderId, $requirement->requirement_type_ids);
        })->values()->all();
    }

    /**
     * Load requirements for a selected root folder
     */
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
                    ->where('course_id', $this->selectedCourse)
                    ->with('submissionFile')
                    ->orderBy('submitted_at', 'desc');
            }])
            ->with('guides')
            ->orderBy('due', 'asc')
            ->get();

        // Filter requirements that belong to this folder AND user is assigned via program chain
        $this->folderRequirements = $allRequirements->filter(function ($requirement) use ($user) {
            // Check if user is assigned to requirement via program chain
            if (!$this->isUserAssignedToRequirement($requirement, $user)) {
                return false;
            }
            
            // Check if requirement belongs to the selected folder
            if (!empty($requirement->requirement_type_ids) && 
                is_array($requirement->requirement_type_ids) && 
                count($requirement->requirement_type_ids) > 0) {
                return in_array($this->selectedFolder, $requirement->requirement_type_ids);
            }
            
            // For custom requirements (empty requirement_type_ids), check if we're in the custom folder
            if (($this->selectedFolder === 'custom_requirements') && 
                (empty($requirement->requirement_type_ids) || 
                 (is_array($requirement->requirement_type_ids) && count($requirement->requirement_type_ids) === 0))) {
                return true;
            }
            
            return false;
        })
        ->map(function ($requirement) use ($userId) {
            $userSubmitted = $this->hasUserSubmittedRequirement($requirement->id, $userId, $this->selectedCourse);
            $userMarkedDone = $this->hasUserMarkedDone($requirement->id, $userId, $this->selectedCourse);
            
            return [
                'requirement' => $requirement,
                'user_has_submitted' => $userSubmitted || $userMarkedDone,
                'user_marked_done' => $userMarkedDone,
                'can_mark_done' => $this->canMarkAsDone($requirement->id),
                'partnership_status' => $this->getPartnershipStatus($requirement),
            ];
        });
    }

    /**
     * Load requirements for a selected sub-folder
     */
    private function loadSubFolderRequirements()
    {
        if (!$this->selectedSubFolder || !$this->activeSemester) {
            $this->folderRequirements = [];
            return;
        }

        $userId = Auth::id();
        $user = Auth::user();
        
        // Get all requirements for the active semester
        $allRequirements = Requirement::where('semester_id', $this->activeSemester->id)
            ->with(['userSubmissions' => function($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('course_id', $this->selectedCourse)
                    ->with('submissionFile')
                    ->orderBy('submitted_at', 'desc');
            }])
            ->with('guides')
            ->orderBy('due', 'asc')
            ->get();

        // Filter requirements that belong to this sub-folder AND user is assigned via program chain
        $this->folderRequirements = $allRequirements->filter(function ($requirement) use ($user) {
            // Check if user is assigned to requirement via program chain
            if (!$this->isUserAssignedToRequirement($requirement, $user)) {
                return false;
            }
            
            // Check if requirement belongs to the selected sub-folder
            if (!empty($requirement->requirement_type_ids) && 
                is_array($requirement->requirement_type_ids) && 
                count($requirement->requirement_type_ids) > 0) {
                return in_array($this->selectedSubFolder, $requirement->requirement_type_ids);
            }
            
            return false;
        })
        ->map(function ($requirement) use ($userId) {
            $userSubmitted = $this->hasUserSubmittedRequirement($requirement->id, $userId, $this->selectedCourse);
            $userMarkedDone = $this->hasUserMarkedDone($requirement->id, $userId, $this->selectedCourse);
            
            return [
                'requirement' => $requirement,
                'user_has_submitted' => $userSubmitted || $userMarkedDone,
                'user_marked_done' => $userMarkedDone,
                'can_mark_done' => $this->canMarkAsDone($requirement->id),
                'partnership_status' => $this->getPartnershipStatus($requirement),
            ];
        });
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
            $requirement = Requirement::findOrFail($requirementId);
            
            // Always do a fresh check instead of relying on cached properties
            $canMarkDone = $this->canMarkAsDone($requirementId);
            
            \Log::info("Attempting to toggle mark as done - FRESH CHECK", [
                'requirement_id' => $requirementId,
                'requirement_name' => $requirement->name,
                'requirement_group' => $requirement->requirement_group,
                'can_mark_done' => $canMarkDone,
                'user_has_submitted' => $this->hasUserSubmittedRequirement($requirementId, Auth::id(), $this->selectedCourse),
                'is_part_of_partnership' => $requirement->isPartOfPartnership(),
            ]);
            
            // Check if requirement can be marked as done (partnership validation)
            if (!$canMarkDone) {
                if ($requirement->isPartOfPartnership()) {
                    $this->dispatch('showNotification',
                        type: 'error',
                        content: 'Cannot mark as done. All partner requirements (TOS and Examinations) must be submitted together.'
                    );
                } else {
                    $this->dispatch('showNotification',
                        type: 'error',
                        content: 'You need to submit a file first before marking as done.'
                    );
                }
                return;
            }
            
            // Check if already marked as done FOR THIS COURSE
            $existingIndicator = RequirementSubmissionIndicator::where('requirement_id', $requirementId)
                ->where('user_id', $user->id)
                ->where('course_id', $this->selectedCourse)
                ->first();
            
            if ($existingIndicator) {
                // Toggle off - mark as undone (for all partners too if it's a partnership)
                $this->markPartnershipAsUndone($requirement, $user->id);
                $message = 'Requirement marked as undone!';
                
                // Delete notification for admins
                $this->deleteNotificationForRequirement($requirementId, $user->id);
                
            } else {
                // Toggle on - mark as done FOR THIS COURSE (and all partners if it's a partnership)
                $this->markPartnershipAsDone($requirement, $user->id);
                $message = 'Requirement marked as done!';
                
                // Get ALL submissions for this requirement by this user FOR THIS COURSE
                $submissions = SubmittedRequirement::where('requirement_id', $requirementId)
                    ->where('user_id', $user->id)
                    ->where('course_id', $this->selectedCourse)
                    ->with('media')
                    ->get();
                
                if ($submissions->count() > 0) {
                    // Notify all admins with ALL submissions FOR THIS COURSE
                    $admins = User::role('admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\NewSubmissionNotification($requirement, $submissions));
                    }
                }
            }
            
            // Reload requirements to reflect changes
            if ($this->selectedSubFolder) {
                $this->loadSubFolderRequirements();
            } else if ($this->selectedFolder) {
                $this->loadFolderRequirements();
            } else {
                $this->loadCourseRequirements();
            }
            
            $this->dispatch('showNotification',
                type: 'success',
                content: $message
            );
            
        } catch (\Exception $e) {
            \Log::error("Failed to toggle mark as done", [
                'requirement_id' => $requirementId,
                'error' => $e->getMessage()
            ]);
            
            $this->dispatch('showNotification',
                type: 'error',
                content: 'Failed to update status: ' . $e->getMessage()
            );
        }
    }

    /**
     * Mark a partnership group as done (main requirement + all partners)
     */
    protected function markPartnershipAsDone($requirement, $userId)
    {
        if ($requirement->isPartOfPartnership()) {
            // Get all partners including the main requirement
            $allRequirements = $requirement->getPartnerRequirements($this->activeSemester->id);
            $allRequirements->push($requirement);
            
            foreach ($allRequirements as $partnerRequirement) {
                // Check if already marked as done for this course
                $existingIndicator = RequirementSubmissionIndicator::where('requirement_id', $partnerRequirement->id)
                    ->where('user_id', $userId)
                    ->where('course_id', $this->selectedCourse)
                    ->first();
                
                if (!$existingIndicator) {
                    RequirementSubmissionIndicator::create([
                        'requirement_id' => $partnerRequirement->id,
                        'user_id' => $userId,
                        'course_id' => $this->selectedCourse,
                        'submitted_at' => now(),
                    ]);
                    
                    \Log::info("Marked partner requirement as done", [
                        'main_requirement_id' => $requirement->id,
                        'partner_requirement_id' => $partnerRequirement->id,
                        'user_id' => $userId,
                        'course_id' => $this->selectedCourse
                    ]);
                }
            }
        } else {
            // For non-partnership requirements, just mark the single requirement
            RequirementSubmissionIndicator::create([
                'requirement_id' => $requirement->id,
                'user_id' => $userId,
                'course_id' => $this->selectedCourse,
                'submitted_at' => now(),
            ]);
        }
    }

    /**
     * Mark a partnership group as undone (main requirement + all partners)
     */
    protected function markPartnershipAsUndone($requirement, $userId)
    {
        if ($requirement->isPartOfPartnership()) {
            // Get all partners including the main requirement
            $allRequirements = $requirement->getPartnerRequirements($this->activeSemester->id);
            $allRequirements->push($requirement);
            
            foreach ($allRequirements as $partnerRequirement) {
                // Delete the indicator for this partner requirement
                RequirementSubmissionIndicator::where('requirement_id', $partnerRequirement->id)
                    ->where('user_id', $userId)
                    ->where('course_id', $this->selectedCourse)
                    ->delete();
                    
                \Log::info("Marked partner requirement as undone", [
                    'main_requirement_id' => $requirement->id,
                    'partner_requirement_id' => $partnerRequirement->id,
                    'user_id' => $userId,
                    'course_id' => $this->selectedCourse
                ]);
            }
        } else {
            // For non-partnership requirements, just unmark the single requirement
            RequirementSubmissionIndicator::where('requirement_id', $requirement->id)
                ->where('user_id', $userId)
                ->where('course_id', $this->selectedCourse)
                ->delete();
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
     * Get current folder for breadcrumb
     */
    public function getCurrentFolderProperty()
    {
        if (!$this->selectedFolder) {
            return null;
        }
        
        // Handle custom folder
        if ($this->selectedFolder === 'custom_requirements') {
            return (object)[
                'id' => 'custom_requirements',
                'name' => 'Other Requirements',
                'parent_id' => null,
                'is_folder' => true
            ];
        }
        
        return RequirementType::find($this->selectedFolder);
    }

    /**
     * Get current sub-folder for breadcrumb
     */
    public function getCurrentSubFolderProperty()
    {
        if (!$this->selectedSubFolder) {
            return null;
        }
        
        return RequirementType::find($this->selectedSubFolder);
    }

    public function render()
    {
        \Log::info('RequirementsList render', [
            'selectedCourse' => $this->selectedCourse,
            'selectedFolder' => $this->selectedFolder,
            'selectedSubFolder' => $this->selectedSubFolder,
            'assignedCoursesCount' => $this->assignedCourses->count()
        ]);

        return view('livewire.user.requirements.requirements-list', [
            'assignedCourses' => $this->assignedCourses,
            'activeSemester' => $this->activeSemester,
            'hasAssignedRequirements' => $this->hasAssignedRequirements,
            'currentCourse' => $this->currentCourse,
            'currentFolder' => $this->currentFolder,
            'currentSubFolder' => $this->currentSubFolder,
        ]);
    }
}