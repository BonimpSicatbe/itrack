<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\User;
use App\Models\College;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentialsMail;
use App\Mail\AccountSetupMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class UserManagement extends Component
{
    public $search = '';
    public $collegeFilter = '';
    
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
        'role' => ''
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
        'role' => '',
        'password' => '',
        'password_confirmation' => ''
    ];

    // Delete Confirmation Properties
    public $showDeleteConfirmationModal = false;
    public $userToDelete = null;

    public function showUser($userId)
    {
        $this->selectedUser = User::with(['college'])->find($userId);
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
            'role' => $user->roles->first() ? $user->roles->first()->id : '',
            'password' => '',
            'password_confirmation' => ''
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
                'editingUser.password' => 'nullable|confirmed|min:8',
                'editingUser.role' => 'required|exists:roles,id',
            ], [
                'editingUser.firstname.required' => 'First name is required.',
                'editingUser.lastname.required' => 'Last name is required.',
                'editingUser.email.required' => 'Email is required.',
                'editingUser.email.unique' => 'This email is already in use.',
                'editingUser.password.confirmed' => 'Password confirmation does not match.',
                'editingUser.password.min' => 'Password must be at least 8 characters.',
                'editingUser.role.required' => 'Role is required.',
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
                'college_id' => $this->editingUser['college_id'],
            ];

            // Only update password if provided
            if (!empty($this->editingUser['password'])) {
                $updateData['password'] = Hash::make($this->editingUser['password']);
            }

            $user->update($updateData);

            // Update role
            $role = Role::find($this->editingUser['role']);
            $user->syncRoles([$role->name]);

            $this->closeEditUserModal();
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$userName}' updated successfully!"
            );

            // Refresh the selected user if it's the same user
            if ($this->selectedUser && $this->selectedUser->id == $user->id) {
                $this->selectedUser = $user->fresh(['college']);
            }

        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to update user. Please try again.'
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
                'newUser.role' => 'required|exists:roles,id',
            ], [
                'newUser.firstname.required' => 'First name is required.',
                'newUser.lastname.required' => 'Last name is required.',
                'newUser.email.required' => 'Email is required.',
                'newUser.email.unique' => 'This email is already in use.',
                'newUser.role.required' => 'Role is required.',
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

            // Generate a random password
            $generatedPassword = Str::password(12);

            // Create the user
            $user = User::create([
                'firstname' => $this->newUser['firstname'],
                'middlename' => $this->newUser['middlename'],
                'lastname' => $this->newUser['lastname'],
                'extensionname' => $this->newUser['extensionname'],
                'email' => $this->newUser['email'],
                'college_id' => $this->newUser['college_id'],
                'password' => Hash::make($generatedPassword),
            ]);

            // Assign role
            $role = Role::find($this->newUser['role']);
            $user->assignRole($role->name);

            // Send email with credentials
            try {
                Mail::to($user->email)->send(new AccountSetupMail($user, $generatedPassword));
            } catch (\Exception $mailException) {
                // Log mail error but don't fail the user creation
                \Log::error('Failed to send account setup email: ' . $mailException->getMessage());
            }

            $this->closeAddUserModal();
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$user->firstname} {$user->lastname}' created successfully! Account setup email sent."
            );

        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to create user. Please try again.'
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

    public function render()
    {
        $query = User::with(['college', 'roles'])
            ->where(function ($q) {
                $q->where('firstname', 'like', '%' . $this->search . '%')
                  ->orWhere('middlename', 'like', '%' . $this->search . '%')
                  ->orWhere('lastname', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhereHas('college', function ($collegeQuery) {
                      $collegeQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });

        if ($this->collegeFilter) {
            $query->where('college_id', $this->collegeFilter);
        }

        $users = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $colleges = College::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.management.user-management', compact('users', 'colleges', 'roles'));
    }
}