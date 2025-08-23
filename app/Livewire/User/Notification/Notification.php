<?php

namespace App\Livewire\User\Notification;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Features\SupportRedirects\Redirector;

class Notification extends Component
{
    use WithFileUploads;
    
    public $notifications;
    public $selectedNotification = null;
    public $selectedNotificationData = null;
    public $showSubmissionModal = false;
    public $uploadedFiles = [];
    public $submissionNotes = '';
    public $currentRequirementId = null;
    public $currentRequirementName = '';

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $this->notifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(100)
            ->get();
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        session()->flash('message', 'All notifications marked as read.');
    }

    public function selectNotification(string $id): void
    {
        $this->selectedNotification = $id;

        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        // Base info
        $data = [
            'type'       => data_get($notification->data, 'type'),
            'message'    => data_get($notification->data, 'message', ''),
            'created_at' => $notification->created_at,
            'unread'     => false,
        ];

        // IDs may be in different keys
        $requirementId = data_get($notification->data, 'requirement_id')
            ?? data_get($notification->data, 'requirement.id');

        $submissionId  = data_get($notification->data, 'submission_id');

        // Store the current requirement ID for modal
        $this->currentRequirementId = $requirementId;
        
        // Get requirement name for modal
        if ($requirementId) {
            $requirement = Requirement::find($requirementId);
            $this->currentRequirementName = $requirement->name ?? 'Requirement';
        }

        // ---------------- REQUIREMENT (details + FILES FROM REQUIREMENT) ----------------
        $requirement = null;
        if ($requirementId) {
            // Eager load media relationship to get files
            $requirement = Requirement::with('media')->find($requirementId);
        }

        if ($requirement) {
            $dueRaw = $requirement->due ?? null;

            $data['requirement'] = [
                'id'          => $requirement->id,
                'name'        => $requirement->name,
                'description' => $requirement->description,
                'due'         => $dueRaw instanceof Carbon ? $dueRaw : ($dueRaw ? Carbon::parse($dueRaw) : null),
                'assigned_to' => $requirement->assigned_to ?? data_get($notification->data, 'requirement.assigned_to'),
                'status'      => $requirement->status ?? null,
                'priority'    => $requirement->priority ?? null,
                'created_at'  => $requirement->created_at,
                'updated_at'  => $requirement->updated_at,
            ];

            // Get ALL media files associated with the requirement (from admin)
            // This is the key part - fetching files from the requirement itself
            $requirementFiles = $requirement->getMedia('requirements');
            
            // If no files in 'requirements' collection, try other collections
            if ($requirementFiles->isEmpty()) {
                $requirementFiles = $requirement->getMedia('guides');
            }
            if ($requirementFiles->isEmpty()) {
                $requirementFiles = $requirement->getMedia('files');
            }
            if ($requirementFiles->isEmpty()) {
                // Fallback to all media if specific collections are empty
                $requirementFiles = $requirement->media;
            }

            $data['files'] = $requirementFiles->map(function ($media) {
                $ext = Str::lower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                return [
                    'id'             => $media->id,
                    'name'           => $media->file_name,
                    'extension'      => $ext,
                    'size'           => $this->formatFileSize($media->size),
                    'is_previewable' => $this->isPreviewable($ext),
                    'uploaded_at'    => $media->created_at,
                ];
            })->toArray();
        }

        // ---------------- ADMIN REVIEW DETAILS ----------------
        $submission = null;

        if ($submissionId) {
            $submission = SubmittedRequirement::query()
                ->where('id', $submissionId)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$submission && $requirementId) {
            $submission = SubmittedRequirement::query()
                ->where('requirement_id', $requirementId)
                ->where('user_id', Auth::id())
                ->latest()
                ->first();
        }

        if ($submission) {
            $data['admin_review'] = [
                'status'       => $submission->status,
                'status_label' => $this->statusLabel($submission->status),
                'admin_notes'  => $submission->admin_notes,
                'reviewed_at'  => $submission->reviewed_at,
                'submitted_at' => $submission->submitted_at ?? $submission->created_at,
            ];

            // Get admin review files if any (these are different from requirement files)
            $adminReviewFiles = $submission->getMedia('admin_review_files');
            if ($adminReviewFiles->isNotEmpty()) {
                $data['admin_files'] = $adminReviewFiles->map(function ($media) {
                    $ext = Str::lower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                    return [
                        'id'             => $media->id,
                        'name'           => $media->file_name,
                        'extension'      => $ext,
                        'size'           => $this->formatFileSize($media->size),
                        'is_previewable' => $this->isPreviewable($ext),
                    ];
                })->toArray();
            }
        }

        $this->selectedNotificationData = $data;
        $this->loadNotifications();
    }
    
    public function openSubmissionModal(): void
    {
        $this->showSubmissionModal = true;
    }
    
    public function closeSubmissionModal(): void
    {
        $this->showSubmissionModal = false;
        $this->uploadedFiles = [];
        $this->submissionNotes = '';
    }
    
    public function submitRequirement(): Redirector
{
    $this->validate([
        'uploadedFiles.*' => 'file|max:10240',
        'submissionNotes' => 'nullable|string|max:1000',
    ]);
    
    if (!$this->currentRequirementId) {
        session()->flash('error', 'No requirement selected for submission.');
        return redirect()->to('/user/requirements');
    }
    
    $submission = SubmittedRequirement::create([
        'user_id' => Auth::id(),
        'requirement_id' => $this->currentRequirementId,
        'admin_notes' => $this->submissionNotes,
        'status' => 'under_review',
        'submitted_at' => now(),
    ]);
    
    foreach ($this->uploadedFiles as $file) {
        try {
            // Use the Livewire uploaded file directly with media library
            $submission->addMedia($file->getRealPath())
                ->usingName($file->getClientOriginalName())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('submission_files');
        } catch (\Exception $e) {
            // Fallback: store temporarily and then add
            $tempPath = $file->store('temp', 'local');
            $fullPath = storage_path('app/' . $tempPath);
            
            $submission->addMedia($fullPath)
                ->usingName($file->getClientOriginalName())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('submission_files');
            
            // Clean up the temporary file after media is processed
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
    
    $this->closeSubmissionModal();
    
    // Redirect to requirements page with success message
    return redirect()->to('/user/requirements')->with('success', 'Requirement submitted successfully!');
}
    
    public function removeUploadedFile($index)
    {
        if (isset($this->uploadedFiles[$index])) {
            unset($this->uploadedFiles[$index]);
            $this->uploadedFiles = array_values($this->uploadedFiles);
        }
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            SubmittedRequirement::STATUS_UNDER_REVIEW     => 'Under Review',
            SubmittedRequirement::STATUS_REVISION_NEEDED  => 'Revision Needed',
            SubmittedRequirement::STATUS_REJECTED         => 'Rejected',
            SubmittedRequirement::STATUS_APPROVED         => 'Approved',
            default                                        => $status ? ucfirst($status) : '',
        };
    }

    protected function isPreviewable(?string $ext): bool
    {
        if (!$ext) return false;
        return in_array($ext, ['jpg','jpeg','png','gif','pdf'], true);
    }

    protected function formatFileSize($bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }

    public function render()
    {
        return view('livewire.user.notification.notification');
    }
}