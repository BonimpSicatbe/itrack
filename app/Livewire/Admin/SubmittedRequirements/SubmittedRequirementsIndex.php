<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use App\Models\SubmittedRequirement;
use Livewire\Component;

class SubmittedRequirementsIndex extends Component
{
    public function render()
    {
        $submittedRequirements = SubmittedRequirement::with(['requirement', 'user', 'media'])
            ->orderBy('submitted_at', 'desc')->get();

        return view('livewire.admin.submitted-requirements.submitted-requirements-index', [
            'submittedRequirements' => $submittedRequirements
        ]);
    }
}
