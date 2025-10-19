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
    public $viewMode = 'list';
    public $statuses = [
        'under_review' => 'Under Review',
        'revision_needed' => 'Revision Required',
        'rejected' => 'Rejected',
        'approved' => 'Approved'
    ];
    public $activeSemester; // NEW PROPERTY
    public $isUserActive; // NEW PROPERTY

    protected $queryString = [
        'statusFilter',
        'search' => ['except' => '', 'as' => 'q'],
        'viewMode' => ['except' => 'list']
    ];

    public function mount()
    {
        $this->isUserActive = Auth::user()->is_active; // CHECK USER ACTIVE STATUS
        $this->activeSemester = Semester::getActiveSemester(); // CHECK FOR ACTIVE SEMESTER
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

    public function changeViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function loadRecentSubmissions()
    {
        // Don't load submissions if user is inactive
        if (!$this->isUserActive) {
            $this->recentSubmissions = collect(); // Set to empty collection
            return;
        }

        // Only load submissions if an active semester exists
        if (!$this->activeSemester) {
            $this->recentSubmissions = collect(); // Set to empty collection
            return;
        }

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