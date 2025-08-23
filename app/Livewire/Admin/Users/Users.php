<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use App\Models\College;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Rule;
use Illuminate\Validation\ValidationException;

class Users extends Component
{
    use WithPagination;

    public $search = '';
    public $departmentFilter = '';
    public $collegeFilter = '';
    public $perPage = 10;
    
    // Modal properties
    public $showModal = false;
    public $showDeleteModal = false;
    public $isDeleting = false;
    public $userToDelete;
    
    // Form properties
    #[Rule('required|min:2')]
    public $firstname = '';
    
    #[Rule('nullable')]
    public $middlename = '';
    
    #[Rule('required|min:2')]
    public $lastname = '';
    
    #[Rule('nullable')]
    public $extensionname = '';
    
    #[Rule('required|email|unique:users,email')]
    public $email = '';
    
    #[Rule('required|min:8')]
    public $password = '';
    
    #[Rule('required|same:password')]
    public $password_confirmation = '';

    public $selectedRole = '';
    
    #[Rule('nullable|exists:departments,id')]
    public $department_id = '';
    
    #[Rule('nullable|exists:colleges,id')]
    public $college_id = '';

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
    
    public function openModal()
    {
        $this->showModal = true;
        $this->resetForm();
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->resetValidation();
    }
    
    private function resetForm()
    {
        $this->firstname = '';
        $this->middlename = '';
        $this->lastname = '';
        $this->extensionname = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->department_id = '';
        $this->college_id = '';
        $this->selectedRole = '';
    }
    
    public function createUser()
    {
        try {
            // Validate all fields
            $this->validate();
            
            // Check if user already exists with the same name combination
            $existingUser = User::where('firstname', $this->firstname)
                ->where('lastname', $this->lastname)
                ->where('middlename', $this->middlename)
                ->where('extensionname', $this->extensionname)
                ->first();
                
            if ($existingUser) {
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'A user with this name combination already exists.',
                    duration: 5000
                );
                return;
            }
            
            // Check if user already exists with this email
            if (User::where('email', $this->email)->exists()) {
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'User with this email already exists.',
                    duration: 5000
                );
                return;
            }
            
            // Create the user
            $user = User::create([
                'firstname' => $this->firstname,
                'middlename' => $this->middlename,
                'lastname' => $this->lastname,
                'extensionname' => $this->extensionname,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'department_id' => $this->department_id ?: null,
                'college_id' => $this->college_id ?: null,
            ]);
            
            // Assign role if selected
            if (!empty($this->selectedRole)) {
                $user->assignRole($this->selectedRole);
            }
            
            // Reset form and close modal
            $this->closeModal();
            
            // Show success notification
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'User created successfully!',
                duration: 5000
            );
            
            // Refresh the users list
            $this->resetPage();
            
        } catch (ValidationException $e) {
            // Handle validation errors with notification
            $errors = $e->validator->getMessageBag();
            
            if ($errors->has('email') && str_contains($errors->first('email'), 'unique')) {
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'This email is already registered. Please use a different email.',
                    duration: 5000
                );
            } else if ($errors->has('password_confirmation')) {
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'Password confirmation does not match.',
                    duration: 5000
                );
            } else {
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'Please check the form for errors.',
                    duration: 5000
                );
            }
            
            throw $e;
            
        } catch (\Exception $e) {
            // Handle any other errors
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'An error occurred while creating the user. Please try again.',
                duration: 5000
            );
        }
    }

    // Add delete confirmation method
    public function confirmDelete($id)
    {
        $this->userToDelete = $id;
        $this->showDeleteModal = true;
    }

    // Add delete user method
    public function deleteUser()
    {
        $this->isDeleting = true;
        
        try {
            User::findOrFail($this->userToDelete)->delete();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'User deleted successfully',
                duration: 3000
            );
            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to delete user: ' . $e->getMessage(),
                duration: 5000
            );
        } finally {
            $this->isDeleting = false;
        }
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
        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.users.users-index', [
            'users' => $users,
            'departments' => $departments,
            'colleges' => $colleges,
            'roles' => $roles,
        ]);
    }
}