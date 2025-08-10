<?php

use App\Http\Controllers\admin\RequirementController;
use App\Http\Controllers\admin\SubmittedRequirementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRequirementController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\admin\SemesterController; // Added SemesterController
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// User routes (unchanged)
Route::middleware(['auth', 'role:user'])
    ->prefix('user')
    ->group(function () {
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

// Admin routes
Route::middleware(['auth', 'role:admin|super-admin'])
    ->prefix('/admin')
    ->as('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // Notifications
        Route::get('/notifications', function () {
            return view('admin.pages.notification.notifications');
        })->name('notifications');

        // File Manager
        Route::get('/file-manager', [FileManagerController::class, 'index'])
            ->name('file-manager.index');

        // Requirements
        Route::get('/requirements', [RequirementController::class, 'index'])
            ->name('requirements.index');
        Route::get('/requirements/{requirement}', [RequirementController::class, 'show'])
            ->name('requirements.show');
        Route::get('/requirements/{requirement}/edit', [RequirementController::class, 'edit'])
            ->name('requirements.edit');

        // Submitted Requirements
        Route::get('/submitted-requirements', [SubmittedRequirementController::class, 'index'])
            ->name('submitted-requirements.index');
        Route::get('/submitted-requirements/{submitted_requirement}', [SubmittedRequirementController::class, 'show'])
            ->name('submitted-requirements.show');

        // Users
        Route::resource('users', UserController::class);

        // Semesters (updated with correct controller path)
        Route::prefix('semesters')->group(function () {
            Route::get('/', [SemesterController::class, 'index'])
                ->name('semesters.index');
            Route::post('/', [SemesterController::class, 'store']);
            Route::put('/{semester}', [SemesterController::class, 'update']);
            Route::delete('/{semester}', [SemesterController::class, 'destroy']);
            Route::post('/{semester}/activate', [SemesterController::class, 'setActive'])
                ->name('semesters.activate');
        });
    });

// File download/preview routes (unchanged)
Route::middleware('auth')->group(function () {
    Route::get('/download/file/{submission}', [FileController::class, 'download'])
        ->name('file.download');
    Route::get('/preview/file/{submission}', [FileController::class, 'preview'])
        ->name('file.preview');
});

// Notification routes (unchanged)
Route::middleware('auth')->group(function () {
    Route::post('/notifications/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read');
    })->name('notifications.markAllRead');

    Route::post('/notifications/{notification}/mark-as-read', function ($notificationId) {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.markAsRead');
});

// Profile routes (unchanged)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';