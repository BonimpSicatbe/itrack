<?php

namespace App\Livewire\User\Recents;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SubmittedRequirement;
use App\Models\Semester; // Add this import if you have a Semester model

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

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter']);
        $this->loadRecentSubmissions();
    }

    public function loadRecentSubmissions()
    {
        $query = SubmittedRequirement::where('user_id', Auth::id())
            ->with(['requirement', 'submissionFile', 'reviewer'])
            ->whereNotNull('submitted_at')
            ->whereHas('requirement', function ($q) {
                // Filter by active semester - adjust this based on your database structure
                $q->whereHas('semester', function ($semesterQuery) {
                    $semesterQuery->where('is_active', true);
                });
                // Alternative approach if you have different relationship structure:
                // $q->where('semester_id', $this->getActiveSemesterId());
            })
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

    /**
     * Helper method to get active semester ID if you need it
     * Uncomment and use this if your relationship structure is different
     */
    // private function getActiveSemesterId()
    // {
    //     return Semester::where('is_active', true)->first()?->id;
    // }

    public function showSubmissionDetail($submissionId)
    {
        $this->dispatch('showRecentSubmissionDetail', submissionId: $submissionId);
    }

    public function render()
    {
        return view('livewire.user.recents.recent-submissions-list');
    }
}