<?php

namespace App\Notifications;

use App\Models\Requirement;
use App\Models\Course;
use Illuminate\Support\Collection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewSubmissionNotification extends Notification
{
    use Queueable;

    public $requirement;
    public $submissions;
    public $course;

    public function __construct(Requirement $requirement, Collection $submissions)
    {
        $this->requirement = $requirement;
        $this->submissions = $submissions;
        // Get the course from the first submission
        $this->course = $submissions->first() ? Course::find($submissions->first()->course_id) : null;
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
            'message' => 'New submission for requirement: ' . $this->requirement->name . 
                        ($this->course ? ' (' . $this->course->course_code . ')' : ''),
            'requirement_id' => $this->requirement->id,
            'course_id' => $this->course ? $this->course->id : null,
            'course_code' => $this->course ? $this->course->course_code : null,
            'submission_ids' => $this->submissions->pluck('id')->toArray(),
            'user_id' => $firstSubmission ? $firstSubmission->user_id : null,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}