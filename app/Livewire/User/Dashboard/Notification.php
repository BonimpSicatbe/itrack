<?php

namespace App\Livewire\User\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Notification extends Component
{
    public $unreadCount = 0;
    public $showDropdown = false;
    public $notifications = [];

    protected $listeners = ['notificationRead' => 'loadNotifications'];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        // Get both read and unread notifications (last 10)
        $allNotifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(10)
            ->get();

        // Filter notifications to only show those related to active semester
        $this->notifications = $allNotifications->filter(function ($notification) {
            // Ensure data is decoded properly
            $data = is_array($notification->data) ? $notification->data : (json_decode($notification->data, true) ?? []);
            
            $requirementId = data_get($data, 'requirement_id')
                ?? data_get($data, 'requirement.id');

            // If no requirement_id, show the notification (for system notifications, etc.)
            if (!$requirementId) {
                return true;
            }

            // Check if requirement exists and is in active semester
            $requirement = \App\Models\Requirement::where('id', $requirementId)
                ->whereHas('semester', function ($query) {
                    $query->where('is_active', true);
                })
                ->exists();

            return $requirement;
        })->values();

        // Count only unread notifications
        $this->unreadCount = $this->notifications->filter(function ($notification) {
            return $notification->unread();
        })->count();
        
        // Dispatch event to update count in other components
        $this->dispatch('unreadCountUpdated', count: $this->unreadCount);
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = !$this->showDropdown;
        
        // Reload notifications when opening dropdown to ensure fresh data
        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
            
            // Dispatch event for other components
            $this->dispatch('notificationRead');
            
            // Close dropdown and navigate to notifications page
            $this->showDropdown = false;
            return $this->redirect(route('user.notifications'), navigate: true);
        }
    }

    public function viewAllNotifications()
    {
        $this->showDropdown = false;
        return $this->redirect(route('user.notifications'), navigate: true);
    }

    public function render()
    {
        return view('livewire.user.dashboard.notification');
    }
}