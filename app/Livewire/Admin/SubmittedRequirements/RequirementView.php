<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use Livewire\Component;
use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Livewire\WithPagination;

class RequirementView extends Component
{
    use WithPagination;

    public $requirement_id;
    public $requirement;
    public $allSubmissions = [];
    public $allFiles = [];
    public $selectedFile = null;
    public $fileUrl = null;
    public $isImage = false;
    public $isPdf = false;
    public $isOfficeDoc = false;
    public $isPreviewable = false;
    public $selectedStatus = '';
    public $adminNotes = '';
    public $initialFileId;

    public function mount($requirement_id, $initialFileId = null)
    {
        $this->requirement_id = $requirement_id;
        $this->requirement = Requirement::findOrFail($requirement_id);
        $this->initialFileId = $initialFileId;
        $this->loadFiles();
    }

    public function formatStatus($status)
    {
        return match($status) {
            'under_review' => 'Under Review',
            'revision_needed' => 'Revision Needed',
            'rejected' => 'Rejected',
            'approved' => 'Approved',
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }

    protected function loadFiles()
    {
        // Load all submissions for this requirement
        $this->allSubmissions = SubmittedRequirement::where('requirement_id', $this->requirement_id)
            ->with(['media', 'reviewer', 'user.college', 'user.department'])
            ->latest()
            ->get();

        // Collect all files from all submissions as a collection
        $this->allFiles = $this->allSubmissions->flatMap(function ($submission) {
            return $submission->getMedia('submission_files')->map(function ($media) use ($submission) {
                $extension = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);
                
                return [
                    'id' => $media->id,
                    'submission_id' => $submission->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'mime_type' => $media->mime_type,
                    'size' => $this->formatFileSize($media->size),
                    'created_at' => $media->created_at,
                    'extension' => $extension,
                    'is_previewable' => $isPreviewable,
                    'status' => $submission->status,
                    'admin_notes' => $submission->admin_notes,
                    'reviewed_at' => $submission->reviewed_at,
                    'reviewer' => $submission->reviewer,
                    'user' => $submission->user,
                    'submitted_at' => $submission->submitted_at,
                ];
            });
        });

        // Select the file to preview
        $this->selectInitialFile();
    }

    protected function selectInitialFile()
    {
        // Check if there are any files
        if ($this->allFiles->isEmpty()) {
            $this->selectedFile = null;
            $this->fileUrl = null;
            $this->isImage = false;
            $this->isPdf = false;
            $this->isOfficeDoc = false;
            $this->isPreviewable = false;
            $this->selectedStatus = '';
            $this->adminNotes = '';
            return;
        }

        // Try to select the initial file if provided
        if ($this->initialFileId) {
            $file = $this->allFiles->firstWhere('id', $this->initialFileId);
            if ($file) {
                $this->selectFile($file['id']);
                return;
            }
        }

        // Fallback to the first file
        $this->selectFile($this->allFiles->first()['id']);
    }

    public function selectFile($fileId)
    {
        $this->selectedFile = collect($this->allFiles)->firstWhere('id', $fileId);
        
        if ($this->selectedFile) {
            $this->fileUrl = route('file.preview', [
                'submission' => $this->selectedFile['submission_id'],
                'file' => $this->selectedFile['id']
            ]);
            
            // Determine file type for proper display
            $this->isImage = str_starts_with($this->selectedFile['mime_type'], 'image/');
            $this->isPdf = $this->selectedFile['mime_type'] === 'application/pdf';
            $this->isOfficeDoc = in_array($this->selectedFile['extension'], ['doc', 'docx', 'xls', 'xlsx']);
            $this->isPreviewable = $this->isImage || $this->isPdf || $this->isOfficeDoc;
            
            // Set the current status and notes
            $this->selectedStatus = $this->selectedFile['status'];
            $this->adminNotes = $this->selectedFile['admin_notes'] ?? '';
        }
    }

    public function updateStatus()
    {
        $submission = SubmittedRequirement::find($this->selectedFile['submission_id']);
        
        if ($submission) {
            $submission->update([
                'status' => $this->selectedStatus,
                'admin_notes' => $this->adminNotes,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
            
            // Reload files to reflect changes
            $this->loadFiles();
            
            // Reselect the current file
            $this->selectFile($this->selectedFile['id']);
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'Status updated successfully'
            );
        }
    }

    protected function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function render()
    {
        return view('livewire.admin.submitted-requirements.requirement-view', [
            'statusOptions' => SubmittedRequirement::statuses()
        ]);
    }
}