<?php

namespace App\Livewire\Admin\Requirement\Show;

use App\Livewire\Admin\Dashboard\Requirement;
use App\Models\Requirement as ModelsRequirement;
use Livewire\Component;

class RequirementShow extends Component
{
    public $requirement_id = '';

    public function render()
    {
        $requirement = ModelsRequirement::find($this->requirement_id);

        return view('livewire.admin.requirement.show.requirement-show', [
            'requirement' => $requirement,
        ]);
    }
}
