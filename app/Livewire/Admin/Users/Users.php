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

    public $search = '';
    public $departmentFilter = '';
    public $collegeFilter = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'collegeFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatingCollegeFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::with(['department', 'college', 'roles']);

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('firstname', 'like', "%{$this->search}%")
                  ->orWhere('middlename', 'like', "%{$this->search}%")
                  ->orWhere('lastname', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        // Apply department filter
        if (!empty($this->departmentFilter)) {
            $query->where('department_id', $this->departmentFilter);
        }

        // Apply college filter
        if (!empty($this->collegeFilter)) {
            $query->where('college_id', $this->collegeFilter);
        }

        $users = $query->orderBy('lastname')
            ->orderBy('firstname')
            ->paginate($this->perPage);

        $departments = Department::orderBy('name')->get();
        $colleges = College::orderBy('name')->get();

        return view('livewire.admin.users.users-index', [
            'users' => $users,
            'departments' => $departments,
            'colleges' => $colleges,
        ]);
    }
}