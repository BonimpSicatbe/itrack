<?php

namespace App\Livewire\user;

use App\Livewire\User\Notification\Notification;
use Livewire\Component;
use App\Models\Requirement;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Models\SubmittedRequirement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Add this import

class RequirementDetailModal extends Component
{
    use WithFileUploads;

    public $requirement;
    public $file;
    public $uploading = false;
    public $submissionNotes = '';
    public $confirmingDeletion = null;

    protected $listeners = ['showRequirementDetail' => 'loadRequirement'];

    public function loadRequirement($requirementId)
    {
        $this->requirement = Requirement::with([
            'guides',
            'userSubmissions' => function ($query) {
                $query->with(['submissionFile', 'reviewer'])
                    ->latest();
            }
        ])->find($requirementId);

        if ($this->requirement) {
            $this->requirement->due = \Carbon\Carbon::parse($this->requirement->due);
        }

        $this->reset(['file', 'submissionNotes']);
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

    public function mount()
    {
        // Check if there's a requirement ID in the URL
        $requirementId = request()->get('requirement');
        
        if ($requirementId) {
            $this->loadRequirement($requirementId);
        }
    }

    public function closeModal()
    {
        $this->dispatch('closeModal');
        $this->reset(['requirement', 'file', 'submissionNotes']);
    }

    public function submitRequirement()
    {
        $this->validate([
            'file' => 'required|file|max:10240',
            'submissionNotes' => 'nullable|string|max:500',
        ]);

        $this->uploading = true;

        try {
            // Create the submission
            $submittedRequirement = SubmittedRequirement::create([
                'requirement_id' => $this->requirement->id,
                'user_id' => Auth::id(),
                'status' => SubmittedRequirement::STATUS_UNDER_REVIEW,
                'admin_notes' => $this->submissionNotes,
                'submitted_at' => now(),
            ]);

            // Store the file with original filename
            $submittedRequirement->addMedia($this->file->getRealPath())
                ->usingName($this->file->getClientOriginalName()) // Preserve original name
                ->usingFileName($this->file->getClientOriginalName()) // Preserve filename with extension
                ->toMediaCollection('submission_files');

            // Notify admins (working notification code)
            $admins = \App\Models\User::role(['admin', 'super-admin'])->get();
            foreach ($admins as $admin) {
                $admin->notifyNow(
                    new \App\Notifications\NewSubmissionNotification(
                        $this->requirement,
                        $submittedRequirement
                    )
                );
            }

            // Show success notification
            $this->dispatch('showNotification', 
                type: 'success',
                content: 'Requirement submitted successfully!'
            );

            $this->reset(['file', 'submissionNotes']);
            $this->requirement->refresh();

        } catch (\Exception $e) {
            // Show error notification
            $this->dispatch('showNotification',
                type: 'error',
                content: 'Submission failed: ' . $e->getMessage()
            );
        } finally {
            $this->uploading = false;
        }
    }

    public function confirmDelete($submissionId)
    {
        $this->confirmingDeletion = $submissionId;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = null;
    }

    public function deleteSubmission(SubmittedRequirement $submission)
    {
        try {
            DB::transaction(function () use ($submission) {
                // Delete associated file
                if ($submission->submissionFile) {
                    $submission->submissionFile->delete();
                }

                // Delete the submission record
                $submission->delete();
            });

            // Show success notification
            $this->dispatch('showNotification',
                type: 'success',
                content: 'Submission deleted successfully!'
            );

            $this->requirement->refresh();
            $this->confirmingDeletion = null;
        } catch (\Exception $e) {
            // Show error notification
            $this->dispatch('showNotification',
                type: 'error',
                content: 'Deletion failed: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.user.requirement-detail-modal');
    }
}