<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class RequirementIndex extends Component
{
    public $search = '';
    public $sortStatus = '';
    public $sortAssignedTo = '';
    public $sortDueDate = 'desc';

    public function deleteRequirement($requirementId)
    {
        try {
            $requirement = Requirement::findOrFail($requirementId);
            $requirement->clearMediaCollection('requirementRequiredFiles');
            $requirement->delete();
            $this->reset();
            Log::info('Requirement deleted', ['requirement_id' => $requirementId]);
        } catch (\Exception $e) {
            Log::error('Requirement deletion failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function render()
    {
        $colleges = College::all();
        $departments = Department::all();
        
        $requirements = Requirement::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->sortStatus, function ($query) {
                $query->where('status', $this->sortStatus);
            })
            ->when($this->sortAssignedTo, function ($query) {
                $query->where('assigned_to', $this->sortAssignedTo);
            })
            ->orderBy('due', $this->sortDueDate)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.admin.requirements.requirement-index', [
            'requirements' => $requirements,
            'colleges' => $colleges,
            'departments' => $departments,
        ]);
    }
}