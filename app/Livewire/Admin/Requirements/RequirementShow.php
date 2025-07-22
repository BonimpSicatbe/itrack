<?php

namespace App\Livewire\Admin\Requirements;

use Livewire\Component;

class RequirementShow extends Component
{
    public $requirement;
    public $assignedUsers;

    public function mount($requirement)
    {
        $this->requirement = $requirement;
        $this->assignedUsers = $requirement->assignedTargets();
    }

    public function showUser($user) {
        return redirect()->route('admin.users.show', $user);
    }

    public function render()
    {
        return view('livewire.admin.requirements.requirement-show', [
            'requirement' => $this->requirement,
            'assignedUsers' => $this->assignedUsers,
            'requiredFiles' => $this->requirement->getMedia('requirement/requirement_required_files'),
        ]);
    }
}
