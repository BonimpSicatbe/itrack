<?php
// app/Services/DueDateReminderService.php

namespace App\Services;

use App\Models\Requirement;
use App\Notifications\DueDateReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DueDateReminderService
{
    public function checkAllReminders()
    {
        $reminderIntervals = [7, 3, 1, 0, -1]; // 1 week, 3 days, 1 day, due today, overdue
        $totalSent = 0;

        foreach ($reminderIntervals as $days) {
            $totalSent += $this->checkRemindersForInterval($days);
        }

        return $totalSent;
    }

    protected function checkRemindersForInterval($days)
    {
        $now = Carbon::now();
        
        if ($days >= 0) {
            // Future due dates (7, 3, 1 days from now)
            $targetDate = $now->copy()->addDays($days);
            $requirements = Requirement::with(['semester'])
                ->whereHas('semester', function($query) {
                    $query->where('is_active', true);
                })
                ->where('status', 'pending')
                ->whereBetween('due', [
                    $targetDate->copy()->startOfDay(),
                    $targetDate->copy()->endOfDay()
                ])
                ->get();
        } else {
            // Overdue requirements (past due dates)
            $requirements = Requirement::with(['semester'])
                ->whereHas('semester', function($query) {
                    $query->where('is_active', true);
                })
                ->where('status', 'pending')
                ->where('due', '<=', $now->copy()->subDay()) // Overdue by at least 1 day
                ->get();
        }

        $sentCount = 0;

        foreach ($requirements as $requirement) {
            // Check if reminder was already sent for this specific interval
            $alreadySent = DB::table('notifications')
                ->where('notifiable_type', 'App\Models\User')
                ->where('type', 'App\Notifications\DueDateReminderNotification')
                ->where('data->requirement_id', $requirement->id)
                ->where('data->days_remaining', $days)
                ->whereDate('created_at', today())
                ->exists();

            if (!$alreadySent) {
                $assignedUsers = $requirement->getAssignedUsers();
                
                foreach ($assignedUsers as $user) {
                    $user->notify(new DueDateReminderNotification($requirement, $days));
                }
                
                $sentCount += $assignedUsers->count();
            }
        }

        return $sentCount;
    }
}