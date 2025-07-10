<?php

use App\Http\Controllers\admin\RequirementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRequirementController;
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
        Route::resource('requirements', UserRequirementController::class)
            ->only(['index', 'show'])
            ->names('user.requirements');

        Route::get('/dashboard', function () {
            return view('user.dashboard');
        })->name('user.dashboard');

        Route::get('/file-manager', function () {
            return view('user.file-manager');
        })->name('user.file-manager');

        Route::get('/pending-task', function () {
            return view('user.pending-task');
        })->name('user.pending-task');

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

        // ========== ========== REQUIREMENT ROUTES ========== ==========
        Route::resource('requirements', RequirementController::class); // admin.requirements.show / index / etc

        Route::resource('users', UserController::class);
    });

//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
