<?php

namespace App\Livewire\User\Recents;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SubmittedRequirement;

class RecentSubmissionsList extends Component
{
    public $recentSubmissions;
    public $statusFilter = '';
    public $search = '';
    public $statuses = [
        'under_review' => 'Under Review',
        'revision_needed' => 'Revision Needed',
        'rejected' => 'Rejected',
        'approved' => 'Approved'
    ];

    protected $queryString = [
        'statusFilter',
        'search' => ['except' => '', 'as' => 'q'] // Optional: makes URL cleaner
    ];

    public function mount()
    {
        $this->loadRecentSubmissions();
    }

    public function updated($property)
    {
        // Trigger reload when either search or filter changes
        if (in_array($property, ['search', 'statusFilter'])) {
            $this->loadRecentSubmissions();
        }
    }

    public function loadRecentSubmissions()
    {
        $query = SubmittedRequirement::where('user_id', Auth::id())
            ->with(['requirement', 'submissionFile', 'reviewer'])
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'desc');

        // Apply status filter if selected
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply search filter if text entered
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('requirement', function($q) {
                    $q->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('submissionFile', function($q) {
                    $q->where('file_name', 'like', '%'.$this->search.'%');
                });
            });
        }

        $this->recentSubmissions = $query->get();
    }

    public function showRequirementDetail($submissionId)
    {
        $submission = SubmittedRequirement::find($submissionId);
        $this->dispatch('showRequirementDetail', 
            requirementId: $submission->requirement_id,
            preserveState: true // Preserves filter/search state
        );
    }

    public function render()
    {
        return view('livewire.user.recents.recent-submissions-list');
    }
}