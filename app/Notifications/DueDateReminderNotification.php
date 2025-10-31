<?php
// app/Notifications/DueDateReminderNotification.php

namespace App\Notifications;

use App\Models\Requirement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DueDateReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $requirement;
    public $daysRemaining;

    public function __construct(Requirement $requirement, $daysRemaining)
    {
        $this->requirement = $requirement;
        $this->daysRemaining = $daysRemaining;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        if ($this->daysRemaining > 0) {
            $dayText = $this->daysRemaining == 1 ? 'day' : 'days';
            $message = "Reminder: '{$this->requirement->name}' is due in {$this->daysRemaining} {$dayText}";
        } elseif ($this->daysRemaining == 0) {
            $message = "Reminder: '{$this->requirement->name}' is due today";
        } else {
            $overdueDays = abs($this->daysRemaining);
            $dayText = $overdueDays == 1 ? 'day' : 'days';
            $message = "URGENT: '{$this->requirement->name}' is {$overdueDays} {$dayText} overdue";
        }
        
        return [
            'message' => $message,
            'requirement_id' => $this->requirement->id,
            'type' => 'due_date_reminder',
            'due_date' => $this->requirement->due->toDateTimeString(),
            'days_remaining' => $this->daysRemaining,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}