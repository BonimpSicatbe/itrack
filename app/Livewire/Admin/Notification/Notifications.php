<?php

namespace App\Livewire\Admin\Notification;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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
        $this->notifications = Auth::user()->notifications()->latest()->get();
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

            // Prepare the data for display
            $this->selectedNotificationData = [
                'type' => $notification->data['type'] ?? null,
                'message' => $notification->data['message'] ?? '',
                'created_at' => $notification->created_at,
                'unread' => $notification->unread(),
            ];

            // Add the specific data based on notification type
            if (isset($notification->data['type'])) {
                $type = $notification->data['type'];
                $this->selectedNotificationData = array_merge(
                    $this->selectedNotificationData,
                    $notification->data[$type] ?? []
                );
            }

            // Load any associated files if needed
            $this->loadFiles($notification);
        }
    }

    protected function loadFiles($notification)
    {
        $files = [];
        $type = $notification->data['type'] ?? null;
        $itemId = $notification->data[$type]['id'] ?? null;

        if ($type && $itemId) {
            $model = $type === 'new_submission'
                ? \App\Models\Submission::find($itemId)
                : \App\Models\Requirement::find($itemId);

            if ($model && method_exists($model, 'getMedia')) {
                $mediaCollection = $type === 'new_submission' ? 'submissions' : 'requirements';
                foreach ($model->getMedia($mediaCollection) as $media) {
                    $files[] = [
                        'name' => $media->name,
                        'url' => $media->getFullUrl(),
                        'extension' => $media->extension,
                        'size' => $this->formatFileSize($media->size),
                    ];
                }
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