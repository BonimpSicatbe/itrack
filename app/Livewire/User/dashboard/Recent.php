<?php

namespace App\Livewire\User\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SubmittedRequirement;
use App\Models\Requirement;

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
        $this->recentSubmissions = SubmittedRequirement::where('user_id', Auth::id())
            ->with(['requirement', 'submissionFile', 'reviewer'])
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function showRequirementDetail($submissionId)
    {
        $submission = SubmittedRequirement::find($submissionId);
        $this->selectedRequirementId = $submission->requirement_id;
        $this->dispatch('showRequirementDetail', requirementId: $this->selectedRequirementId);
    }

    public function render()
    {
        return view('livewire.user.dashboard.recent');
    }

    public function recentSubmissions()
    {
        $recentSubmissions = SubmittedRequirement::with(['requirement', 'submissionFile'])
            ->where('user_id', auth()->id())
            ->latest('submitted_at')
            ->take(10) // or paginate() if needed
            ->get();

        return view('user.recent-submissions', compact('recentSubmissions'));
    }

}
