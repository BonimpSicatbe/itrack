<?php

namespace App\Notifications;

use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSubmissionNotification extends Notification
{
    use Queueable;

    public $requirement;
    public $submission;

    public function __construct(Requirement $requirement, SubmittedRequirement $submission)
    {
        $this->requirement = $requirement;
        $this->submission = $submission;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'New submission for requirement: ' . $this->requirement->name,
            'requirement_id' => $this->requirement->id,
            'submission_id' => $this->submission->id,
            'type' => 'new_submission',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}