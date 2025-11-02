<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Semester;
use App\Models\User;
use App\Models\Requirement;
use App\Models\RequirementSubmissionIndicator;
use App\Models\CourseAssignment;
use App\Models\SubmittedRequirement;
use Illuminate\Support\Facades\DB;

class SemesterAnalytics extends Component
{
    public $selectedSemesterId;
    public $userActivityStats = [];
    public $storageStats = [];
    public $semesters = [];
    public $totalStorage = 0;

    protected $listeners = ['refreshCharts'];

    public function mount()
    {
        $this->semesters = Semester::orderBy('end_date', 'desc')->get();
        $this->selectedSemesterId = Semester::active()->first()?->id;
        $this->loadStats();
    }

    public function updatedSelectedSemesterId()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $semester = Semester::find($this->selectedSemesterId);
        
        if (!$semester) {
            $this->reset(['userActivityStats', 'storageStats', 'totalStorage']);
            return;
        }

        // Get all requirements in the semester period
        $requirements = Requirement::whereBetween('created_at', [
            $semester->start_date,
            $semester->end_date
        ])->get();

        $totalRequirements = $requirements->count();
        if ($totalRequirements == 0) {
            $this->userActivityStats = [];
            return;
        }

        // Use the new file-based completion rate calculation
        $this->userActivityStats = $this->calculateUserCompletionByFiles($semester, $requirements, $totalRequirements);

        // Sort by completion rate descending
        $this->userActivityStats = $this->userActivityStats
            ->sortByDesc('completion_rate')
            ->take(10)
            ->values()
            ->toArray();

        // Keep the existing storage stats logic
        $mediaStats = Media::query()
            ->whereBetween('created_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->select('mime_type', DB::raw('sum(size) as total_size'))
            ->groupBy('mime_type')
            ->get();

        $this->storageStats = $mediaStats->mapWithKeys(function ($item) {
            return [$item->mime_type => $item->total_size];
        })->toArray();

        $this->totalStorage = array_sum($this->storageStats);
        $this->dispatch('refreshCharts');
    }

    /**
     * Calculate completion rate based on file approvals per requirement
     */
    private function calculateUserCompletionByFiles($semester, $requirements, $totalRequirements)
    {
        if ($totalRequirements == 0) {
            return collect();
        }

        // Get all active professors with their course assignments for the semester
        $professorsWithCourses = CourseAssignment::where('semester_id', $semester->id)
            ->join('users', 'course_assignments.professor_id', '=', 'users.id')
            ->where('users.is_active', true)
            ->select(
                'users.id as user_id',
                'users.firstname',
                'users.lastname',
                'course_assignments.course_id'
            )
            ->get()
            ->groupBy('user_id');

        $userStats = collect();

        foreach ($professorsWithCourses as $userId => $courseAssignments) {
            $user = $courseAssignments->first();
            $totalUserCompletion = 0;
            $requirementsCounted = 0;

            // For each requirement, calculate completion percentage
            foreach ($requirements as $requirement) {
                $requirementCompletion = $this->calculateRequirementCompletion(
                    $userId, 
                    $requirement->id, 
                    $courseAssignments->pluck('course_id')->toArray(),
                    $semester
                );

                if ($requirementCompletion !== null) {
                    $totalUserCompletion += $requirementCompletion;
                    $requirementsCounted++;
                }
            }

            // Calculate overall completion rate
            $overallCompletionRate = $requirementsCounted > 0 
                ? ($totalUserCompletion / $requirementsCounted) 
                : 0;

            // Get total submitted and approved counts for display
            $submissionStats = $this->getUserSubmissionStats($userId, $semester->id);

            $userStats->push([
                'name' => $user->firstname . ' ' . $user->lastname,
                'submitted' => $submissionStats['submitted_count'],
                'approved' => $submissionStats['approved_count'],
                'total_requirements' => $requirementsCounted,
                'completion_rate' => round($overallCompletionRate, 2)
            ]);
        }

        return $userStats;
    }

    /**
     * Calculate completion percentage for a specific requirement across user's courses
     */
    private function calculateRequirementCompletion($userId, $requirementId, $courseIds, $semester)
    {
        // Get all submissions for this requirement across user's courses
        // Exclude files with "uploaded" status from the count
        $submissions = SubmittedRequirement::where('user_id', $userId)
            ->where('requirement_id', $requirementId)
            ->whereIn('course_id', $courseIds)
            ->whereBetween('submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->where('status', '!=', 'uploaded') // Exclude uploaded status
            ->get();

        if ($submissions->isEmpty()) {
            return 0; // No valid submissions = 0% completion
        }

        $totalSubmissions = $submissions->count();
        $approvedSubmissions = $submissions->where('status', 'approved')->count();

        // Calculate completion percentage for this requirement
        return ($approvedSubmissions / $totalSubmissions) * 100;
    }

    /**
     * Get user's submission statistics for the semester
     */
    private function getUserSubmissionStats($userId, $semesterId)
    {
        $stats = SubmittedRequirement::where('user_id', $userId)
            ->whereHas('requirement', function($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->where('status', '!=', 'uploaded') // Exclude uploaded status from counts
            ->select(
                DB::raw('COUNT(*) as submitted_count'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count')
            )
            ->first();

        return [
            'submitted_count' => $stats->submitted_count ?? 0,
            'approved_count' => $stats->approved_count ?? 0
        ];
    }

    public function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function render()
    {
        return view('livewire.admin.dashboard.semester-analytics');
    }
}