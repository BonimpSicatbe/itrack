<?php

namespace App\Notifications;

use App\Models\Semester;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SemesterEndedWithMissingSubmissions extends Notification implements ShouldQueue
{
    use Queueable;

    public $semester;
    public $missingSubmissions;
    public $totalMissing;

    public function __construct(Semester $semester, $missingSubmissions)
    {
        $this->semester = $semester;
        $this->missingSubmissions = $missingSubmissions;
        $this->totalMissing = $missingSubmissions->count();
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'semester_ended_missing_submissions', // Remove the extra 's'
            'message' => "Semester {$this->semester->name} ended with {$this->totalMissing} missing submissions",
            'semester_id' => $this->semester->id,
            'semester_name' => $this->semester->name,
            'missing_submissions_count' => $this->totalMissing,
            'missing_submissions' => $this->missingSubmissions->take(10)->toArray(),
            'ended_at' => $this->semester->end_date,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}