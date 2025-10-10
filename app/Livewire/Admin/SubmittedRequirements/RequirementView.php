<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use Livewire\Component;
use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use App\Models\User;
use App\Models\Course;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class RequirementView extends Component
{
    use WithPagination;

    public $requirement_id;
    
    #[Url]
    public $user_id;
    
    #[Url]
    public $course_id;
    
    public $requirement;
    public $user;
    public $course;
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

    public function mount($requirement_id, $user_id = null, $course_id = null, $initialFileId = null)
    {
        $this->requirement_id = $requirement_id;
        
        // Get parameters from both route and query string
        $this->user_id = $user_id ?? request()->query('user_id');
        $this->course_id = $course_id ?? request()->query('course_id');
        
        $this->requirement = Requirement::findOrFail($requirement_id);
        
        // Load user and course if provided
        if ($this->user_id) {
            $this->user = User::find($this->user_id);
        }
        
        if ($this->course_id) {
            $this->course = Course::find($this->course_id);
        }
        
        $this->initialFileId = $initialFileId;
        $this->loadFiles();
    }

    public function goBackToIndex()
    {
        return redirect()->route('admin.submitted-requirements.index', [
            'category' => 'requirement',
            'selectedRequirementId' => $this->requirement_id,
            'selectedUserId' => $this->user_id,
        ]);
    }

    public function formatStatus($status)
    {
        return match($status) {
            'under_review' => 'Under Review',
            'revision_needed' => 'Revision Required',
            'rejected' => 'Rejected',
            'approved' => 'Approved',
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }

    protected function loadFiles()
    {
        // Reset files collection
        $this->allFiles = collect();
        $this->allSubmissions = collect();

        // Debug: Log the filter parameters
        \Log::info('RequirementView Filters:', [
            'requirement_id' => $this->requirement_id,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'user_loaded' => !is_null($this->user),
            'course_loaded' => !is_null($this->course)
        ]);

        // Check if we have all required parameters
        if (!$this->user_id || !$this->course_id) {
            \Log::warning('Missing required parameters:', [
                'user_id' => $this->user_id,
                'course_id' => $this->course_id
            ]);
            $this->selectInitialFile();
            return;
        }

        // Build query with STRICT filtering
        $query = SubmittedRequirement::where('requirement_id', $this->requirement_id)
            ->where('user_id', $this->user_id)
            ->where('course_id', $this->course_id);
        
        // Load submissions with relationships
        $this->allSubmissions = $query
            ->with(['media', 'reviewer', 'user.college', 'user.department', 'course'])
            ->latest()
            ->get();

        // Debug: Log the query results
        \Log::info('RequirementView Query Results:', [
            'submissions_count' => $this->allSubmissions->count(),
            'submission_ids' => $this->allSubmissions->pluck('id')->toArray()
        ]);

        // Collect all files from the filtered submissions
        $this->allFiles = $this->allSubmissions->flatMap(function ($submission) {
            $mediaFiles = $submission->getMedia('submission_files')->map(function ($media) use ($submission) {
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
                    'course' => $submission->course,
                    'submitted_at' => $submission->submitted_at,
                ];
            });

            return $mediaFiles;
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
        // Ensure $bytes is numeric
        if (!is_numeric($bytes)) {
            return 'Unknown size';
        }
        
        // Convert to integer to be safe
        $bytes = (int) $bytes;
        
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