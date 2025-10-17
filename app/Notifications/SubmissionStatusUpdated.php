<?php

namespace App\Notifications;

use App\Models\SubmittedRequirement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubmissionStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public $submission;
    public $oldStatus;
    public $newStatus;

    public function __construct(SubmittedRequirement $submission, $oldStatus, $newStatus)
    {
        $this->submission = $submission;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $statusLabels = [
            'under_review' => 'Under Review',
            'revision_needed' => 'Revision Required', 
            'rejected' => 'Rejected',
            'approved' => 'Approved'
        ];

        $oldStatusLabel = $statusLabels[$this->oldStatus] ?? ucfirst(str_replace('_', ' ', $this->oldStatus));
        $newStatusLabel = $statusLabels[$this->newStatus] ?? ucfirst(str_replace('_', ' ', $this->newStatus));

        return [
            'type' => 'submission_status_updated',
            'message' => "Your submission for '{$this->submission->requirement->name}' has been updated from {$oldStatusLabel} to {$newStatusLabel}",
            'submission_id' => $this->submission->id,
            'requirement_id' => $this->submission->requirement_id,
            'user_id' => $this->submission->user_id,
            'course_id' => $this->submission->course_id,
            'requirement' => [
                'id' => $this->submission->requirement->id,
                'name' => $this->submission->requirement->name,
            ],
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'admin_notes' => $this->submission->admin_notes,
            'reviewed_by' => auth()->user()->full_name ?? 'Administrator',
            'reviewed_at' => now()->toDateTimeString(),
        ];
    }

    // Add this method to determine which notifications should be stored together
    public function shouldSend($notifiable)
    {
        return true;
    }
}