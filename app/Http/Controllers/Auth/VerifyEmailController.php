<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
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

        return redirect()->intended(route($route, absolute: false) . '?verified=1');
    }
}
