<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Semester;
use App\Models\User;
use App\Models\Requirement;
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

        // Get users who have submitted at least one requirement
        $activeSubmitters = User::query()
            ->select('users.id', 'users.firstname', 'users.lastname')
            ->join('submitted_requirements', 'users.id', '=', 'submitted_requirements.user_id')
            ->join('requirements', 'submitted_requirements.requirement_id', '=', 'requirements.id')
            ->whereBetween('submitted_requirements.submitted_at', [
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
                $submissions = DB::table('submitted_requirements')
                    ->where('user_id', $user->id)
                    ->where('requirement_id', $requirement->id)
                    ->whereBetween('submitted_at', [
                        $semester->start_date,
                        $semester->end_date
                    ])
                    ->get();

                if ($submissions->count() > 0) {
                    $totalSubmitted += $submissions->count();
                    
                    $approvedCount = $submissions->where('status', 'approved')->count();
                    $totalApproved += $approvedCount;

                    // Calculate contribution to completion rate
                    $reqPercentage = 100 / $totalRequirements;
                    $perSubmissionValue = $reqPercentage / $submissions->count();
                    $completionRate += $approvedCount * $perSubmissionValue;
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

        // Change from grouped to exact types
        $this->storageStats = $mediaStats->mapWithKeys(function ($item) {
            return [$item->mime_type => $item->total_size];
        })->toArray();

        $this->totalStorage = array_sum($this->storageStats);
        $this->dispatch('refreshCharts');
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