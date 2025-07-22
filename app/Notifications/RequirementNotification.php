<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RequirementNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $requirement;

    /**
     * Create a new notification instance.
     */
    public function __construct($requirement)
    {
        $this->requirement = $requirement;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->markdown('mail.requirement-notification', [
                'requirement' => $this->requirement,
            ]);
    }
    /**
     * Get the type casted representation of the notification.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'requirement' => $this->requirement ?? null,
            'message' => 'A new requirement has been assigned to you.',
        ];
    }
}
