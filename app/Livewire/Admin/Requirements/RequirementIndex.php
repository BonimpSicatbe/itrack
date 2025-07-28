<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement;
use Livewire\Component;

class RequirementIndex extends Component
{
    public $search = '';

    public function deleteRequirement($requirementId)
    {
        try {
            $requirement = Requirement::findOrFail($requirementId);

            // Delete associated media files
            $requirement->clearMediaCollection('requirementRequiredFiles');

            // Delete the requirement
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
        $requirements = Requirement::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.admin.requirements.requirement-index', [
            'requirements' => $requirements,
        ]);
    }
}