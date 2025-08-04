<?php

namespace App\Livewire\User\Recents;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SubmittedRequirement;

class RecentSubmissionsList extends Component
{
    public $recentSubmissions;
    public $statusFilter = '';
    public $statuses = [
        'under_review' => 'Under Review',
        'revision_needed' => 'Revision Needed',
        'rejected' => 'Rejected',
        'approved' => 'Approved'
    ];

    protected $queryString = ['statusFilter'];

    public function mount()
    {
        $this->loadRecentSubmissions();
    }

    public function updatedStatusFilter()
    {
        $this->loadRecentSubmissions();
    }

    public function loadRecentSubmissions()
    {
        $query = SubmittedRequirement::where('user_id', Auth::id())
            ->with(['requirement', 'submissionFile', 'reviewer'])
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'desc');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $this->recentSubmissions = $query->get();
    }

    public function showRequirementDetail($submissionId)
    {
        $submission = SubmittedRequirement::find($submissionId);
        $this->dispatch('showRequirementDetail', requirementId: $submission->requirement_id);
    }

    public function render()
    {
        return view('livewire.user.recents.recent-submissions-list');
    }
}