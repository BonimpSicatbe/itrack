<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
    public $password_confirmation;

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
            'teaching_started_at' => 'required|date|before_or_equal:today',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._])[A-Za-z\d@$!%*?&._]+$/'
            ],
        ];
    }

    public function messages()
    {
        return [
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.confirmed' => ' ',
            'teaching_started_at.before_or_equal' => 'The teaching start date cannot be in the future.',
            'email.regex' => 'The email must be a valid CvSU email address (@cvsu.edu.ph).',
        ];
    }

    // Real-time validation for specific fields
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        
        // Special handling for password confirmation
        if ($propertyName === 'password_confirmation') {
            $this->validateOnly('password');
        }
    }

    public function store()
    {
        $validated = $this->validate();

        try {
            // Create user
            $user = User::create([
                'firstname' => $validated['firstname'],
                'middlename' => $validated['middlename'] ?? null,
                'lastname' => $validated['lastname'],
                'extensionname' => $validated['extensionname'] ?? null,
                'email' => $validated['email'],
                'college_id' => $validated['college_id'],
                'position' => $validated['position'],
                'teaching_started_at' => $validated['teaching_started_at'],
                'teaching_ended_at' => null,
                'password' => Hash::make($validated['password']),
            ]);

            // Assign default role
            $user->assignRole('user');

            // Notify admin users about new registration
            $adminUsers = User::role(['admin'])->get();
            foreach ($adminUsers as $admin) {
                try {
                    $admin->notify(new \App\Notifications\NewRegisteredUserNotification($user));
                } catch (\Throwable $e) {
                    Log::error('Failed to notify admin about new user', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('New user registered successfully', ['user_id' => $user->id, 'email' => $user->email]);

            // Redirect to login with success message - DON'T login automatically
            return redirect()->route('login')->with('success', 'Registration successful! Please wait for admin approval before logging in.');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->addError('email', 'Registration failed. Please try again.');
            return;
        }
    }

    public function render()
    {
        $colleges = \App\Models\College::orderBy('name')->get();
        return view('livewire.register-user-controller', [
            'colleges' => $colleges,
        ]);
    }
}