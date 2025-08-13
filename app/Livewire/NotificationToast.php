<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationToast extends Component
{
    public $messages = [];
    public $show = false;
    public $type = '';
    public $content = '';
    public $duration = 5000; // Default duration in milliseconds (5 seconds)

    protected $listeners = [
        'showNotification' => 'show',
        'hideNotification' => 'hide'
    ];

    public function show($type, $content, $duration = null)
    {
        $this->type = $type;
        $this->content = $content;
        $this->duration = $duration ?? $this->duration;
        $this->show = true;
        
        // Dispatch event to auto-hide after duration
        $this->dispatch('hide-notification-after-delay', duration: $this->duration);
    }

    public function hide()
    {
        $this->show = false;
        $this->reset(['type', 'content']);
    }

    public function render()
    {
        return view('livewire.notification-toast');
    }
}