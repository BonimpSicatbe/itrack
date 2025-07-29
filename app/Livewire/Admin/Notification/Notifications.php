<?php

namespace App\Livewire\Admin\Notification;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\SubmittedRequirement;

class Notifications extends Component
{
    public $notifications = [];
    public $selectedNotification = null;
    public $selectedNotificationData = null;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = Auth::user()
            ->notifications()
            ->where('data->type', 'new_submission')
            ->latest()
            ->get();
    }

    public function selectNotification($id)
    {
        $this->selectedNotification = $id;
        $notification = $this->notifications->firstWhere('id', $id);
        
        if ($notification) {
            // Mark as read when selected
            if ($notification->unread()) {
                $notification->markAsRead();
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
                'assigned_to' => $requirement->assigned_to,
                'status' => $requirement->status,
                'priority' => $requirement->priority,
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

        // Load submission with reviewer information
        $submission = \App\Models\SubmittedRequirement::with(['reviewer'])
            ->find($notificationData['submission_id']);

        if ($submission) {
            $statusLabel = match($submission->status) {
                SubmittedRequirement::STATUS_UNDER_REVIEW => 'To Be Reviewed',
                SubmittedRequirement::STATUS_REVISION_NEEDED => 'Revision Needed',
                SubmittedRequirement::STATUS_REJECTED => 'Rejected',
                SubmittedRequirement::STATUS_APPROVED => 'Approved',
                default => ucfirst($submission->status),
            };

            $this->selectedNotificationData['submission'] = [
                'id' => $submission->id,
                'status' => $submission->status,
                'status_label' => $statusLabel,
                'admin_notes' => $submission->admin_notes,
                'submitted_at' => $submission->submitted_at,
                'reviewed_at' => $submission->reviewed_at,
                'reviewer' => $submission->reviewer ? [
                    'id' => $submission->reviewer->id,
                    'name' => $submission->reviewer->name,
                    'email' => $submission->reviewer->email,
                ] : null,
                'needs_review' => $submission->status === SubmittedRequirement::STATUS_UNDER_REVIEW,
            ];

            $this->loadFiles($submission);
        }

        // Load submitting user
        $user = \App\Models\User::find($notificationData['user_id']);
        if ($user) {
            $this->selectedNotificationData['submitter'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        }
    }

    protected function loadFiles($submission)
    {
        $files = [];
        
        // Get all submissions for this requirement by this user
        $allSubmissions = SubmittedRequirement::where('requirement_id', $submission->requirement_id)
            ->where('user_id', $submission->user_id)
            ->with('media')
            ->get();

        foreach ($allSubmissions as $sub) {
            foreach ($sub->getMedia('submission_files') as $media) {
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
                    'status' => $sub->status, // Use the submission status for each file
                    'submission_id' => $sub->id, // Track which submission this file belongs to
                ];
            }
        }

        $this->selectedNotificationData['files'] = $files;
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
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
    }

    public function render()
    {
        return view('livewire.admin.notification.notifications');
    }
}