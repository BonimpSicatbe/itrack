<?php

namespace App\Livewire\Admin\Requirement\Show;

use Livewire\Component;

class RequirementList extends Component
{
    public $requirements;

    public function viewRequirement($requirementId)
    {
        return redirect()->route('admin.requirements.show', ['requirement' => $requirementId]);
    }

    public function render()
    {
        return view('livewire.admin.requirement.show.requirement-list');
    }
}
