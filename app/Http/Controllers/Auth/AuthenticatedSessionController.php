<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Unable to retrieve user after login.']);
        }

        // Decide admin vs user dashboard in a safe way:
        $isAdmin = false;

        if (method_exists($user, 'hasRole')) {
            $isAdmin = $user->hasRole('admin') || $user->hasRole('super-admin');
        } elseif (isset($user->is_admin)) {
            // fallback if you use an `is_admin` boolean on the model
            $isAdmin = (bool) $user->is_admin;
        }

        $route = $isAdmin ? 'admin.dashboard' : 'user.dashboard';

        // Redirect to intended URL if present, otherwise to the chosen route
        return redirect()->intended(route($route))->with('success', 'Login successful!');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
