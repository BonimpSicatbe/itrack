<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;
use App\Models\Department;
use App\Models\College;
use Spatie\Permission\Models\Role;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UsersEdit extends Component
{
    use WithFileUploads;

    public $user;
    public $firstname;
    public $middlename;
    public $lastname;
    public $extensionname;
    public $email;
    public $department_id;
    public $college_id;
    public $roles = [];
    public $password;
    public $password_confirmation;
    public $profile_picture;

    public $departments;
    public $colleges;
    public $availableRoles;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->firstname = $user->firstname;
        $this->middlename = $user->middlename;
        $this->lastname = $user->lastname;
        $this->extensionname = $user->extensionname;
        $this->email = $user->email;
        $this->department_id = $user->department_id;
        $this->college_id = $user->college_id;
        $this->roles = $user->roles->pluck('id')->toArray();

        $this->departments = Department::orderBy('name')->get();
        $this->colleges = College::orderBy('name')->get();
        $this->availableRoles = Role::orderBy('name')->get();
    }

    public function save()
    {
        $this->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'extensionname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->user->id,
            'department_id' => 'nullable|exists:departments,id',
            'college_id' => 'nullable|exists:colleges,id',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'profile_picture' => 'nullable|image|max:2048',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Update user data
        $this->user->update([
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'lastname' => $this->lastname,
            'extensionname' => $this->extensionname,
            'email' => $this->email,
            'department_id' => $this->department_id,
            'college_id' => $this->college_id,
        ]);

        // Update password if provided
        if (!empty($this->password)) {
            $this->user->update([
                'password' => Hash::make($this->password),
            ]);
        }

        // Update profile picture if provided
        if ($this->profile_picture) {
            $this->user->clearMediaCollection('profile_picture');
            $this->user->addMedia($this->profile_picture->getRealPath())
                ->toMediaCollection('profile_picture');
        }

        // Sync roles
        $this->user->syncRoles($this->roles);

        session()->flash('message', 'User updated successfully.');
        
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        return view('livewire.admin.users.users-edit');
    }
}