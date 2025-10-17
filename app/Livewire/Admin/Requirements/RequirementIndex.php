<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\Semester;
use App\Models\User;
use App\Models\Program;
use App\Models\Course;
use App\Models\CourseAssignment;
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
        
        // Return empty collection if no active semester
        if (!$activeSemester || !$activeSemester->is_active) {
            return collect();
        }

        $requirements = Requirement::where('semester_id', $activeSemester->id)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->sortStatus, fn($q) => $q->where('status', $this->sortStatus))
            ->when($this->completionFilter === 'pending', fn($q) => $q->where('due', '>', Carbon::now()))
            ->when($this->completionFilter === 'completed', fn($q) => $q->where('due', '<=', Carbon::now()))
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return $requirements->map(function ($requirement) use ($activeSemester) {
            $assignedTo = $requirement->assigned_to ?? [];
            $count = 0;

            // Check if programs are assigned to this requirement
            if (isset($assignedTo['programs']) && is_array($assignedTo['programs']) && !empty($assignedTo['programs'])) {
                // Get unique professors assigned to courses in the specified programs for active semester
                $professorIds = CourseAssignment::where('semester_id', $activeSemester->id)
                    ->whereHas('course', function ($query) use ($assignedTo) {
                        $query->whereIn('program_id', $assignedTo['programs']);
                    })
                    ->distinct()
                    ->pluck('professor_id')
                    ->toArray();

                $count = count($professorIds);
            } 
            // Check if "select all programs" is enabled
            elseif (isset($assignedTo['selectAllPrograms']) && $assignedTo['selectAllPrograms']) {
                // Get unique professors assigned to any course in active semester
                $professorIds = CourseAssignment::where('semester_id', $activeSemester->id)
                    ->distinct()
                    ->pluck('professor_id')
                    ->toArray();

                $count = count($professorIds);
            }
            // If no specific programs assigned and no select all, count = 0
            else {
                $count = 0;
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