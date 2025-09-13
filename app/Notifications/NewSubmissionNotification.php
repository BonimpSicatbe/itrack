<?php

namespace App\Notifications;

use App\Models\Requirement;
use Illuminate\Support\Collection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewSubmissionNotification extends Notification
{
    use Queueable;

    public $requirement;
    public $submissions;

    public function __construct(Requirement $requirement, Collection $submissions)
    {
        $this->requirement = $requirement;
        $this->submissions = $submissions;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        // Get the first submission to get user info
        $firstSubmission = $this->submissions->first();
        
        return [
            'type' => 'new_submission',
            'message' => 'New submission for requirement: ' . $this->requirement->name,
            'requirement_id' => $this->requirement->id,
            'submission_ids' => $this->submissions->pluck('id')->toArray(),
            'user_id' => $firstSubmission ? $firstSubmission->user_id : null,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}