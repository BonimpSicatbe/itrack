<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use App\Models\College;

class Users extends Component
{
    use WithPagination;

    public $confirmingDeletion = false;
    public $userIdToDelete = null;
    public $search = '';
    public $departmentFilter = '';
    public $collegeFilter = '';

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $users = User::with(['department', 'college', 'roles'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('firstname', 'like', '%'.$this->search.'%')
                      ->orWhere('lastname', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->departmentFilter, function ($query) {
                $query->where('department_id', $this->departmentFilter);
            })
            ->when($this->collegeFilter, function ($query) {
                $query->where('college_id', $this->collegeFilter);
            })
            ->paginate(10);

        return view('livewire.admin.users.users-index', [
            'users' => $users,
            'departments' => Department::all(),
            'colleges' => College::all(),
        ]);
    }

    public function confirmDelete($userId)
    {
        $this->confirmingDeletion = true;
        $this->userIdToDelete = $userId;
    }

    public function deleteUser()
    {
        User::find($this->userIdToDelete)->delete();
        $this->confirmingDeletion = false;
        session()->flash('message', 'User deleted successfully.');
    }
}