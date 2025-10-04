<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\Semester;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class RequirementIndex extends Component
{
    public $search = '';
    public $sortStatus = '';
    public $completionFilter = 'all';
    public $sortField = 'due';
    public $sortDirection = 'desc';
    public $requirementToDelete = null;
    public $showDeleteModal = false;
    public $isDeleting = false;
    public $viewMode = 'list';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortStatus' => ['except' => ''],
        'completionFilter' => ['except' => 'all'],
        'sortField' => ['except' => 'due'],
        'sortDirection' => ['except' => 'desc'],
        'viewMode' => ['except' => 'list']
    ];

    public function mount()
    {
        // Set default view mode - list for desktop by default
        $this->viewMode = 'list';
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function changeViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function showRequirement($requirementId)
    {
        return redirect()->route('admin.requirements.show', ['requirement' => $requirementId]);
    }

    public function createRequirement()
    {
        return redirect()->route('admin.requirements.create');
    }

    public function confirmDelete($requirementId)
    {
        $this->requirementToDelete = $requirementId;
        $this->showDeleteModal = true;
    }

    public function deleteRequirement()
    {
        $this->isDeleting = true;
        
        try {
            $requirement = Requirement::findOrFail($this->requirementToDelete);
            $requirement->clearMediaCollection('requirementRequiredFiles');
            $requirement->delete();
            
            $this->resetDeleteModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'Requirement deleted successfully.',
                duration: 3000
            );
        } catch (\Exception $e) {
            $this->resetDeleteModal();
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to delete requirement.',
                duration: 3000
            );
            Log::error('Requirement deletion failed', ['error' => $e->getMessage()]);
        } finally {
            $this->isDeleting = false; 
        }
    }

    protected function resetDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->requirementToDelete = null;
    }

    protected $listeners = [
        'requirement-created' => '$refresh',
        'showNotification' => 'showNotification'
    ];

    #[Computed]
    public function activeSemester()
    {
        return Semester::getActiveSemester();
    }

    #[Computed]
    public function requirements()
    {
        $activeSemester = $this->activeSemester();
        
        if (!$activeSemester) {
            return collect();
        }

        $requirements = Requirement::where('semester_id', $activeSemester->id)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->sortStatus, fn($q) => $q->where('status', $this->sortStatus))
            ->when($this->completionFilter === 'pending', fn($q) => $q->where('due', '>', Carbon::now()))
            ->when($this->completionFilter === 'completed', fn($q) => $q->where('due', '<=', Carbon::now()))
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return $requirements->map(function ($requirement) {
            // assigned_to is now automatically an array due to the cast
            $assignedTo = $requirement->assigned_to ?? [];
            
            $userQuery = User::query();
            $hasConditions = false;
            
            // Specific colleges AND departments combination
            if (isset($assignedTo['colleges']) && is_array($assignedTo['colleges']) && 
                isset($assignedTo['departments']) && is_array($assignedTo['departments'])) {
                
                $userQuery->where(function ($query) use ($assignedTo) {
                    // Users in assigned colleges
                    $query->whereIn('college_id', $assignedTo['colleges'])
                        // AND in assigned departments
                        ->whereIn('department_id', $assignedTo['departments']);
                });
                $hasConditions = true;
            }
            // Only colleges assigned
            elseif (isset($assignedTo['colleges']) && is_array($assignedTo['colleges'])) {
                $userQuery->whereIn('college_id', $assignedTo['colleges']);
                $hasConditions = true;
            }
            // Only departments assigned  
            elseif (isset($assignedTo['departments']) && is_array($assignedTo['departments'])) {
                $userQuery->whereIn('department_id', $assignedTo['departments']);
                $hasConditions = true;
            }
            
            // Handle "select all" cases
            if (isset($assignedTo['selectAllColleges']) && $assignedTo['selectAllColleges']) {
                $userQuery->orWhereNotNull('college_id');
                $hasConditions = true;
            }
            
            if (isset($assignedTo['selectAllDepartments']) && $assignedTo['selectAllDepartments']) {
                $userQuery->orWhereNotNull('department_id');
                $hasConditions = true;
            }
            
            if (!$hasConditions) {
                $count = 0;
            } else {
                $count = $userQuery->count();
            }
            
            $requirement->assigned_users_count = $count;
            return $requirement;
        });
    }

    public function render()
    {
        return view('livewire.admin.requirements.requirement-index', [
            'requirements' => $this->requirements,
            'activeSemester' => $this->activeSemester,
        ]);
    }
}