<?php

use App\Http\Controllers\admin\RequirementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->hasRole('user')) {
            return redirect()->route('user.dashboard');
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        }
    } else {
        return redirect()->route('login');
    }
});

Route::middleware(['auth', 'role:user'])
    ->prefix('user')
    ->group(function () {
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
    });

// admin and super-admin routes
Route::middleware(['auth', 'role:admin|super-admin'])
    ->prefix('/admin')
    ->as('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // ========== ========== REQUIREMENT ROUTES ========== ==========
        Route::resource('requirements', RequirementController::class);
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