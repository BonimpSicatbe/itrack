<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserManagement extends Component
{
    use WithPagination;

     #[Url]
    public $search = '';
    
    #[Url]
    public $collegeFilter = '';
    
    #[Url]
    public $departmentFilter = '';
    
    #[Url]
    public $perPage = 10;

    public $sortField = 'lastname';
    public $sortDirection = 'asc';
    public $selectedUser = null;
    
    public $showAddUserModal = false;
    public $newUser = [
        'firstname' => '',
        'middlename' => '',
        'lastname' => '',
        'extensionname' => '',
        'email' => '',
        'college_id' => '',
        'department_id' => '',
        'password' => '',
        'password_confirmation' => ''
    ];

    // Edit User Modal Properties
    public $showEditUserModal = false;
    public $editingUser = [
        'id' => '',
        'firstname' => '',
        'middlename' => '',
        'lastname' => '',
        'extensionname' => '',
        'email' => '',
        'college_id' => '',
        'department_id' => '',
    ];

    // Delete Confirmation Properties
    public $showDeleteConfirmationModal = false;
    public $userToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'collegeFilter' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'lastname'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function showUser($userId)
    {
        $this->selectedUser = User::with(['college', 'department'])->find($userId);
    }

    public function closeUserDetail()
    {
        $this->selectedUser = null;
    }

    public function openAddUserModal()
    {
        $this->showAddUserModal = true;
        $this->reset('newUser');
        $this->resetErrorBag();
    }

    public function closeAddUserModal()
    {
        $this->showAddUserModal = false;
        $this->reset('newUser');
        $this->resetErrorBag();
    }

    // Edit User Methods
    public function openEditUserModal($userId)
    {
        $user = User::find($userId);
        
        $this->editingUser = [
            'id' => $user->id,
            'firstname' => $user->firstname,
            'middlename' => $user->middlename,
            'lastname' => $user->lastname,
            'extensionname' => $user->extensionname,
            'email' => $user->email,
            'college_id' => $user->college_id,
            'department_id' => $user->department_id,
        ];
        
        $this->showEditUserModal = true;
        $this->resetErrorBag();
    }

    public function closeEditUserModal()
    {
        $this->showEditUserModal = false;
        $this->reset('editingUser');
        $this->resetErrorBag();
    }

    // Delete User Methods
    public function openDeleteConfirmationModal($userId)
    {
        $this->userToDelete = User::find($userId);
        $this->showDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->showDeleteConfirmationModal = false;
        $this->userToDelete = null;
    }

    public function deleteUser()
    {
        if ($this->userToDelete) {
            $userName = $this->userToDelete->firstname . ' ' . $this->userToDelete->lastname;
            $userId = $this->userToDelete->id;
            $this->userToDelete->delete();
            
            $this->closeDeleteConfirmationModal();
            // Dispatch notification instead of custom event
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$userName}' deleted successfully!"
            );
            
            if ($this->selectedUser && $this->selectedUser->id == $userId) {
                $this->closeUserDetail();
            }
        }
    }


    public function updateUser()
    {
        try {
            $this->validate([
                'editingUser.firstname' => 'required|string|max:255',
                'editingUser.middlename' => 'nullable|string|max:255',
                'editingUser.lastname' => 'required|string|max:255',
                'editingUser.extensionname' => 'nullable|string|max:255',
                'editingUser.email' => 'required|string|email|max:255|unique:users,email,' . $this->editingUser['id'],
                'editingUser.college_id' => 'nullable|exists:colleges,id',
                'editingUser.department_id' => 'nullable|exists:departments,id',
                'editingUser.password' => 'nullable|confirmed|min:8',
            ], [
                'editingUser.firstname.required' => 'First name is required.',
                'editingUser.lastname.required' => 'Last name is required.',
                'editingUser.email.required' => 'Email is required.',
                'editingUser.email.unique' => 'This email is already in use.',
                'editingUser.password.confirmed' => 'Password confirmation does not match.',
                'editingUser.password.min' => 'Password must be at least 8 characters.',
            ]);

            // Check if another user with same first and last name already exists
            $existingUser = User::where('firstname', $this->editingUser['firstname'])
                ->where('lastname', $this->editingUser['lastname'])
                ->where('id', '!=', $this->editingUser['id'])
                ->first();
                
            if ($existingUser) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: "Another user with the name '{$this->editingUser['firstname']} {$this->editingUser['lastname']}' already exists."
                );
                return;
            }

            $user = User::find($this->editingUser['id']);
            $userName = $user->firstname . ' ' . $user->lastname;
            
            $updateData = [
                'firstname' => $this->editingUser['firstname'],
                'middlename' => $this->editingUser['middlename'],
                'lastname' => $this->editingUser['lastname'],
                'extensionname' => $this->editingUser['extensionname'],
                'email' => $this->editingUser['email'],
                'college_id' => $this->editingUser['college_id'] ?: null,
                'department_id' => $this->editingUser['department_id'] ?: null,
            ];
            
            // Only update password if provided
            if (!empty($this->editingUser['password'])) {
                $updateData['password'] = Hash::make($this->editingUser['password']);
            }
            
            $user->update($updateData);

            $this->closeEditUserModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$userName}' updated successfully!"
            );
            
            // If we're viewing the user detail, refresh it
            if ($this->selectedUser && $this->selectedUser->id == $user->id) {
                $this->selectedUser = $user->fresh(['college', 'department']);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            foreach ($errors as $error) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: $error
                );
            }
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'An unexpected error occurred: ' . $e->getMessage()
            );
        }
    }

    public function addUser()
    {
        try {
            $this->validate([
                'newUser.firstname' => 'required|string|max:255',
                'newUser.middlename' => 'nullable|string|max:255',
                'newUser.lastname' => 'required|string|max:255',
                'newUser.extensionname' => 'nullable|string|max:255',
                'newUser.email' => 'required|string|email|max:255|unique:users,email',
                'newUser.college_id' => 'nullable|exists:colleges,id',
                'newUser.department_id' => 'nullable|exists:departments,id',
                'newUser.password' => ['required', 'confirmed', Rules\Password::defaults()],
            ], [
                'newUser.firstname.required' => 'First name is required.',
                'newUser.lastname.required' => 'Last name is required.',
                'newUser.email.required' => 'Email is required.',
                'newUser.email.unique' => 'This email is already in use.',
                'newUser.password.required' => 'Password is required.',
                'newUser.password.confirmed' => 'Password confirmation does not match.',
            ]);

            // Check if user with same first and last name already exists
            $existingUser = User::where('firstname', $this->newUser['firstname'])
                ->where('lastname', $this->newUser['lastname'])
                ->first();
                
            if ($existingUser) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: "A user with the name '{$this->newUser['firstname']} {$this->newUser['lastname']}' already exists."
                );
                return;
            }

            $user = User::create([
                'firstname' => $this->newUser['firstname'],
                'middlename' => $this->newUser['middlename'],
                'lastname' => $this->newUser['lastname'],
                'extensionname' => $this->newUser['extensionname'],
                'email' => $this->newUser['email'],
                'college_id' => $this->newUser['college_id'] ?: null,
                'department_id' => $this->newUser['department_id'] ?: null,
                'password' => Hash::make($this->newUser['password']),
            ]);

            $userName = $user->firstname . ' ' . $user->lastname;
            $this->closeAddUserModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$userName}' added successfully!"
            );
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            foreach ($errors as $error) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: $error
                );
            }
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'An unexpected error occurred: ' . $e->getMessage()
            );
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCollegeFilter()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::with(['college', 'department', 'roles'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('firstname', 'like', '%' . $this->search . '%')
                    ->orWhere('middlename', 'like', '%' . $this->search . '%')
                    ->orWhere('lastname', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->collegeFilter, function ($query) {
                $query->where('college_id', $this->collegeFilter);
            })
            ->when($this->departmentFilter, function ($query) {
                $query->where('department_id', $this->departmentFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $colleges = College::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('livewire.admin.management.user-management', [
            'users' => $users,
            'colleges' => $colleges,
            'departments' => $departments,
        ]);
    }
}