<?php

namespace App\Livewire\Admin\Notification;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\SubmittedRequirement;
use Illuminate\Notifications\DatabaseNotification;

class Notifications extends Component
{
    public $notifications = [];
    public $selectedNotification = null;
    public $selectedNotificationData = null;
    public $activeTab = 'all';
    
    // Change to array properties to handle multiple files
    public $newStatus = [];
    public $adminNotes = [];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = Auth::user()
            ->notifications()
            ->whereIn('type', ['App\Notifications\NewSubmissionNotification', 'App\Notifications\SubmissionStatusUpdated'])
            ->latest()
            ->get();
    }

    public function getFilteredNotifications()
    {
        if ($this->activeTab === 'unread') {
            return $this->notifications->filter(fn($notification) => $notification->unread());
        } elseif ($this->activeTab === 'read') {
            return $this->notifications->filter(fn($notification) => !$notification->unread());
        }

        return $this->notifications;
    }

    public function updatedActiveTab(): void
    {
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        // Reset form data when tab changes
        $this->reset(['newStatus', 'adminNotes']);
    }

    public function selectNotification($id)
    {
        $id = urldecode($id);
        $this->selectedNotification = $id;
        
        $notification = DatabaseNotification::find($id);
        
        if ($notification) {
            if ($notification->unread()) {
                $notification->markAsRead();
                $this->dispatch('notification-read');
                $this->loadNotifications();
            }

            $this->selectedNotificationData = [
                'type' => $notification->data['type'] ?? null,
                'message' => $notification->data['message'] ?? '',
                'created_at' => $notification->created_at,
                'unread' => $notification->unread(),
            ];

            $this->loadDetails($notification->data);
        }
        
        // Reset form data when selecting a new notification
        $this->reset(['newStatus', 'adminNotes']);
    }

    protected function loadDetails($notificationData)
    {
        // Load requirement details for both notification types
        $requirementId = $notificationData['requirement_id'] ?? null;
        if ($requirementId) {
            $requirement = \App\Models\Requirement::with(['creator', 'updater', 'archiver'])
                ->find($requirementId);

            if ($requirement) {
                $this->selectedNotificationData['requirement'] = [
                    'id' => $requirement->id,
                    'name' => $requirement->name,
                    'description' => $requirement->description,
                    'due' => $requirement->due,
                    'assigned_to' => $requirement->assigned_to_display,
                    'status' => $requirement->status,
                    'created_at' => $requirement->created_at,
                    'updated_at' => $requirement->updated_at,
                    'creator' => $requirement->creator ? [
                        'id' => $requirement->creator->id,
                        'name' => $requirement->creator->name,
                        'email' => $requirement->creator->email,
                    ] : null,
                    'updater' => $requirement->updater ? [
                        'id' => $requirement->updater->id,
                        'name' => $requirement->updater->name,
                        'email' => $requirement->updater->email,
                    ] : null,
                    'archiver' => $requirement->archiver ? [
                        'id' => $requirement->archiver->id,
                        'name' => $requirement->archiver->name,
                        'email' => $requirement->archiver->email,
                    ] : null,
                ];
            }
        }

        // Handle different notification types
        if ($notificationData['type'] === 'submission_status_updated') {
            $this->loadStatusUpdateDetails($notificationData);
        } else {
            $this->loadNewSubmissionDetails($notificationData);
        }
    }

    protected function loadStatusUpdateDetails($notificationData)
    {
        // For status update notifications
        $submissionId = $notificationData['submission_id'] ?? null;
        
        if ($submissionId) {
            $submission = SubmittedRequirement::with(['reviewer', 'course.program', 'user', 'media'])
                ->find($submissionId);

            if ($submission) {
                $this->selectedNotificationData['submissions'] = [$this->formatSubmission($submission)];
                $this->loadFiles([$submission]);
                
                // Add status update specific data
                $this->selectedNotificationData['status_update'] = [
                    'old_status' => $notificationData['old_status'],
                    'new_status' => $notificationData['new_status'],
                    'reviewed_by' => $notificationData['reviewed_by'],
                    'reviewed_at' => $notificationData['reviewed_at'],
                ];
            }
        }

        // Load user data
        $userId = $notificationData['user_id'] ?? null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $this->selectedNotificationData['submitter'] = [
                    'id' => $user->id,
                    'name' => $user->full_name ?? $user->name,
                    'email' => $user->email,
                ];
            }
        }
    }

    protected function loadNewSubmissionDetails($notificationData)
    {
        // Original logic for new submission notifications
        $submissionIds = [];
        
        if (isset($notificationData['submission_ids'])) {
            $submissionIds = $notificationData['submission_ids'];
        } elseif (isset($notificationData['submission_id'])) {
            $submissionIds = [$notificationData['submission_id']];
        }

        $submissions = SubmittedRequirement::with(['reviewer', 'course.program', 'user', 'media'])
            ->whereIn('id', $submissionIds)
            ->get();

        if ($submissions->count() > 0) {
            $this->selectedNotificationData['submissions'] = $submissions->map(function ($submission) {
                return $this->formatSubmission($submission);
            })->toArray();

            $this->loadFiles($submissions);
        }

        $userId = $notificationData['user_id'] ?? null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $this->selectedNotificationData['submitter'] = [
                    'id' => $user->id,
                    'name' => $user->full_name ?? $user->name,
                    'email' => $user->email,
                ];
            }
        }

        if (isset($notificationData['course_id']) && $notificationData['course_id']) {
            $course = \App\Models\Course::with('program')->find($notificationData['course_id']);
            if ($course) {
                $this->selectedNotificationData['course'] = [
                    'id' => $course->id,
                    'course_code' => $course->course_code,
                    'course_name' => $course->course_name,
                    'program' => $course->program ? [
                        'id' => $course->program->id,
                        'program_code' => $course->program->program_code,
                        'program_name' => $course->program->program_name,
                    ] : null,
                ];
            }
        }
    }

    protected function formatSubmission($submission)
    {
        $statusLabel = match($submission->status) {
            SubmittedRequirement::STATUS_UNDER_REVIEW => 'Under Review',
            SubmittedRequirement::STATUS_REVISION_NEEDED => 'Revision Required',
            SubmittedRequirement::STATUS_REJECTED => 'Rejected',
            SubmittedRequirement::STATUS_APPROVED => 'Approved',
            default => ucfirst($submission->status),
        };

        return [
            'id' => $submission->id,
            'status' => $submission->status,
            'status_label' => $statusLabel,
            'admin_notes' => $submission->admin_notes,
            'submitted_at' => $submission->submitted_at,
            'reviewed_at' => $submission->reviewed_at,
            'course' => $submission->course ? [
                'id' => $submission->course->id,
                'course_code' => $submission->course->course_code,
                'course_name' => $submission->course->course_name,
                'program' => $submission->course->program ? [
                    'id' => $submission->course->program->id,
                    'program_code' => $submission->course->program->program_code,
                    'program_name' => $submission->course->program->program_name,
                ] : null,
            ] : null,
            'reviewer' => $submission->reviewer ? [
                'id' => $submission->reviewer->id,
                'name' => $submission->reviewer->name,
                'email' => $submission->reviewer->email,
            ] : null,
            'needs_review' => $submission->status === SubmittedRequirement::STATUS_UNDER_REVIEW,
        ];
    }

    protected function loadFiles($submissions)
    {
        $files = [];
        
        foreach ($submissions as $submission) {
            foreach ($submission->getMedia('submission_files') as $media) {
                $extension = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
                
                $files[] = [
                    'id' => $media->id,
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
                    'submission_id' => $submission->id,
                    'course' => $submission->course ? [
                        'id' => $submission->course->id,
                        'course_code' => $submission->course->course_code,
                        'course_name' => $submission->course->course_name,
                        'program' => $submission->course->program ? [
                            'id' => $submission->course->program->id,
                            'program_code' => $submission->course->program->program_code,
                            'program_name' => $submission->course->program->program_name,
                        ] : null,
                    ] : null,
                ];
            }
        }

        $this->selectedNotificationData['files'] = $files;
    }

    public function updateFileStatus($submissionId)
    {
        // Validate the specific submission's data
        $this->validate([
            "newStatus.{$submissionId}" => 'required|in:' . implode(',', array_keys(SubmittedRequirement::statuses())),
            "adminNotes.{$submissionId}" => 'nullable|string',
        ]);

        $submission = SubmittedRequirement::findOrFail($submissionId);
        
        // Store old status for notification
        $oldStatus = $submission->status;
        
        $submission->update([
            'status' => $this->newStatus[$submissionId],
            'admin_notes' => $this->adminNotes[$submissionId] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Send notification to user about status update
        $user = \App\Models\User::find($submission->user_id);
        if ($user) {
            $user->notify(new \App\Notifications\SubmissionStatusUpdated($submission, $oldStatus, $this->newStatus[$submissionId]));
        }

        // Reload the notification data to reflect changes
        if ($this->selectedNotification) {
            $notification = DatabaseNotification::find($this->selectedNotification);
            if ($notification) {
                $this->loadDetails($notification->data);
            }
        }

        // Reset only the specific submission's form data
        if (isset($this->newStatus[$submissionId])) {
            unset($this->newStatus[$submissionId]);
        }
        if (isset($this->adminNotes[$submissionId])) {
            unset($this->adminNotes[$submissionId]);
        }
        
        $this->dispatch('showNotification', 
            type: 'success',
            content: 'Status updated successfully!',
            duration: 3000
        );
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

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()
            ->whereIn('type', ['App\Notifications\NewSubmissionNotification', 'App\Notifications\SubmissionStatusUpdated'])
            ->update(['read_at' => now()]);
        
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        $this->reset(['newStatus', 'adminNotes']);
        
        $this->dispatch('notifications-marked-read');
        
        $this->dispatch('showNotification', 
            type: 'success',
            content: 'All notifications marked as read!',
            duration: 3000
        );
    }

    public function markAllAsUnread()
    {
        $readNotifications = Auth::user()->readNotifications()
            ->whereIn('type', ['App\Notifications\NewSubmissionNotification', 'App\Notifications\SubmissionStatusUpdated'])
            ->get();
        
        $readNotifications->each(function ($notification) {
            $notification->markAsUnread();
        });
        
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        $this->reset(['newStatus', 'adminNotes']);
        
        $this->dispatch('notifications-marked-unread', count: $readNotifications->count());
        
        $this->dispatch('showNotification', 
            type: 'success',
            content: 'All notifications marked as unread!',
            duration: 3000
        );
    }

    public function markAsUnread($id)
    {
        $id = urldecode($id);
        $notification = DatabaseNotification::find($id);
        
        if ($notification && !$notification->unread()) {
            $notification->markAsUnread();
            $this->loadNotifications();
            
            // Update the current notification data if it's the selected one
            if ($this->selectedNotification === $id) {
                $this->selectedNotificationData['unread'] = true;
            }
            
            // Add this dispatch to update the navigation count
            $this->dispatch('notification-unread');
            
            $this->dispatch('showNotification', 
                type: 'success',
                content: 'Notification marked as unread!',
                duration: 3000
            );
        }
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

    public function render()
    {
        $filteredNotifications = $this->getFilteredNotifications();
        
        return view('livewire.admin.notification.notifications', [
            'filteredNotifications' => $filteredNotifications,
            'activeTab' => $this->activeTab,
        ]);
    }
}