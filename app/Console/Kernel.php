<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Main check - runs daily at 8:00 AM
        $schedule->command('submissions:check-missing')
                 ->dailyAt('08:00')
                 ->description('Daily missing submissions check');
        
        // Test command - runs every 5 minutes (great for development)
        $schedule->command('submissions:check-missing --test')
                 ->everyFiveMinutes()
                 ->description('Test missing submissions check');
        
        // More frequent checks during semester end periods
        $schedule->command('submissions:check-missing')
                 ->hourly()
                 ->between('May 15', 'June 30')
                 ->description('End-of-semester intensive check');

        $schedule->command('submissions:check-missing')
                 ->hourly()
                 ->between('December 15', 'December 31')
                 ->description('Year-end intensive check');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}