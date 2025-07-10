<?php

namespace App\Livewire\User\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SubmittedRequirement;

class Recent extends Component
{
    public $recentSubmissions;
    public $selectedSubmission = null;

    // public function mount()
    // {
    //     $this->loadRecentSubmissions();
    // }

    // public function loadRecentSubmissions()
    // {
    //     $this->recentSubmissions = SubmittedRequirement::where('user_id', Auth::id())
    //         ->with(['requirement', 'submissionFile', 'reviewer'])
    //         ->whereNotNull('submitted_at')
    //         ->orderBy('submitted_at', 'desc')
    //         ->limit(10)
    //         ->get();
    // }

    // public function selectSubmission($submissionId)
    // {
    //     $this->selectedSubmission = SubmittedRequirement::with([
    //         'requirement',
    //         'submissionFile',
    //         'reviewer'
    //     ])->find($submissionId);
    // }

    // public function closeModal()
    // {
    //     $this->selectedSubmission = null;
    // }

    public function render()
    {
        return view('livewire.user.dashboard.recent');
    }
}
