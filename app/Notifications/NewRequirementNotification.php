<?php

namespace App\Notifications;

use App\Models\Requirement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRequirementNotification extends Notification
{
    use Queueable;

    public $requirement;

    public function __construct(Requirement $requirement)
    {
        $this->requirement = $requirement;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'New requirement created: ' . $this->requirement->name,
            'requirement_id' => $this->requirement->id,
            'type' => 'new_requirement',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}