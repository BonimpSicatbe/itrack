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

    // Remove the automatic refresh listener since we'll handle it manually
    protected $listeners = [];

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
        // Add some debug logging
        logger('Sort button clicked', ['field' => $field, 'current_sort' => $this->sortField]);
        
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
        
        logger('Sort updated', ['new_field' => $this->sortField, 'direction' => $this->sortDirection]);
        
        // Reset pagination to first page when sorting
        $this->resetPage();
        
        // The component will automatically re-render, no need to force refresh
    }

    public function markAsDone($requirementId)
    {
        try {
            $user = Auth::user();
            
            // Validate the requirement exists and belongs to the user
            $requirement = $user->requirements()->find($requirementId);
            
            if (!$requirement) {
                session()->flash('error', 'Requirement not found or you do not have permission to modify it.');
                return;
            }
            
            // Create a submission indicator
            RequirementSubmissionIndicator::updateOrCreate([
                'requirement_id' => $requirementId,
                'user_id' => $user->id,
            ], [
                'submitted_at' => now(),
            ]);
            
            // Show success notification
            session()->flash('message', 'Requirement marked as done successfully!');
            
        } catch (\Exception $e) {
            logger('Error marking requirement as done', [
                'requirement_id' => $requirementId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'An error occurred while marking the requirement as done.');
        }
    }

    public function markAsUndone($requirementId)
    {
        try {
            $user = Auth::user();
            
            // Validate the requirement exists and belongs to the user
            $requirement = $user->requirements()->find($requirementId);
            
            if (!$requirement) {
                session()->flash('error', 'Requirement not found or you do not have permission to modify it.');
                return;
            }
            
            // Delete the submission indicator
            $deleted = RequirementSubmissionIndicator::where('requirement_id', $requirementId)
                ->where('user_id', $user->id)
                ->delete();
            
            if ($deleted) {
                session()->flash('message', 'Requirement marked as undone successfully!');
            } else {
                session()->flash('error', 'No submission record found to undo.');
            }
            
        } catch (\Exception $e) {
            logger('Error marking requirement as undone', [
                'requirement_id' => $requirementId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'An error occurred while marking the requirement as undone.');
        }
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

        $query = $user->requirements()
            ->where('semester_id', $activeSemester->id); // Only requirements from active semester

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply completion filter
        if ($this->completionFilter === 'submitted') {
            $query->whereIn('id', $submittedRequirementIds);
        } elseif ($this->completionFilter === 'pending') {
            $query->whereNotIn('id', $submittedRequirementIds);
        }

        // Apply sorting
        if ($this->sortField === 'priority') {
            // Custom sorting for priority field
            $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low') " . ($this->sortDirection === 'asc' ? 'ASC' : 'DESC'));
        } else {
            // Default sorting for other fields
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $requirements = $query->get();

        return view('livewire.user.requirements.requirements-list', [
            'requirements' => $requirements,
            'completionStatuses' => $this->completionStatuses,
        ]);
    }
}