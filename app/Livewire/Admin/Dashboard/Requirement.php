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
    public $currentSemester;

    protected $listeners = ['requirementCreated' => '$refresh'];

    public function mount($currentSemester)
    {
        $this->currentSemester = $currentSemester;
    }

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
        $requirements = $this->currentSemester->requirements()
            ->search('name', $this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(20);

        return view('livewire.admin.dashboard.requirement', [
            'requirements' => $requirements,
            'colleges' => College::all(),
            'departments' => Department::all(),
            'activeSemester' => $this->currentSemester, // Pass to view
        ]);
    }
}
