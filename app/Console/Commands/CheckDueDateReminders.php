<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DueDateReminderService;

class CheckDueDateReminders extends Command
{
    protected $signature = 'reminders:check-due-dates {--test}';
    protected $description = 'Check and send due date reminders for requirements';

    public function handle()
    {
        $reminderService = new DueDateReminderService();
        
        $this->info('Checking due date reminders...');
        
        $sentCount = $reminderService->checkAllReminders();
        
        if ($this->option('test')) {
            $this->info("[TEST MODE] Would have sent {$sentCount} due date reminders");
        } else {
            $this->info("Successfully sent {$sentCount} due date reminders");
            
            // Log the execution
            \Log::info("Due date reminders sent: {$sentCount}", [
                'date' => now()->toDateString(),
                'time' => now()->toTimeString()
            ]);
        }
        
        return Command::SUCCESS;
    }
}