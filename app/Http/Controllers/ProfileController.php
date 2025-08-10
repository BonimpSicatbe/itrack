<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        // Handle the name field since your User model has firstname, lastname, etc.
        if (isset($validated['name'])) {
            $name = trim($validated['name']);
            
            if (!empty($name)) {
                // Split the name into parts
                $nameParts = array_filter(explode(' ', $name));
                
                // Clear all name fields first
                $request->user()->firstname = '';
                $request->user()->middlename = '';
                $request->user()->lastname = '';
                $request->user()->extensionname = '';
                
                // Assign name parts
                if (count($nameParts) >= 1) {
                    $request->user()->firstname = $nameParts[0];
                }
                
                if (count($nameParts) >= 2) {
                    // If only 2 parts, second part is lastname
                    if (count($nameParts) == 2) {
                        $request->user()->lastname = $nameParts[1];
                    } else {
                        // If 3 or more parts, second part is middlename
                        $request->user()->middlename = $nameParts[1];
                        
                        // Everything else becomes lastname
                        $request->user()->lastname = implode(' ', array_slice($nameParts, 2));
                    }
                }
            }
            
            unset($validated['name']); // Remove name from validated data
        }

        // Fill other validated fields
        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}