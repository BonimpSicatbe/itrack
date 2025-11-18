<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MissingSubmissionService;
use App\Notifications\SemesterEndedWithMissingSubmissions;
use App\Models\User;
use App\Models\Semester;
use Illuminate\Support\Facades\Log;

class CheckMissingSubmissions extends Command
{
    protected $signature = 'submissions:check-missing 
                            {--test : Run in test mode without sending notifications}
                            {--force : Force check even if semester hasn\'t ended}
                            {--semester= : Check specific semester ID}';
    
    protected $description = 'Check for missing submissions when semesters end and notify admins';

    protected $missingSubmissionService;

    public function __construct(MissingSubmissionService $missingSubmissionService)
    {
        parent::__construct();
        $this->missingSubmissionService = $missingSubmissionService;
    }

    public function handle()
    {
        $testMode = $this->option('test');
        $force = $this->option('force');
        $specificSemester = $this->option('semester');
        
        $this->info('🚀 Checking for missing submissions...');
        
        if ($specificSemester) {
            $this->checkSpecificSemester($specificSemester, $testMode, $force);
        } else {
            $this->checkAllSemesters($testMode, $force);
        }
    }

    protected function checkAllSemesters($testMode, $force)
    {
        $notificationsData = $this->missingSubmissionService->checkMissingSubmissionsForEndedSemesters($force);

        if (empty($notificationsData)) {
            $this->info('✅ No missing submissions found for recently ended semesters.');
            return;
        }

        $this->sendNotifications($notificationsData, $testMode);
    }

    protected function checkSpecificSemester($semesterId, $testMode, $force)
    {
        $semester = Semester::find($semesterId);
        
        if (!$semester) {
            $this->error("❌ Semester ID {$semesterId} not found.");
            return;
        }

        $this->info("🔍 Checking semester: {$semester->name} (ID: {$semester->id})");
        
        $missingSubmissions = $this->missingSubmissionService->getMissingSubmissionsForSemester($semester);
        
        if ($missingSubmissions->isEmpty()) {
            $this->info("✅ No missing submissions found for semester: {$semester->name}");
            return;
        }

        $this->info("❌ Found {$missingSubmissions->count()} missing submissions for semester: {$semester->name}");
        
        // Display summary
        $this->table(
            ['Requirement', 'User', 'Email', 'Due Date'],
            $missingSubmissions->map(function($item) {
                return [
                    $item['requirement_name'],
                    $item['user_name'],
                    $item['user_email'],
                    \Carbon\Carbon::parse($item['due_date'])->format('M d, Y')
                ];
            })
        );

        if (!$testMode && $this->confirm('Send notifications to admins?')) {
            $notificationsData = [['semester' => $semester, 'missing_submissions' => $missingSubmissions]];
            $this->sendNotifications($notificationsData, false);
        }
    }

    protected function sendNotifications($notificationsData, $testMode)
    {
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        if ($adminUsers->isEmpty()) {
            $this->error('❌ No admin users found to notify.');
            return;
        }

        foreach ($notificationsData as $notificationData) {
            $semesterName = $notificationData['semester']->name;
            $missingCount = $notificationData['missing_submissions']->count();
            
            if ($testMode) {
                $this->info("[TEST] 📧 Would notify {$adminUsers->count()} admins about {$missingCount} missing submissions for: {$semesterName}");
            } else {
                foreach ($adminUsers as $admin) {
                    $admin->notify(new SemesterEndedWithMissingSubmissions(
                        $notificationData['semester'],
                        $notificationData['missing_submissions']
                    ));
                }
                $this->info("✅ Notified {$adminUsers->count()} admins about {$missingCount} missing submissions for: {$semesterName}");
                
                Log::info('Missing submissions notification sent', [
                    'semester' => $semesterName,
                    'missing_count' => $missingCount,
                    'admin_count' => $adminUsers->count()
                ]);
            }
        }
    }
}