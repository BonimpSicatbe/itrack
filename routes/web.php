<?php

use App\Http\Controllers\admin\RequirementController;
use App\Http\Controllers\admin\SubmittedRequirementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRequirementController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\admin\SemesterController;
use App\Http\Controllers\Admin\ManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// User routes
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
        Route::get('/submitted-requirements/requirement/{requirement_id}', function ($requirement_id) {
            return view('admin.pages.submitted-requirements.requirement', ['requirement_id' => $requirement_id]);
        })->name('submitted-requirements.requirement');

        Route::prefix('management')->group(function () {
            // Main management dashboard
            Route::get('/', [ManagementController::class, 'index'])
                ->name('management.index');
            
            // User management routes - remove or comment out for now
            // Route::prefix('users')->group(function () {
            //     Route::get('/{user}/edit', [ManagementController::class, 'editUser'])
            //         ->name('management.users.edit');
            //     Route::put('/{user}', [ManagementController::class, 'updateUser'])
            //         ->name('management.users.update');
            //     Route::delete('/{user}', [ManagementController::class, 'destroyUser'])
            //         ->name('management.users.destroy');
            // });
        });

        // Semesters
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

// File download/preview routes
Route::middleware('auth')->group(function () {
    Route::get('/download/file/{submission}', [FileController::class, 'download'])
        ->name('file.download');
    Route::get('/preview/file/{submission}', [FileController::class, 'preview'])
        ->name('file.preview');
    // Add this new route for guide files
     Route::get('/download/guide/{media}', [FileController::class, 'downloadGuide'])
        ->name('guide.download');
    Route::get('/preview/guide/{media}', [FileController::class, 'previewGuide'])
        ->name('guide.preview');
});

// Notification routes
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

Route::get('/user/files/{media}/preview', [UserController::class, 'preview'])
        ->whereNumber('media')
        ->name('user.file.preview');

Route::get('/user/files/{media}/download', [UserController::class, 'download'])
        ->whereNumber('media')
        ->name('user.file.download');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    
    // Email verification
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// Livewire routes (add these if not already present)
Livewire::setScriptRoute(function ($handle) {
    return Route::get('/vendor/livewire/livewire.js', $handle);
});

Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/vendor/livewire/update', $handle);
});

require __DIR__ . '/auth.php';