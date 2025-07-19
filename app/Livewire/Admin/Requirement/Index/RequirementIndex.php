<?php

namespace App\Livewire\Admin\Requirement\Index;

use App\Models\Requirement;
use Livewire\Component;

class RequirementIndex extends Component
{
    public $search = '';

    /**
     *
     * Handles the creation of a new requirement.
     *
     **/
    public function createRequirement() {}

    /**
     *
     * Serves as a route or link handler for when a row is selected in the table.
     * This method is typically used to navigate or perform actions based on the selected requirement.
     *
     */
    public function viewRequirement($requirement_id)
    {
        return redirect()->route('admin.requirements.show', ['requirement_id' => $requirement_id]);
    }

    public function render()
    {
        $requirements = Requirement::all();

        return view('livewire.admin.requirement.index.requirement-index', [
            'requirements' => $requirements->filter(function ($requirement) {
                return str_contains(strtolower($requirement->name), strtolower($this->search));
            }),
        ]);
    }
}
