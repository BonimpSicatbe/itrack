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
        // Get all unread notifications
        $allNotifications = Auth::user()
            ->unreadNotifications()
            ->latest()
            ->take(5)
            ->get();

        // Only filter if the notification has a requirement_id AND you want to check active semester
        // Otherwise, show all notifications
        $this->notifications = $allNotifications->filter(function ($notification) {
            $requirementId = data_get($notification->data, 'requirement_id')
                ?? data_get($notification->data, 'requirement.id');

            // If no requirement_id, show the notification (for system notifications, etc.)
            if (!$requirementId) {
                return true;
            }

            // Check if requirement exists and is in active semester
            $requirement = \App\Models\Requirement::where('id', $requirementId)
                ->whereHas('semester', function ($query) {
                    $query->where('is_active', true);
                })
                ->exists(); // Use exists() instead of first() for better performance

            return $requirement;
        })->values();

        $this->unreadCount = $this->notifications->count();
        
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

    public function markAsRead($notificationId): void
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
            
            // Close dropdown and redirect to notifications page
            $this->showDropdown = false;
            $this->redirect(route('user.notifications'));
        }
    }

    public function viewAllNotifications(): void
    {
        $this->showDropdown = false;
        $this->redirect(route('user.notifications'));
    }

    public function render()
    {
        return view('livewire.user.dashboard.notification');
    }
}