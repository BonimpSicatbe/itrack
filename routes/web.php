<?php

use App\Http\Controllers\admin\RequirementController;
use App\Http\Controllers\admin\SubmittedRequirementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRequirementController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user(); // initiate user

        if ($user->hasRole('user')) {
            return redirect()->route('user.dashboard');
        } elseif ($user->hasRole('admin') || $user->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        }
    } else {
        return redirect()->route('login');
    }
});

Route::middleware(['auth', 'role:user'])
    ->prefix('user')
    ->group(function () {
        /**
         *
         * show user requirement list
         * show user requirement details
         *
         **/
        // Route::resource('requirements', UserRequirementController::class)
        //     ->only(['index', 'show'])
        //     ->names('user.requirements');

        Route::get('/dashboard', function () {
            return view('user.dashboard');
        })->name('user.dashboard');

        Route::get('/file-manager', function () {
            return view('user.file-manager');
        })->name('user.file-manager');

        Route::get('/requirements', function () {
            return view('user.requirements');
        })->name('user.requirements');

        Route::get('/recents', function () {
            return view('user.recents');
        })->name('user.recents');

        Route::get('/archive', function () {
            return view('user.archive');
        })->name('user.archive');

        Route::get('/notifications', function () {
            return view('user.notifications');
        })->name('user.notifications');
    });

// admin and super-admin routes
Route::middleware(['auth', 'role:admin|super-admin'])
    ->prefix('/admin')
    ->as('admin.')
    ->group(function () {
        /**
         *
         * //TODO add route for users (view, edit, delete, show, etc)
         *
         **/

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // ========== ========== NOTIFICATIONS ROUTES | START ========== ==========
        Route::get('/notifications', function () {
            return view('admin.pages.notification.notifications');
        })->name('notifications');
        // ========== ========== NOTIFICATIONS ROUTES | END ========== ==========

        // ========== ========== REQUIREMENT ROUTES | START ========== ==========
        // Route::resource('requirements', RequirementController::class); // admin.requirements.show / index / etc
        Route::get('/requirements', [RequirementController::class, 'index'])->name('requirements.index');
        Route::get('/requirements/{requirement_id}', [RequirementController::class, 'show'])->name('requirements.show');
        Route::get('/requirements/{requirement_id}/edit', [RequirementController::class, 'edit'])->name('requirements.edit');
        // ========== ========== REQUIREMENT ROUTES | END ========== ==========

        // ========== ========== SUBMISSION ROUTES | START ========== ==========
        Route::get('/submitted-requirements-list', [SubmittedRequirementController::class, 'index'])->name('submitted-requirements.index');
        Route::get('/submitted-requirements-list/{submission}', [SubmittedRequirementController::class, 'show'])->name('submitted-requirements.show');
        // ========== ========== SUBMISSION ROUTES | END ========== ==========

        Route::resource('users', UserController::class);
    });

// File download and preview routes
Route::middleware('auth')->group(function () {
    // File download route
    Route::get('/download/file/{submission}', [FileController::class, 'download'])
         ->name('file.download');

    // File preview route
    Route::get('/preview/file/{submission}', [FileController::class, 'preview'])
         ->name('file.preview');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
