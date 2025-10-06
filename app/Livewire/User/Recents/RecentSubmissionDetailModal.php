<?php

namespace App\Livewire\User\Recents;

use Livewire\Component;
use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecentSubmissionDetailModal extends Component
{
    public $isOpen = false;
    public $submission = null;
    public $requirement = null;

    protected $listeners = ['showRecentSubmissionDetail'];

    public function showRecentSubmissionDetail($submissionId)
    {
        $this->submission = SubmittedRequirement::with([
            'requirement.semester',
            'submissionFile',
            'reviewer',
            'course' 
        ])->find($submissionId);
        
        if ($this->submission) {
            $this->requirement = $this->submission->requirement;
            $this->isOpen = true;
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->submission = null;
        $this->requirement = null;
    }

    public function downloadFile($fileId = null)
    {
        // If no fileId provided, use the submission's file
        if (!$fileId && $this->submission && $this->submission->submissionFile) {
            $fileId = $this->submission->submissionFile->id;
        }

        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($fileId);
        
        if (!$media) {
            session()->flash('error', 'File not found.');
            return;
        }

        // Verify this media belongs to the current submission
        if ($media->model_id !== $this->submission->id || $media->model_type !== SubmittedRequirement::class) {
            session()->flash('error', 'Unauthorized access to file.');
            return;
        }

        try {
            // Get the file path
            $filePath = $media->getPath();
            
            // Check if file exists
            if (!file_exists($filePath)) {
                session()->flash('error', 'File not found on disk.');
                return;
            }

            // Return download response
            return response()->download($filePath, $media->file_name);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error downloading file: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Get preview URL for the file
     */
    public function getPreviewUrl()
    {
        if ($this->submission && $this->submission->submissionFile) {
            return route('file.preview', ['submission' => $this->submission->id]);
        }
        return null;
    }

    /**
     * Check if file is previewable
     */
    public function getIsPreviewableProperty()
    {
        if (!$this->submission || !$this->submission->submissionFile) {
            return false;
        }

        $extension = strtolower(pathinfo($this->submission->submissionFile->file_name, PATHINFO_EXTENSION));
        
        $previewableTypes = [
            'pdf',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', // Images
            'txt', 'rtf', // Text files
        ];
        
        return in_array($extension, $previewableTypes);
    }

    public function render()
    {
        return view('livewire.user.recents.recent-submission-detail-modal');
    }
}