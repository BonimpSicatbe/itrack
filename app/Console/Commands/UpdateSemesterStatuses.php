<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateSemesterStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'semesters:update-status {--test : Run in test mode with verbose output}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Update semester active statuses based on current date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isTest = $this->option('test');
        $today = Carbon::today()->format('Y-m-d');
        
        if ($isTest) {
            $this->info("🧪 TEST MODE: Updating semester statuses for {$today}...");
        } else {
            $this->info("📅 Updating semester statuses for {$today}...");
        }
        
        Log::info("Semester status update started for {$today}");
        
        // Get current active semester before changes (for logging)
        $previousActive = Semester::where('is_active', true)->first();
        if ($previousActive && $isTest) {
            $this->info("📊 Previously active semester: {$previousActive->name}");
        }
        
        // Deactivate all semesters first
        $deactivatedCount = Semester::where('is_active', true)->update(['is_active' => false]);
        
        if ($isTest) {
            $this->info("🔴 Deactivated {$deactivatedCount} previously active semesters");
        }
        
        // Find and activate the current semester
        $currentSemester = Semester::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
            
        if ($currentSemester) {
            $currentSemester->update(['is_active' => true]);
            if ($isTest) {
                $this->info("✅ Activated current semester: {$currentSemester->name}");
                $this->info("   Start Date: {$currentSemester->start_date->format('M d, Y')}");
                $this->info("   End Date: {$currentSemester->end_date->format('M d, Y')}");
            }
            Log::info("Semester activated: {$currentSemester->name}");
        } else {
            if ($isTest) {
                $this->warn("❌ No active semester found for today's date ({$today})");
            }
            Log::info("No active semester found for {$today}");
        }
        
        // Also deactivate past semesters explicitly (in case any were missed)
        $pastSemesters = Semester::where('end_date', '<', $today)
            ->where('is_active', true)
            ->get();
            
        foreach ($pastSemesters as $semester) {
            $semester->update(['is_active' => false]);
            if ($isTest) {
                $this->info("🔴 Deactivated past semester: {$semester->name}");
            }
            Log::info("Past semester deactivated: {$semester->name}");
        }
        
        // Log the changes
        if ($previousActive && $currentSemester && $previousActive->id !== $currentSemester->id) {
            Log::info("Semester status changed: {$previousActive->name} → {$currentSemester->name}");
            if ($isTest) {
                $this->info("🔄 Status changed: {$previousActive->name} → {$currentSemester->name}");
            }
        } elseif ($previousActive && !$currentSemester) {
            Log::info("Semester status changed: {$previousActive->name} → No active semester");
            if ($isTest) {
                $this->info("🔄 Status changed: {$previousActive->name} → No active semester");
            }
        } elseif (!$previousActive && $currentSemester) {
            Log::info("Semester status changed: No active semester → {$currentSemester->name}");
            if ($isTest) {
                $this->info("🔄 Status changed: No active semester → {$currentSemester->name}");
            }
        }
        
        if ($isTest) {
            $this->info('🎉 Test completed successfully!');
        } else {
            $this->info('🎉 Semester statuses updated successfully!');
        }
        
        return 0;
    }
}