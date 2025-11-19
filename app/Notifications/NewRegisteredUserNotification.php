<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRegisteredUserNotification extends Notification
{
    use Queueable;

    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Notification channels
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Data stored in the database
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'new_registered_user',
            'message' => 'A new user has registered: ' . $this->user->name,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'email' => $this->user->email,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Array representation fallback
     */
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
