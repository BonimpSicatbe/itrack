<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationToast extends Component
{
    public $messages = [];
    public $show = false;

    protected $listeners = [
        'showNotification' => 'showNotification',
        'hideNotification' => 'hide'
    ];

    public function showNotification($type, $content, $duration = 5000)
    {
        // Add the new message to the messages array
        $this->messages[] = [
            'type' => $type,
            'content' => $content,
            'duration' => $duration,
            'id' => uniqid()
        ];
        
        // Show the notification
        $this->show = true;
        
        // If there are too many messages, remove the oldest one
        if (count($this->messages) > 5) {
            array_shift($this->messages);
        }
        
        $this->dispatch('notificationsUpdated');
    }

    public function removeMessage($id)
    {
        $this->messages = array_filter($this->messages, function($message) use ($id) {
            return $message['id'] !== $id;
        });
        
        // Hide notification if no messages left
        if (empty($this->messages)) {
            $this->show = false;
        }
        
        $this->dispatch('notificationsUpdated');
    }

    public function hide()
    {
        $this->show = false;
        $this->messages = [];
    }

    public function render()
    {
        return view('livewire.notification-toast');
    }
}