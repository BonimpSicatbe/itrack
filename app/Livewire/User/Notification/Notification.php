<?php

namespace App\Livewire\User\Notification;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notification extends Component
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

            // Prepare data for display
            $this->selectedNotificationData = [
                'type' => $notification->data['type'] ?? null,
                'message' => $notification->data['message'] ?? '',
                'created_at' => $notification->created_at,
                'unread' => $notification->unread(),
                'data' => $notification->data
            ];

            // Load any files if this is a requirement notification
            if (isset($notification->data['type']) && $notification->data['type'] === 'new_requirement') {
                $this->loadRequirementFiles($notification);
            }
        }
    }

    protected function loadRequirementFiles($notification)
    {
        $files = [];
        $requirementId = $notification->data['requirement']['id'] ?? null;

        if ($requirementId) {
            $requirement = \App\Models\Requirement::find($requirementId);
            if ($requirement && method_exists($requirement, 'getMedia')) {
                foreach ($requirement->getMedia('requirements') as $media) {
                    $files[] = [
                        'name' => $media->name,
                        'url' => $media->getFullUrl(),
                        'type' => $media->mime_type,
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

    public function render()
    {
        return view('livewire.user.notification.notification');
    }
}