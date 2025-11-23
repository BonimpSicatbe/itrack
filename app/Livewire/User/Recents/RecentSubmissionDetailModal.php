<?php

namespace App\Livewire\User\Recents;

use Livewire\Component;
use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use App\Models\AdminCorrectionNote;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecentSubmissionDetailModal extends Component
{
    public $isOpen = false;
    public $submission = null;
    public $requirement = null;
    public $correctionNotes = [];

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
            $this->loadCorrectionNotes();
            $this->isOpen = true;
        }
    }

    public function loadCorrectionNotes()
    {
        if ($this->submission) {
            $this->correctionNotes = AdminCorrectionNote::with(['admin'])
                ->where('submitted_requirement_id', $this->submission->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'correction_notes' => $note->correction_notes,
                        'file_name' => $note->file_name,
                        'status' => $note->status,
                        'status_label' => $this->formatCorrectionNoteStatus($note->status),
                        'status_badge' => $note->status_badge,
                        'created_at' => $note->created_at,
                        'admin' => $note->admin ? [
                            'id' => $note->admin->id,
                            'name' => $note->admin->full_name ?? $note->admin->name,
                            'email' => $note->admin->email,
                        ] : null,
                        'addressed_at' => $note->addressed_at,
                        'has_file_been_replaced' => $note->hasFileBeenReplaced(),
                        'current_file_name' => $note->getCurrentFileName(),
                    ];
                })
                ->toArray();
        }
    }

    protected function formatCorrectionNoteStatus($status)
    {
        return match($status) {
            AdminCorrectionNote::STATUS_UPLOADED => 'Uploaded',
            AdminCorrectionNote::STATUS_UNDER_REVIEW => 'Under Review',
            AdminCorrectionNote::STATUS_REVISION_NEEDED => 'Revision Required',
            AdminCorrectionNote::STATUS_REJECTED => 'Rejected',
            AdminCorrectionNote::STATUS_APPROVED => 'Approved',
            default => ucfirst($status),
        };
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->submission = null;
        $this->requirement = null;
        $this->correctionNotes = [];
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

    /**
     * Get requirement folder URL
     */
    public function getRequirementFolderUrl()
    {
        if ($this->submission && $this->submission->course && $this->requirement) {
            // Check if requirement has requirement_type_ids and find the appropriate folder
            if (!empty($this->requirement->requirement_type_ids) && is_array($this->requirement->requirement_type_ids)) {
                $requirementTypeIds = $this->requirement->requirement_type_ids;
                
                // If there are multiple folders, we need to find the hierarchy
                if (count($requirementTypeIds) > 0) {
                    // Load the requirement types to check parent-child relationships
                    $requirementTypes = \App\Models\RequirementType::whereIn('id', $requirementTypeIds)
                        ->with('parent')
                        ->get();
                    
                    // Try to find a sub-folder (child folder) first
                    $subFolder = $requirementTypes->first(function($type) {
                        return $type->parent_id !== null;
                    });
                    
                    // If we found a sub-folder, build URL with both folder and subfolder
                    if ($subFolder) {
                        return route('user.requirements', [
                            'course' => $this->submission->course->id,
                            'folder' => $subFolder->parent_id,
                            'subfolder' => $subFolder->id
                        ]);
                    }
                    
                    // Otherwise, use the first folder as root folder
                    $rootFolder = $requirementTypes->first();
                    if ($rootFolder) {
                        return route('user.requirements', [
                            'course' => $this->submission->course->id,
                            'folder' => $rootFolder->id
                        ]);
                    }
                }
            }
            
            // Fallback: just link to the course requirements without specific folder
            return route('user.requirements', [
                'course' => $this->submission->course->id
            ]);
        }
        return null;
    }

    public function render()
    {
        return view('livewire.user.recents.recent-submission-detail-modal');
    }
}