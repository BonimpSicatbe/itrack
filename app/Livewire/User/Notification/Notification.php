<?php

namespace App\Livewire\User\Notification;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notification extends Component
{
    public $notifications = [];
    public $selectedNotification = null;

    public function mount()
    {
        $this->notifications = Auth::user()->notifications;
    }

    public function selectNotification($id)
    {
        $this->selectedNotification = $id;
    }

    public function render()
    {
        return view('livewire.user.notification.notification');
    }
}
