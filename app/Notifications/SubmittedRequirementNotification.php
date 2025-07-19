<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmittedRequirementNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $message;
    protected $submittedRequirement;
    protected $requirement;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $submittedRequirement = null, $requirement = null)
    {
        $this->title = $title;
        $this->message = $message;

        $this->submittedRequirement = $submittedRequirement;
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
            ->markdown('emails.submitted_requirement', [
                'title' => $this->title,
                'message' => $this->message,
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
            'title' => $this->title,
            'message' => $this->message,
            'requirement' => $this->requirement ?? null,
            'submission' => $this->submittedRequirement ?? null,
        ];
    }
}
