<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $route = $request->user()->hasRole('admin') || $request->user()->hasRole('super-admin') ? 'admin.dashboard' : 'user.dashboard';
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route($route, absolute: false))
            : view('auth.verify-email');
    }
}
