<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\Semester;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class RequirementIndex extends Component
{
    use WithPagination;
    
    public $search = '';
    public $sortStatus = '';
    public $sortAssignedTo = '';
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
        'sortAssignedTo' => ['except' => ''],
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
            return collect()->paginate(20);
        }

        $query = Requirement::where('semester_id', $activeSemester->id)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->sortStatus, fn($q) => $q->where('status', $this->sortStatus))
            ->when($this->sortAssignedTo, fn($q) => $q->where('assigned_to', $this->sortAssignedTo))
            ->when($this->completionFilter === 'pending', fn($q) => $q->where('due', '>', Carbon::now()))
            ->when($this->completionFilter === 'completed', fn($q) => $q->where('due', '<=', Carbon::now()))
            ->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate(20)->through(function ($requirement) {
            $count = 0;
            
            if (College::where('name', $requirement->assigned_to)->exists()) {
                $college = College::where('name', $requirement->assigned_to)->first();
                $count = User::where('college_id', $college->id)->count();
            } elseif (Department::where('name', $requirement->assigned_to)->exists()) {
                $department = Department::where('name', $requirement->assigned_to)->first();
                $count = User::where('department_id', $department->id)->count();
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