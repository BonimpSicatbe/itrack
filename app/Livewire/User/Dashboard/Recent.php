<?php

namespace App\Livewire\User\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use App\Models\Semester;

class Recent extends Component
{
    public $recentSubmissions;
    public $selectedRequirementId = null;

    public $showAll = false;   //
    public $listView = false;  //

    public function mount()
    {
        $this->loadRecentSubmissions();
    }

    public function loadRecentSubmissions()
    {
        // Get the active semester
        $activeSemester = Semester::where('is_active', true)->first();
        
        if ($activeSemester) {
            $this->recentSubmissions = SubmittedRequirement::where('user_id', Auth::id())
                ->with(['requirement', 'submissionFile', 'reviewer'])
                ->whereNotNull('submitted_at')
                ->whereBetween('submitted_at', [$activeSemester->start_date, $activeSemester->end_date])
                ->orderBy('submitted_at', 'desc')
                ->limit(10)
                ->get();
        } else {
            // No active semester found
            $this->recentSubmissions = collect();
        }
    }

    public function showRequirementDetail($submissionId)
    {
        $this->dispatch('showRecentSubmissionDetail', submissionId: $submissionId);
    }

    public function render()
    {
        return view('livewire.user.dashboard.recent');
    }

    public function recentSubmissions()
    {
        // Get the active semester
        $activeSemester = Semester::where('is_active', true)->first();
        
        if ($activeSemester) {
            $recentSubmissions = SubmittedRequirement::with(['requirement', 'submissionFile'])
                ->where('user_id', auth()->id())
                ->whereNotNull('submitted_at')
                ->whereBetween('submitted_at', [$activeSemester->start_date, $activeSemester->end_date])
                ->latest('submitted_at')
                ->take(10)
                ->get();
        } else {
            $recentSubmissions = collect();
        }

        return view('user.recent-submissions', compact('recentSubmissions'));
    }
}