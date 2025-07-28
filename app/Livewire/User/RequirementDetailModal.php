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
            $submittedRequirement = SubmittedRequirement::create([
                'requirement_id' => $this->requirement->id,
                'user_id' => Auth::id(),
                'status' => SubmittedRequirement::STATUS_UNDER_REVIEW,
                'admin_notes' => $this->submissionNotes,
            ]);

            $submittedRequirement->addMedia($this->file->getRealPath())
                ->usingName($this->file->getClientOriginalName())
                ->usingFileName($this->file->getClientOriginalName())
                ->toMediaCollection('submission_files');

            $submittedRequirement->uploaded_by = Auth::id();
            $submittedRequirement->save();

            // Notify all admins (not just the creator)
            $admins = \App\Models\User::role(['admin', 'super-admin'])->get();

            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\NewSubmissionNotification(
                    $this->requirement,
                    $submittedRequirement
                ));
            }

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Requirement submitted successfully! Status: Under Review'
            );

            $this->reset(['file', 'submissionNotes']);
            $this->requirement->refresh();
        } catch (\Exception $e) {
            Log::error('Submission failed', [
                'requirement_id' => $this->requirement->id,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Submission failed: ' . $e->getMessage()
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

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Submission deleted successfully!'
            );

            $this->requirement->refresh();
            $this->confirmingDeletion = null;
        } catch (\Exception $e) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Deletion failed: ' . $e->getMessage()
            );
        }
    }

    public function via($notifiable)
    {
        return ['database']; // Ensure this is present
    }

    public function render()
    {
        return view('livewire.user.requirement-detail-modal');
    }
}
