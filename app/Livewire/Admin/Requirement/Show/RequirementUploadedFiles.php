<?php

namespace App\Livewire\Admin\Requirement\Show;

use App\Models\Requirement;
use Livewire\Component;

class RequirementUploadedFiles extends Component
{
    public $requirement_id = '';

    public function viewFile($fileId)
    {
        $file = Requirement::find($this->requirement_id)->getMedia('requirementRequiredFiles')->find($fileId);
        if ($file) {
            return response()->download($file->getPath(), $file->name);
        } else {
            session()->flash('error', 'File not found.');
        }
    }

    public function render()
    {
        $requirement = Requirement::find($this->requirement_id);

        return view('livewire.admin.requirement.show.requirement-uploaded-files', [
            'requirement' => $requirement,
        ]);
    }
}
