<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewRegisteredUserNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $colleges = \App\Models\College::orderBy('name')->get();

        return view('auth.register', [
            'colleges' => $colleges,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'extensionname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'college_id' => ['required', 'exists:colleges,id'],
            'position' => ['required', 'string', 'max:255'],
            'teaching_started_at' => ['required', 'date'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
        ]);

        try {
            // prepare data using only validated fields
            $userData = [
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
            ];

            $user = User::create($userData);

            // log registration for debugging/records
            Log::info('New user registered', ['id' => $user->id, 'email' => $user->email]);

            // assign default role
            $user->assignRole('user');

            // notify admin users about new registration
            $adminUsers = User::role(['admin'])->get();
            Log::info('Preparing to notify admin users of new registration', [
                'new_user_id' => $user->id,
                'new_user_email' => $user->email,
                'admin_count' => $adminUsers->count(),
            ]);

            foreach ($adminUsers as $admin) {
                try {
                    $admin->notify(new NewRegisteredUserNotification($user));
                    Log::info('Admin notified about new user', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'notified_user_id' => $user->id,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to notify admin about new user', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'notified_user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // optionally fire the Registered event
            // event(new Registered($user));

            $route = $user->hasRole('admin') || $user->hasRole('super-admin') ? 'admin.dashboard' : 'user.dashboard';

            return redirect()->route($route)->with('success', 'Registration successful!');
        } catch (\Throwable $e) {
            // log the exception and return back with error
            Log::error('User registration failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Registration failed, please try again.');
        }
    }
}
