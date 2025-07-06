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
}