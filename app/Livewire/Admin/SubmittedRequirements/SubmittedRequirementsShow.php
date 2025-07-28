<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use Livewire\Component;

class SubmittedRequirementsShow extends Component
{
    public $submittedRequirement;

    public function render()
    {
        return view('livewire.admin.submitted-requirements.submitted-requirements-show', [
            'submittedRequirement' => $this->submittedRequirement,
        ]);
    }
}
