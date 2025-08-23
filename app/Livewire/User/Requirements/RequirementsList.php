<?php

namespace App\Livewire\user\Requirements;

use Livewire\Component;
use App\Models\Requirement;
use App\Models\Semester;
use Livewire\WithPagination;
use App\Models\RequirementSubmissionIndicator;
use Illuminate\Support\Facades\Auth;

class RequirementsList extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $completionFilter = '';
    public $sortField = 'due';
    public $sortDirection = 'asc';
    public $completionStatuses = [];
    public $viewMode = 'list';

    protected $queryString = [
        'search' => ['except' => ''],
        'completionFilter' => ['except' => ''],
        'sortField' => ['except' => 'due'],
        'sortDirection' => ['except' => 'asc'],
        'viewMode' => ['except' => 'list'],
    ];

    protected $listeners = ['requirement-marked-done' => '$refresh'];

    public function mount()
    {
        $this->completionStatuses = [
            'submitted' => 'Submitted',
            'pending' => 'Pending Submission'
        ];
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function markAsDone($requirementId)
    {
        $user = Auth::user();
        
        // Create a submission indicator
        RequirementSubmissionIndicator::firstOrCreate([
            'requirement_id' => $requirementId,
            'user_id' => $user->id,
        ], [
            'submitted_at' => now(),
        ]);
        
        // Show success notification
        $this->dispatch('showNotification', 'success', 'Requirement marked as done successfully!');
        
        // Refresh the component to update the UI
        $this->dispatch('requirement-marked-done');
    }

    public function markAsUndone($requirementId)
    {
        $user = Auth::user();
        
        // Delete the submission indicator
        RequirementSubmissionIndicator::where('requirement_id', $requirementId)
            ->where('user_id', $user->id)
            ->delete();
        
        // Show success notification
        $this->dispatch('showNotification', 'success', 'Requirement marked as undone successfully!');
        
        // Refresh the component to update the UI
        $this->dispatch('requirement-marked-done');
    }

    public function isRequirementSubmitted($requirementId)
    {
        $user = Auth::user();
        
        return RequirementSubmissionIndicator::where('requirement_id', $requirementId)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function render()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Get active semester
        $activeSemester = Semester::getActiveSemester();
        
        if (!$activeSemester) {
            return view('livewire.user.requirements.requirements-list', [
                'requirements' => collect(),
                'completionStatuses' => $this->completionStatuses,
            ]);
        }

        // Get all requirement IDs that the user has submitted
        $submittedRequirementIds = RequirementSubmissionIndicator::where('user_id', $userId)
            ->pluck('requirement_id')
            ->toArray();

        $requirements = $user->requirements()
            ->where('semester_id', $activeSemester->id) // Only requirements from active semester
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->completionFilter, function ($query) use ($submittedRequirementIds) {
                if ($this->completionFilter === 'submitted') {
                    $query->whereIn('id', $submittedRequirementIds);
                } else if ($this->completionFilter === 'pending') {
                    $query->whereNotIn('id', $submittedRequirementIds);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return view('livewire.user.requirements.requirements-list', [
            'requirements' => $requirements,
            'completionStatuses' => $this->completionStatuses,
        ]);
    }
}