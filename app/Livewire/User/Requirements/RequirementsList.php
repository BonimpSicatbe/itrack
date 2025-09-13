<?php

namespace App\Livewire\User\Requirements;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\Semester;
use App\Models\User;
use App\Models\SubmittedRequirement;
use App\Models\RequirementSubmissionIndicator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

class RequirementsList extends Component
{
    use WithFileUploads;

    public $search = '';
    public $statusFilter = 'all';
    public $sortField = 'due';
    public $sortDirection = 'desc';
    public $file;
    public $confirmingDeletion = null;
    public $submissionNotes = '';
    public $currentRequirementId = null;
    public $openAccordions = [];
    public $activeSemester;
    public $activeTabs = [];
    
    // Add these properties for delete modal
    public $showDeleteModal = false;
    public $submissionToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'sortField' => ['except' => 'due'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->activeSemester = Semester::getActiveSemester();
    }

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

    public function toggleAccordion($requirementId)
    {
        if (isset($this->openAccordions[$requirementId])) {
            unset($this->openAccordions[$requirementId]);
        } else {
            $this->openAccordions[$requirementId] = true;
            $this->setActiveTab($requirementId, 'details');
        }
    }

    public function isAccordionOpen($requirementId)
    {
        return isset($this->openAccordions[$requirementId]);
    }

    public function setActiveTab($requirementId, $tabName)
    {
        $this->activeTabs[$requirementId] = $tabName;
    }

    public function isTabActive($requirementId, $tabName)
    {
        return isset($this->activeTabs[$requirementId]) && $this->activeTabs[$requirementId] === $tabName;
    }

    #[Computed]
    public function requirements()
    {
        $user = Auth::user();
        $activeSemester = $this->activeSemester;
        
        if (!$activeSemester) {
            return collect();
        }

        // Get requirements assigned to user's college or department using assigned_to column
        $requirements = Requirement::where('semester_id', $activeSemester->id)
            ->where(function($query) use ($user) {
                if ($user->college) {
                    $query->orWhere('assigned_to', $user->college->name);
                }
                if ($user->department) {
                    $query->orWhere('assigned_to', $user->department->name);
                }
            })
            ->with(['userSubmissions' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->with('guides')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return $requirements->map(function ($requirement) use ($user) {
            $count = 0;
            
            if (College::where('name', $requirement->assigned_to)->exists()) {
                $college = College::where('name', $requirement->assigned_to)->first();
                $count = User::where('college_id', $college->id)->count();
            } elseif (Department::where('name', $requirement->assigned_to)->exists()) {
                $department = Department::where('name', $requirement->assigned_to)->first();
                $count = User::where('department_id', $department->id)->count();
            }
            
            // Check if current user has submitted this requirement
            $userSubmitted = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->where('user_id', $user->id)
                ->exists();
            
            $userMarkedDone = RequirementSubmissionIndicator::where('requirement_id', $requirement->id)
                ->where('user_id', $user->id)
                ->exists();
            
            $requirement->assigned_users_count = $count;
            $requirement->user_has_submitted = $userSubmitted || $userMarkedDone;
            $requirement->user_marked_done = $userMarkedDone;
            
            return $requirement;
        })
        ->filter(function ($requirement) {
            // Apply status filter
            if ($this->statusFilter === 'completed') {
                return $requirement->user_marked_done;
            } elseif ($this->statusFilter === 'overdue') {
                return $requirement->due->isPast() && !$requirement->user_has_submitted;
            }
            return true; // 'all' filter or no filter
        });
    }

    public function submitRequirement($requirementId)
    {
        $this->validate([
            'file' => 'required|file|max:10240',
            'submissionNotes' => 'nullable|string|max:500',
        ]);

        try {
            $requirement = Requirement::findOrFail($requirementId);
            
            // Create the submission
            $submittedRequirement = SubmittedRequirement::create([
                'requirement_id' => $requirementId,
                'user_id' => Auth::id(),
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

    public function confirmDelete($submissionId)
    {
        $this->submissionToDelete = $submissionId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->submissionToDelete = null;
    }

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

    // Modify the toggleMarkAsDone method
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
                
                // DELETE THE NOTIFICATION FOR ADMINS
                $this->deleteNotificationForRequirement($requirementId, $user->id);
                
            } else {
                // Toggle on - mark as done (existing code remains the same)
                $indicator = RequirementSubmissionIndicator::create([
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

    public function render()
    {
        return view('livewire.user.requirements.requirements-list', [
            'activeSemester' => $this->activeSemester,
        ]);
    }
}