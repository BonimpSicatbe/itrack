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

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        // Get all notifications first
        $allNotifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(100)
            ->get();

        // Filter notifications to only show those related to active semester requirements
        $this->notifications = $allNotifications->filter(function ($notification) {
            $requirementId = data_get($notification->data, 'requirement_id')
                ?? data_get($notification->data, 'requirement.id');

            if (!$requirementId) {
                // If no requirement ID, keep the notification (might be system notifications)
                return true;
            }

            // Check if the requirement belongs to an active semester
            $requirement = Requirement::where('id', $requirementId)
                ->whereHas('semester', function ($query) {
                    $query->where('is_active', true);
                })
                ->first();

            return $requirement !== null;
        })->values(); // Reset array keys
    }

    public function markAllAsRead(): void
    {
        // Only mark notifications related to active semester requirements as read
        $notificationIds = $this->notifications->pluck('id')->toArray();
        
        Auth::user()->notifications()
            ->whereIn('id', $notificationIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        session()->flash('message', 'All active semester notifications marked as read.');
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

        // ---------------- REQUIREMENT (details + FILES FROM REQUIREMENT) ----------------
        $requirement = null;
        if ($requirementId) {
            // Only get requirement if it belongs to active semester
            $requirement = Requirement::with('media')
                ->where('id', $requirementId)
                ->whereHas('semester', function ($query) {
                    $query->where('is_active', true);
                })
                ->first();
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
            // Only get submission if related requirement is from active semester
            $submission = SubmittedRequirement::query()
                ->where('id', $submissionId)
                ->where('user_id', Auth::id())
                ->whereHas('requirement', function ($query) {
                    $query->whereHas('semester', function ($semesterQuery) {
                        $semesterQuery->where('is_active', true);
                    });
                })
                ->first();
        }

        if (!$submission && $requirementId) {
            $submission = SubmittedRequirement::query()
                ->where('requirement_id', $requirementId)
                ->where('user_id', Auth::id())
                ->whereHas('requirement', function ($query) {
                    $query->whereHas('semester', function ($semesterQuery) {
                        $semesterQuery->where('is_active', true);
                    });
                })
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
    
    public function submitRequirement(): Redirector
    {
        // Get the requirement ID from the selected notification data
        $requirementId = $this->selectedNotificationData['requirement']['id'] ?? null;
        
        if ($requirementId) {
            // Redirect to requirements page with the specific requirement highlighted/opened
            return redirect()->to("/user/requirements?requirement={$requirementId}");
        }
        
        // Fallback: redirect to general requirements page
        return redirect()->to('/user/requirements');
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