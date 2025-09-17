<?php

namespace App\Livewire\User\Recents;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SubmittedRequirement;
use App\Models\Semester;

class RecentSubmissionsList extends Component
{
    public $recentSubmissions;
    public $statusFilter = '';
    public $search = '';
    public $viewMode = 'list'; // New property for the view mode
    public $statuses = [
        'under_review' => 'Under Review',
        'revision_needed' => 'Revision Needed',
        'rejected' => 'Rejected',
        'approved' => 'Approved'
    ];

    protected $queryString = [
        'statusFilter',
        'search' => ['except' => '', 'as' => 'q'],
        'viewMode' => ['except' => 'list'] // New: Add viewMode to the query string
    ];

    public function mount()
    {
        $this->loadRecentSubmissions();
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'statusFilter'])) {
            $this->loadRecentSubmissions();
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter']);
        $this->loadRecentSubmissions();
    }
    
    // New method to change the view mode
    public function changeViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function loadRecentSubmissions()
    {
        $query = SubmittedRequirement::where('user_id', Auth::id())
            ->with(['requirement', 'submissionFile', 'reviewer'])
            ->whereNotNull('submitted_at')
            ->whereHas('requirement', function ($q) {
                $q->whereHas('semester', function ($semesterQuery) {
                    $semesterQuery->where('is_active', true);
                });
            })
            ->orderBy('submitted_at', 'desc');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

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

    public function showSubmissionDetail($submissionId)
    {
        $this->dispatch('showRecentSubmissionDetail', submissionId: $submissionId);
    }

    public function render()
    {
        return view('livewire.user.recents.recent-submissions-list');
    }
}