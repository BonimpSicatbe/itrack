<?php

namespace App\Livewire\Admin\Requirement\Show;

use App\Models\Requirement;
use Livewire\Component;

class RequirementAssignedUsers extends Component
{
    public $requirement_id = '';

    public function viewUser($userId) {
        dd("Viewing user with ID: $userId");
        // return redirect()->route('admin.users.show', ['user_id' => $userId]);
    }

    public function render()
    {
        $requirement = Requirement::find($this->requirement_id);

        return view('livewire.admin.requirement.show.requirement-assigned-users', [
            'requirement' => $requirement
        ]);
    }
}
