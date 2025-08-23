<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Semester;
use Illuminate\Support\Facades\Log;

class AutoArchiveSemesters extends Command
{
    protected $signature = 'semesters:auto-archive';
    protected $description = 'Automatically archive semesters that have passed their end date';

    public function handle()
    {
        $today = now()->format('Y-m-d');
        
        $archivedCount = Semester::where('is_active', true)
                                ->where('end_date', '<', $today)
                                ->update(['is_active' => false]);
        
        if ($archivedCount > 0) {
            $message = "Auto-archived {$archivedCount} expired semester(s)";
            $this->info($message);
            Log::info($message);
        } else {
            $this->info('No semesters needed archiving');
        }
        
        return 0;
    }
}