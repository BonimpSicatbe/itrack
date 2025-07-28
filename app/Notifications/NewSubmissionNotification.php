<?php

namespace App\Notifications;

use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use Illuminate\Bus\Queueable;
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
            'type' => 'new_submission',
            'message' => 'New submission for requirement: ' . $this->requirement->name,
            'requirement_id' => $this->requirement->id,
            'submission_id' => $this->submission->id,
            'user_id' => $this->submission->user_id,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}