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

class RequirementIndex extends Component
{
    public $search = '';
    public $sortStatus = '';
    public $sortAssignedTo = '';
    public $completionFilter = 'all'; // 'all', 'pending', 'completed'
    
    // Sorting properties
    public $sortBy = 'due'; // default sort field
    public $sortDir = 'desc'; // default sort direction

    // Delete confirmation properties
    public $requirementToDelete = null;
    public $showDeleteModal = false;
    public $isDeleting = false;

    public function confirmDelete($requirementId)
    {
        $this->requirementToDelete = $requirementId;
        $this->showDeleteModal = true;
    }

    public function loadRequirements()
    {
        
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
            Log::info('Requirement deleted', ['requirement_id' => $this->requirementToDelete]);
        } catch (\Exception $e) {
            $this->resetDeleteModal();
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to delete requirement.',
                duration: 3000
            );
            Log::error('Requirement deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
        'requirement-created' => 'loadRequirements',
        'showNotification' => 'showNotification'
    ];

    public function setSort($field)
    {
        if ($this->sortBy === $field) {
            // Reverse the sort direction if same field
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            // Sort by new field, default to ascending
            $this->sortDir = 'asc';
        }
        $this->sortBy = $field;
    }

    public function render()
    {
        $colleges = College::all();
        $departments = Department::all();
        
        // Get the active semester
        $activeSemester = Semester::getActiveSemester();
        
        $requirements = Requirement::query()
            ->when($activeSemester, function ($query) use ($activeSemester) {
                $query->where('semester_id', $activeSemester->id);
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->sortStatus, function ($query) {
                $query->where('status', $this->sortStatus);
            })
            ->when($this->sortAssignedTo, function ($query) {
                $query->where('assigned_to', $this->sortAssignedTo);
            })
            ->when($this->completionFilter === 'pending', function ($query) {
                $query->where('due', '>', Carbon::now());
            })
            ->when($this->completionFilter === 'completed', function ($query) {
                $query->where('due', '<=', Carbon::now());
            })
            ->orderBy($this->sortBy, $this->sortDir)
            ->get()
            ->map(function ($requirement) {
                // Calculate assigned users count without modifying the model
                $count = 0;
                
                if (College::where('name', $requirement->assigned_to)->exists()) {
                    $college = College::where('name', $requirement->assigned_to)->first();
                    $count = User::where('college_id', $college->id)->count();
                } elseif (Department::where('name', $requirement->assigned_to)->exists()) {
                    $department = Department::where('name', $requirement->assigned_to)->first();
                    $count = User::where('department_id', $department->id)->count();
                }
                
                // Add the count as a dynamic property
                $requirement->assigned_users_count = $count;
                return $requirement;
            });

        return view('livewire.admin.requirements.requirement-index', [
            'requirements' => $requirements,
            'colleges' => $colleges,
            'departments' => $departments,
            'activeSemester' => $activeSemester, // Optional: pass to view if needed
        ]);
    }
}