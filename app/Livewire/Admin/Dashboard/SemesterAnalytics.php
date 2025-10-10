<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Semester;
use App\Models\User;
use App\Models\Requirement;
use App\Models\RequirementSubmissionIndicator;
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

        // Get users who have submitted at least one requirement using requirement_submission_indicators
        $activeSubmitters = User::query()
            ->select('users.id', 'users.firstname', 'users.lastname')
            ->join('requirement_submission_indicators', 'users.id', '=', 'requirement_submission_indicators.user_id')
            ->join('requirements', 'requirement_submission_indicators.requirement_id', '=', 'requirements.id')
            ->whereBetween('requirement_submission_indicators.submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->groupBy('users.id', 'users.firstname', 'users.lastname')
            ->get();

        $this->userActivityStats = collect();
        
        foreach ($activeSubmitters as $user) {
            $totalSubmitted = 0;
            $totalApproved = 0;
            $completionRate = 0;

            foreach ($requirements as $requirement) {
                // Check if user has submitted this requirement using requirement_submission_indicators
                $submissionIndicator = RequirementSubmissionIndicator::where('user_id', $user->id)
                    ->where('requirement_id', $requirement->id)
                    ->whereBetween('submitted_at', [
                        $semester->start_date,
                        $semester->end_date
                    ])
                    ->first();

                if ($submissionIndicator) {
                    $totalSubmitted++;

                    // Check if there's an approved submission in submitted_requirements
                    // that matches the requirement_id and course_id from the indicator
                    $approvedSubmission = DB::table('submitted_requirements')
                        ->where('user_id', $user->id)
                        ->where('requirement_id', $requirement->id)
                        ->where('course_id', $submissionIndicator->course_id)
                        ->where('status', 'approved')
                        ->whereBetween('submitted_at', [
                            $semester->start_date,
                            $semester->end_date
                        ])
                        ->exists();

                    if ($approvedSubmission) {
                        $totalApproved++;
                    }

                    // Calculate completion rate
                    $reqPercentage = 100 / $totalRequirements;
                    $completionRate += $approvedSubmission ? $reqPercentage : 0;
                }
            }

            $this->userActivityStats->push([
                'name' => $user->firstname . ' ' . $user->lastname,
                'submitted' => $totalSubmitted,
                'approved' => $totalApproved,
                'total_requirements' => $totalRequirements,
                'completion_rate' => round($completionRate, 2)
            ]);
        }

        // Alternative approach using a single query for better performance
        // You can replace the above loop with this more efficient query:
        $this->userActivityStats = $this->calculateUserStatsWithQuery($semester, $requirements, $totalRequirements);

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
     * Alternative more efficient method using SQL joins
     */
    private function calculateUserStatsWithQuery($semester, $requirements, $totalRequirements)
    {
        if ($totalRequirements == 0) {
            return collect();
        }

        $userStats = DB::table('users')
            ->select(
                'users.id',
                'users.firstname',
                'users.lastname',
                DB::raw('COUNT(DISTINCT rsi.requirement_id) as total_submitted'),
                DB::raw('COUNT(DISTINCT CASE WHEN sr.status = "approved" THEN rsi.requirement_id END) as total_approved')
            )
            ->join('requirement_submission_indicators as rsi', 'users.id', '=', 'rsi.user_id')
            ->leftJoin('submitted_requirements as sr', function($join) use ($semester) {
                $join->on('rsi.requirement_id', '=', 'sr.requirement_id')
                     ->on('rsi.user_id', '=', 'sr.user_id')
                     ->on('rsi.course_id', '=', 'sr.course_id')
                     ->where('sr.status', 'approved')
                     ->whereBetween('sr.submitted_at', [
                         $semester->start_date,
                         $semester->end_date
                     ]);
            })
            ->whereBetween('rsi.submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->whereIn('rsi.requirement_id', $requirements->pluck('id'))
            ->groupBy('users.id', 'users.firstname', 'users.lastname')
            ->get();

        return $userStats->map(function($user) use ($totalRequirements) {
            $completionRate = $totalRequirements > 0 ? 
                ($user->total_approved / $totalRequirements) * 100 : 0;

            return [
                'name' => $user->firstname . ' ' . $user->lastname,
                'submitted' => $user->total_submitted,
                'approved' => $user->total_approved,
                'total_requirements' => $totalRequirements,
                'completion_rate' => round($completionRate, 2)
            ];
        });
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