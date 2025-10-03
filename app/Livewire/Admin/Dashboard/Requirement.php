<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement as ModelsRequirement;
use App\Models\Semester;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Requirement extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'asc';

    protected $listeners = ['requirementCreated' => '$refresh'];

    #[Computed()]
    public function sectors()
    {
        return collect([
            'college' => 'College',
            'department' => 'Department',
        ]);
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : 'asc';

        $this->sortField = $field;
    }

    public function showRequirement($requirementId)
    {
        return redirect()->route('admin.requirements.show', ['requirement' => $requirementId]);
    }

    private function getAssignedUsersCount($requirement)
    {
        $assignedTo = json_decode($requirement->assigned_to, true) ?? [];
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
            return 0;
        }
        
        return $userQuery->count();
    }

    public function render()
    {
        // Get the active semester
        $activeSemester = Semester::getActiveSemester();
        
        // Only query requirements if there's an active semester
        $requirementsQuery = ModelsRequirement::query();
        
        if ($activeSemester) {
            $requirementsQuery->where('semester_id', $activeSemester->id);
        } else {
            // Return empty results if no active semester
            $requirementsQuery->whereRaw('1 = 0');
        }

        $requirements = $requirementsQuery
            ->search('name', $this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(20);

        // Add assigned users count to each requirement
        $requirements->getCollection()->transform(function ($requirement) {
            $requirement->assigned_users_count = $this->getAssignedUsersCount($requirement);
            return $requirement;
        });

        return view('livewire.admin.dashboard.requirement', [
            'requirements' => $requirements,
            'colleges' => College::all(),
            'departments' => Department::all(),
            'activeSemester' => $activeSemester, // Pass to view
        ]);
    }
}