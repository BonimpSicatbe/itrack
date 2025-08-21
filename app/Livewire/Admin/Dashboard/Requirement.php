<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement as ModelsRequirement;
use App\Models\Semester;
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

        return view('livewire.admin.dashboard.requirement', [
            'requirements' => $requirements,
            'colleges' => College::all(),
            'departments' => Department::all(),
            'activeSemester' => $activeSemester, // Pass to view
        ]);
    }
}