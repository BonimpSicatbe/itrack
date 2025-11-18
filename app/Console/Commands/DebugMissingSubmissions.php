<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MissingSubmissionService;
use App\Models\Semester;

class DebugMissingSubmissions extends Command
{
    protected $signature = 'debug:missing-submissions {semester? : The semester ID to debug}';
    protected $description = 'Debug missing submissions detection';

    protected $missingSubmissionService;

    public function __construct(MissingSubmissionService $missingSubmissionService)
    {
        parent::__construct();
        $this->missingSubmissionService = $missingSubmissionService;
    }

    public function handle()
    {
        $semesterId = $this->argument('semester');

        if ($semesterId) {
            $semester = Semester::find($semesterId);
            if (!$semester) {
                $this->error("Semester ID {$semesterId} not found.");
                return;
            }
            $this->debugSingleSemester($semester);
        } else {
            $this->debugAllSemesters();
        }
    }

    protected function debugSingleSemester(Semester $semester)
    {
        $this->info("=== Debugging Semester: {$semester->name} (ID: {$semester->id}) ===");
        
        $debugInfo = $this->missingSubmissionService->debugSemesterCheck($semester);
        
        $this->info("Requirements found: {$debugInfo['requirements_count']}");
        $this->info("Course assignments: {$debugInfo['course_assignments_count']}");
        $this->info("Missing submissions: {$debugInfo['missing_submissions']}");
        
        foreach ($debugInfo['details'] as $detail) {
            $this->info("\nRequirement: {$detail['requirement']} (ID: {$detail['requirement_id']})");
            $this->info("Assigned to (raw): {$detail['assigned_to_raw']}");
            $this->info("Assigned to (parsed): " . json_encode($detail['assigned_to_parsed']));
            $this->info("Due date: {$detail['due_date']}");
            $this->info("Course assignments: {$detail['course_assignments_count']}");
            $this->info("Missing for: " . count($detail['missing_for_assignments']) . " course assignments");
            
            if (!empty($detail['missing_for_assignments'])) {
                foreach ($detail['missing_for_assignments'] as $assignment) {
                    $this->info("    - {$assignment['professor']} (ID: {$assignment['professor_id']}, Active: {$assignment['professor_active']})");
                    $this->info("      -> {$assignment['course']} (Program: {$assignment['program_name']})");
                }
            }
        }

        // Test the actual missing submissions
        $missing = $this->missingSubmissionService->getMissingSubmissionsForSemester($semester);
        $this->info("\n=== ACTUAL MISSING SUBMISSIONS ===");
        $this->info("Total missing: " . $missing->count());
        
        if ($missing->count() > 0) {
            $this->table(
                ['Requirement', 'User', 'Course', 'Email'],
                $missing->map(function($item) {
                    return [
                        $item['requirement_name'],
                        $item['user_name'],
                        $item['course_code'] . ' - ' . $item['course_name'],
                        $item['user_email']
                    ];
                })
            );
        }
    }

    protected function debugAllSemesters()
    {
        $semesters = Semester::all();
        
        $this->info("=== Debugging All Semesters ===");
        
        foreach ($semesters as $semester) {
            $this->info("\nSemester: {$semester->name} (ID: {$semester->id})");
            $this->info("Start: {$semester->start_date}, End: {$semester->end_date}");
            $this->info("Active: " . ($semester->is_active ? 'Yes' : 'No'));
            
            $requirementsCount = \App\Models\Requirement::where('semester_id', $semester->id)->count();
            $facultyCount = \App\Models\User::where('is_active', true)
                ->whereHas('courseAssignments', function($query) use ($semester) {
                    $query->where('semester_id', $semester->id);
                })
                ->count();
                
            $this->info("Requirements: {$requirementsCount}");
            $this->info("Faculty with assignments: {$facultyCount}");
            
            $missing = $this->missingSubmissionService->getMissingSubmissionsForSemester($semester);
            $this->info("Missing submissions: " . $missing->count());
        }
    }
}