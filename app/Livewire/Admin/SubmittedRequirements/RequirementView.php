<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use Livewire\Component;
use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use App\Models\User;
use App\Models\Course;
use App\Notifications\SubmissionStatusUpdated;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;

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

    public $showCorrectionNotesModal = false;
    public $correctionNotes = [];

    public $showESignConfirmationModal = false;
    public $pendingStatusUpdate = false;


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

    public function getBackUrl()
    {
        $source = request()->query('source', 'overview');
        
        // Get the current page from the query string, default to 1
        $page = request()->query('page', 1);
        
        if ($source === 'requirement-category') {
            // Return to requirement category with context preserved
            return route('admin.submitted-requirements.index', [
                'category' => 'requirement',
                'selectedRequirementId' => $this->requirement_id,
                'selectedUserId' => $this->user_id,
                'page' => $page // Preserve pagination
            ]);
        }
        
        // Default: return to overview with pagination preserved
        return route('admin.submitted-requirements.index', [
            'category' => 'overview',
            'page' => $page // Preserve pagination
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

        // Check if we have all required parameters
        if (!$this->user_id || !$this->course_id) {
            $this->selectInitialFile();
            return;
        }

        // Use JOIN to only get submissions that are marked as "done"
        $submissionIds = DB::table('submitted_requirements as sr')
            ->join('requirement_submission_indicators as rsi', function($join) {
                $join->on('sr.requirement_id', '=', 'rsi.requirement_id')
                    ->on('sr.user_id', '=', 'rsi.user_id')
                    ->on('sr.course_id', '=', 'rsi.course_id');
            })
            ->where('sr.requirement_id', $this->requirement_id)
            ->where('sr.user_id', $this->user_id)
            ->where('sr.course_id', $this->course_id)
            ->pluck('sr.id');

        // Debug: Log the filtered submission IDs
        \Log::info('Filtered Submission IDs:', [
            'submission_ids' => $submissionIds->toArray(),
            'filters' => [
                'requirement_id' => $this->requirement_id,
                'user_id' => $this->user_id,
                'course_id' => $this->course_id
            ]
        ]);

        // Load only the submissions that passed the JOIN filter
        $this->allSubmissions = SubmittedRequirement::whereIn('id', $submissionIds)
            ->with(['media', 'reviewer', 'user.college', 'course'])
            ->latest()
            ->get();

        // Collect all files from the filtered submissions
        $this->allFiles = $this->allSubmissions->flatMap(function ($submission) {
            $mediaFiles = $submission->getMedia('submission_files')->map(function ($media) use ($submission) {
                $extension = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);
                
                // Use original file name for display
                $displayFileName = $submission->getOriginalFileName();
                
                // Determine which URL to use for preview
                $previewUrl = $submission->getPreviewRouteUrl();
                
                return [
                    'id' => $media->id,
                    'submission_id' => $submission->id,
                    'name' => $displayFileName, // Show original file name
                    'file_name' => $media->file_name,
                    'display_name' => $displayFileName,
                    'url' => $previewUrl, // Use preview route URL
                    'original_url' => route('file.preview', ['submission' => $submission->id]),
                    'signed_url' => $submission->has_signed_document ? route('file.preview.signed', ['submission' => $submission->id]) : null,
                    'mime_type' => $media->mime_type,
                    'size' => $this->formatFileSize($media->size),
                    'created_at' => $media->created_at,
                    'extension' => $extension,
                    'signed_extension' => $submission->has_signed_document ? 'pdf' : null,
                    'is_previewable' => $isPreviewable,
                    'status' => $submission->status,
                    'admin_notes' => $submission->admin_notes,
                    'reviewed_at' => $submission->reviewed_at,
                    'reviewer' => $submission->reviewer,
                    'user' => $submission->user,
                    'course' => $submission->course,
                    'submitted_at' => $submission->submitted_at,
                    'has_signed_document' => $submission->has_signed_document,
                    'is_signed' => $submission->has_signed_document,
                    'signatory' => $submission->signatory,
                    'signed_at' => $submission->signed_at,
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
            
            \Log::info('No files found after filtering');
            return;
        }

        // Try to select the initial file if provided
        if ($this->initialFileId) {
            $file = $this->allFiles->firstWhere('id', $this->initialFileId);
            if ($file) {
                $this->selectFile($file['id']);
                \Log::info('Selected initial file by ID:', ['file_id' => $this->initialFileId]);
                return;
            }
        }

        // Fallback to the first file
        $firstFile = $this->allFiles->first();
        if ($firstFile) {
            $this->selectFile($firstFile['id']);
            \Log::info('Selected first available file:', ['file_id' => $firstFile['id']]);
        }
    }

    public function selectFile($fileId)
    {
        $this->selectedFile = collect($this->allFiles)->firstWhere('id', $fileId);
        
        if ($this->selectedFile) {
            // Use the correct preview URL
            $this->fileUrl = $this->selectedFile['url'];
            
            // Determine file type for proper display
            // If signed document exists, always treat as PDF
            if ($this->selectedFile['is_signed']) {
                $this->isImage = false;
                $this->isPdf = true;
                $this->isOfficeDoc = false;
                $this->isPreviewable = true;
            } else {
                $this->isImage = str_starts_with($this->selectedFile['mime_type'], 'image/');
                $this->isPdf = $this->selectedFile['mime_type'] === 'application/pdf';
                $this->isOfficeDoc = in_array($this->selectedFile['extension'], ['doc', 'docx', 'xls', 'xlsx']);
                $this->isPreviewable = $this->isImage || $this->isPdf || $this->isOfficeDoc;
            }
            
            // Set the current status and notes
            $this->selectedStatus = $this->selectedFile['status'];
            $this->adminNotes = $this->selectedFile['admin_notes'] ?? '';
        }
    }

    // NEW METHOD: Handle the update button click
    public function updateStatusButton()
    {
        if ($this->selectedStatus === 'approved') {
            $this->confirmUpdate();
        } else {
            // Check if changing FROM approved status
            $oldStatus = $this->selectedFile['status'] ?? '';
            if ($oldStatus === 'approved') {
                // Force page refresh when changing from approved
                $this->updateStatusWithRefresh();
            } else {
                $this->updateStatus();
            }
        }
    }

    public function updateStatus()
    {
        // First, check if this submission still exists in requirement_submission_indicators
        $stillSubmitted = DB::table('requirement_submission_indicators as rsi')
            ->where('rsi.requirement_id', $this->requirement_id)
            ->where('rsi.user_id', $this->user_id)
            ->where('rsi.course_id', $this->course_id)
            ->exists();

        if (!$stillSubmitted) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Cannot update status: This requirement is no longer marked as submitted.'
            );
            
            // Reload files to reflect the current state
            $this->loadFiles();
            return;
        }

        $submission = SubmittedRequirement::find($this->selectedFile['submission_id']);
        
        if ($submission) {
            // Store old status for notification
            $oldStatus = $submission->status;
            
            $submission->update([
                'status' => $this->selectedStatus,
                'admin_notes' => $this->adminNotes,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // CREATE CORRECTION NOTE
            \App\Models\AdminCorrectionNote::create([
                'submitted_requirement_id' => $submission->id,
                'requirement_id' => $this->requirement_id,
                'course_id' => $this->course_id,
                'user_id' => $this->user_id,
                'admin_id' => auth()->id(),
                'correction_notes' => $this->adminNotes ?: 'No notes provided',
                'file_name' => $this->selectedFile['file_name'],
                'status' => $this->selectedStatus,
            ]);
            
            // Send notification to user if status changed
            if ($oldStatus !== $this->selectedStatus) {
                $user = User::find($submission->user_id);
                if ($user) {
                    $user->notify(new SubmissionStatusUpdated($submission, $oldStatus, $this->selectedStatus));
                }
            }
            
            // Reload files to reflect changes
            $this->loadFiles();
            
            // Reselect the current file if it still exists
            if ($this->allFiles->isNotEmpty()) {
                $currentFile = $this->allFiles->firstWhere('id', $this->selectedFile['id']);
                if ($currentFile) {
                    $this->selectFile($currentFile['id']);
                } else {
                    // If current file no longer exists, select the first available file
                    $this->selectFile($this->allFiles->first()['id']);
                }
            }
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'Status updated successfully'
            );
        }

        $this->pendingStatusUpdate = false;
    }

    /**
     * Update status with page refresh (for status changes from approved)
     */
    public function updateStatusWithRefresh()
    {
        $submission = SubmittedRequirement::find($this->selectedFile['submission_id']);
        
        if ($submission) {
            // Store old status for notification
            $oldStatus = $submission->status;
            
            $submission->update([
                'status' => $this->selectedStatus,
                'admin_notes' => $this->adminNotes,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // CREATE CORRECTION NOTE
            \App\Models\AdminCorrectionNote::create([
                'submitted_requirement_id' => $submission->id,
                'requirement_id' => $this->requirement_id,
                'course_id' => $this->course_id,
                'user_id' => $this->user_id,
                'admin_id' => auth()->id(),
                'correction_notes' => $this->adminNotes ?: 'No notes provided',
                'file_name' => $this->selectedFile['file_name'],
                'status' => $this->selectedStatus,
            ]);
            
            // Send notification to user if status changed
            if ($oldStatus !== $this->selectedStatus) {
                $user = User::find($submission->user_id);
                if ($user) {
                    $user->notify(new SubmissionStatusUpdated($submission, $oldStatus, $this->selectedStatus));
                }
            }
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'Status updated successfully. Page will refresh.'
            );
            
            // Force a page refresh to avoid display issues
            $this->dispatch('refresh-page');
        }

        $this->pendingStatusUpdate = false;
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

    public function loadCorrectionNotes()
    {
        if ($this->selectedFile) {
            $submissionId = $this->selectedFile['submission_id'];
            
            $notes = \App\Models\AdminCorrectionNote::with(['admin'])
                ->where('submitted_requirement_id', $submissionId)
                ->orderBy('created_at', 'desc')
                ->get();

            $this->correctionNotes = $notes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'correction_notes' => $note->correction_notes,
                    'file_name' => $note->file_name,
                    'status' => $note->status,
                    'status_label' => $this->formatCorrectionNoteStatus($note->status),
                    'created_at' => $note->created_at,
                    'admin' => $note->admin ? [
                        'id' => $note->admin->id,
                        'name' => $note->admin->full_name ?? $note->admin->name,
                        'email' => $note->admin->email,
                    ] : null,
                    'addressed_at' => $note->addressed_at,
                ];
            })->toArray();
        }

        $this->showCorrectionNotesModal = true;
    }

    protected function formatCorrectionNoteStatus($status)
    {
        return match($status) {
            \App\Models\AdminCorrectionNote::STATUS_UPLOADED => 'Uploaded',
            \App\Models\AdminCorrectionNote::STATUS_UNDER_REVIEW => 'Under Review',
            \App\Models\AdminCorrectionNote::STATUS_REVISION_NEEDED => 'Revision Required',
            \App\Models\AdminCorrectionNote::STATUS_REJECTED => 'Rejected',
            \App\Models\AdminCorrectionNote::STATUS_APPROVED => 'Approved',
            default => ucfirst($status),
        };
    }

    public function confirmUpdate()
    {
        // Store the pending update
        $this->pendingStatusUpdate = true;
        
        // Show confirmation modal
        $this->showESignConfirmationModal = true;
        
        // Debug log
        \Log::info('confirmUpdate called', [
            'showESignConfirmationModal' => $this->showESignConfirmationModal,
            'pendingStatusUpdate' => $this->pendingStatusUpdate
        ]);
        
        // Force Livewire to re-render
        $this->dispatch('modal-should-show');
    }

    public function approveWithoutSignature()
    {
        // Close confirmation modal
        $this->showESignConfirmationModal = false;
        
        // Proceed with normal update
        $this->updateStatus();
        
        // Reset pending update
        $this->pendingStatusUpdate = false;
    }

    public function openESignModal()
    {
        // Close confirmation modal
        $this->showESignConfirmationModal = false;
        
        // Dispatch event to open e-sign modal
        $this->dispatch('open-esign-modal', 
            submissionId: $this->selectedFile['submission_id'],
            fileUrl: $this->fileUrl,
            fileExtension: $this->selectedFile['extension']
        );
    } 

    #[On('signature-applied')]
    public function refreshFiles($submissionId)
    {
        // Reload files to show the signed version
        $this->loadFiles();
        
        // Reselect the signed file
        if ($this->allFiles->isNotEmpty()) {
            $signedFile = $this->allFiles->firstWhere('submission_id', $submissionId);
            if ($signedFile) {
                $this->selectFile($signedFile['id']);
            }
        }
    }

    /**
     * Handle page refresh when status changes from approved
     */
    #[On('refresh-page')]
    public function refreshPage()
    {
        // Reload the entire component
        $this->loadFiles();
    }

    public function render()
    {
        return view('livewire.admin.submitted-requirements.requirement-view', [
            'statusOptions' => SubmittedRequirement::statusesForReview()
        ]);
    }
}