<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement as ModelsRequirement;
use App\Models\Semester;
use App\Models\User;
use App\Models\CourseAssignment;
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

    public function getAssignedUsersCount($requirement)
    {
        $activeSemester = Semester::getActiveSemester();
        if (!$activeSemester) {
            return 0;
        }

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

        return $count;
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