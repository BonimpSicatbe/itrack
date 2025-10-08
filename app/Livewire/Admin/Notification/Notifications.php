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
    
    // For status update form
    public $selectedFileId;
    public $newStatus;
    public $adminNotes;

    // For delete confirmation modal
    public $showDeleteConfirmationModal = false;
    public $notificationToDelete = null;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = Auth::user()
            ->notifications()
            ->where('type', 'App\Notifications\NewSubmissionNotification')
            ->latest()
            ->get();
    }

    public function selectNotification($id)
    {
        // Decode the ID if it's encoded
        $id = urldecode($id);
        $this->selectedNotification = $id;
        
        // Find the notification by ID
        $notification = DatabaseNotification::find($id);
        
        if ($notification) {
            // Mark as read when selected only if unread
            if ($notification->unread()) {
                $notification->markAsRead();
                $this->dispatch('notification-read'); // Fixed event name to match navigation
                $this->loadNotifications(); // Reload to update read status
            }

            // Prepare basic notification data
            $this->selectedNotificationData = [
                'type' => $notification->data['type'] ?? null,
                'message' => $notification->data['message'] ?? '',
                'created_at' => $notification->created_at,
                'unread' => $notification->unread(),
            ];

            // Load requirement and submission details with related user data
            $this->loadDetails($notification->data);
        }
    }

    protected function loadDetails($notificationData)
    {
        // Load requirement with creator, updater, and archiver information
        $requirement = \App\Models\Requirement::with(['creator', 'updater', 'archiver'])
            ->find($notificationData['requirement_id']);

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

        // Handle both old (single submission) and new (multiple submissions) formats
        $submissionIds = [];
        
        if (isset($notificationData['submission_ids'])) {
            // New format with multiple submissions
            $submissionIds = $notificationData['submission_ids'];
        } elseif (isset($notificationData['submission_id'])) {
            // Old format with single submission
            $submissionIds = [$notificationData['submission_id']];
        }

        // Load submissions with course information
        $submissions = \App\Models\SubmittedRequirement::with(['reviewer', 'course'])
            ->whereIn('id', $submissionIds)
            ->get();

        if ($submissions->count() > 0) {
            $this->selectedNotificationData['submissions'] = $submissions->map(function ($submission) {
                $statusLabel = match($submission->status) {
                    SubmittedRequirement::STATUS_UNDER_REVIEW => 'Under Review',
                    SubmittedRequirement::STATUS_REVISION_NEEDED => 'Revision Needed',
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
                    ] : null,
                    'reviewer' => $submission->reviewer ? [
                        'id' => $submission->reviewer->id,
                        'name' => $submission->reviewer->name,
                        'email' => $submission->reviewer->email,
                    ] : null,
                    'needs_review' => $submission->status === SubmittedRequirement::STATUS_UNDER_REVIEW,
                ];
            })->toArray();

            $this->loadFiles($submissions);
        }

        // Load submitting user
        $userId = $notificationData['user_id'] ?? null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $this->selectedNotificationData['submitter'] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            }
        }

        // Load course from notification data if available
        if (isset($notificationData['course_id']) && $notificationData['course_id']) {
            $course = \App\Models\Course::find($notificationData['course_id']);
            if ($course) {
                $this->selectedNotificationData['course'] = [
                    'id' => $course->id,
                    'course_code' => $course->course_code,
                    'course_name' => $course->course_name,
                ];
            }
        }
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
                    ] : null,
                ];
            }
        }

        $this->selectedNotificationData['files'] = $files;
    }

    public function updateFileStatus($submissionId)
    {
        $this->validate([
            'newStatus' => 'required|in:' . implode(',', array_keys(SubmittedRequirement::statuses())),
            'adminNotes' => 'nullable|string',
        ]);

        $submission = SubmittedRequirement::findOrFail($submissionId);
        
        $submission->update([
            'status' => $this->newStatus,
            'admin_notes' => $this->adminNotes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Reload data
        if ($this->selectedNotification) {
            $notification = DatabaseNotification::find($this->selectedNotification);
            if ($notification) {
                $this->loadDetails($notification->data);
            }
        }

        // Reset and show success message
        $this->reset(['newStatus', 'adminNotes']);
        
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
            ->where('type', 'App\Notifications\NewSubmissionNotification')
            ->update(['read_at' => now()]);
        
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        
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
            ->where('type', 'App\Notifications\NewSubmissionNotification')
            ->get();
        
        $readNotifications->each(function ($notification) {
            $notification->markAsUnread();
        });
        
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        
        $this->dispatch('notifications-marked-unread', count: $readNotifications->count());
        
        $this->dispatch('showNotification', 
            type: 'success',
            content: 'All notifications marked as unread!',
            duration: 3000
        );
    }

    public function toggleNotificationReadStatus($notificationId)
    {
        $notificationId = urldecode($notificationId);
        
        // Get the notification scoped to the current user
        $notification = Auth::user()->notifications()
            ->where('id', $notificationId)
            ->where('type', 'App\Notifications\NewSubmissionNotification')
            ->first();
        
        if ($notification) {
            if ($notification->unread()) {
                $notification->markAsRead();
                $message = 'Notification marked as read';
                $this->dispatch('notification-read');
            } else {
                $notification->markAsUnread();
                $message = 'Notification marked as unread';
                $this->dispatch('notification-unread');
            }
            
            $this->loadNotifications();
            
            if ($this->selectedNotification === $notificationId) {
                $this->selectedNotificationData['unread'] = $notification->unread();
            }
            
            $this->dispatch('showNotification', 
                type: 'success',
                content: $message,
                duration: 3000
            );
        }
    }

    // Delete confirmation modal methods
    public function openDeleteConfirmationModal($notificationId)
    {
        $this->notificationToDelete = $notificationId;
        $this->showDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->showDeleteConfirmationModal = false;
        $this->notificationToDelete = null;
    }

    public function confirmDeleteNotification()
    {
        if ($this->notificationToDelete) {
            $this->deleteNotification($this->notificationToDelete);
            $this->closeDeleteConfirmationModal();
        }
    }

    public function deleteNotification($notificationId)
    {
        $notificationId = urldecode($notificationId);
        
        // Get the notification scoped to the current user
        $notification = Auth::user()->notifications()
            ->where('id', $notificationId)
            ->where('type', 'App\Notifications\NewSubmissionNotification')
            ->first();
        
        if ($notification) {
            $wasUnread = $notification->unread();
            $notification->delete();
            
            $this->loadNotifications();
            
            if ($this->selectedNotification === $notificationId) {
                $this->selectedNotification = null;
                $this->selectedNotificationData = null;
            }
            
            if ($wasUnread) {
                $this->dispatch('notification-read');
            }
            
            $this->dispatch('showNotification', 
                type: 'success',
                content: 'Notification deleted successfully!',
                duration: 3000
            );
        }
    }

    public function render()
    {
        return view('livewire.admin.notification.notifications');
    }
}