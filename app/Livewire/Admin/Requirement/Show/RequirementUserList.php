<?php

namespace App\Livewire\Admin\Requirement\Show;

use Livewire\Component;

class RequirementUserList extends Component
{
    public $requirement;
    public $assignedUsers;

    public function viewUser($userId) {
        return redirect()->route('admin.users.show', ['user' => $userId]);
    }

    public function render()
    {
        return view('livewire.admin.requirement.show.requirement-user-list');
    }
}
