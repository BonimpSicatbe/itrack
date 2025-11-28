<?php

namespace App\Livewire;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;

class RegisterUserController extends Component
{
    public $firstname;
    public $middlename;
    public $lastname;
    public $extensionname;
    public $email;
    public $college_id;
    public $position;
    public $teaching_started_at;
    public $password;

    public function rules()
    {
        return [
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'extensionname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email|regex:/^[A-Za-z0-9._%+-]+@cvsu\.edu\.ph$/i',
            'college_id' => 'required|exists:colleges,id',
            'position' => 'required|string|max:255',
            'teaching_started_at' => 'required|date',
            'password' => 'required|confirmed|string|min:8',
        ];
    }

    // Real-time validation for specific fields
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function store(LoginRequest $request)
    {
        $validated = $this->validate();

        // $request->authenticate();

        // // $request->session()->regenerate();

        $user = $request->user();

        $user = User::create([
            'firstname' => $validated['firstname'],
            'middlename' => $validated['middlename'],
            'lastname' => $validated['lastname'],
            'extensionname' => $validated['extensionname'],
            'email' => $validated['email'],
            'college_id' => $validated['college_id'],
            'position' => $validated['position'],
            'teaching_started_at' => $validated['teaching_started_at'],
            'password' => Hash::make($validated['password']),
        ]);

        auth()->login($user);

        $isAdmin = method_exists($user, 'hasRole')
            ? ($user->hasRole('admin') || $user->hasRole('super-admin'))
            : ($user->is_admin ?? false);

        $route = $isAdmin ? 'admin.dashboard' : 'user.dashboard';

        return redirect()->intended(route($route))->with('success', 'Registration successful!');
    }

    public function render()
    {
        $colleges = \App\Models\College::orderBy('name')->get();
        return view('livewire.register-user-controller', [
            'colleges' => $colleges,
        ]);
    }
}
