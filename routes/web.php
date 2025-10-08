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
use App\Models\SubmittedRequirement;
use Illuminate\Http\Request;
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
        Route::get('/requirements/create', [RequirementController::class, 'create'])
            ->name('requirements.create');
        Route::get('/requirements/{requirement}', [RequirementController::class, 'show'])
            ->name('requirements.show');
        Route::get('/requirements/{requirement}/edit', [RequirementController::class, 'edit'])
            ->name('requirements.edit');

        // Submitted Requirements
        Route::get('/submitted-requirements', [SubmittedRequirementController::class, 'index'])
            ->name('submitted-requirements.index');
            
        // Individual submission view (original route)
        Route::get('/submitted-requirements/{submitted_requirement}', [SubmittedRequirementController::class, 'show'])
            ->name('submitted-requirements.show');
            
        Route::get('/submitted-requirements/requirement/{requirement_id}/{user_id?}/{course_id?}', function ($requirement_id, $user_id = null, $course_id = null) {
            return view('admin.pages.submitted-requirements.requirement', [
                'requirement_id' => $requirement_id,
                'user_id' => $user_id,
                'course_id' => $course_id
            ]);
        })->name('submitted-requirements.requirement-context');

        Route::prefix('management')->group(function () {
            // Main management dashboard
            Route::get('/', [ManagementController::class, 'index'])
                ->name('management.index');
            // Semester management (archive)
            Route::get('/semesters/{semester}/download', [SemesterController::class, 'downloadZippedSemester'])
                ->name('semesters.download');
            Route::get('/users/{user}/report', [UserController::class, 'downloadUserReport'])->name('users.report');
        });

        Route::get('/test', function () {
            $user = App\Models\User::where('id', 1)->with('college', 'department')->first();
            $semester = App\Models\Semester::where('is_active', true)->first();

            $submittedRequirements = App\Models\SubmittedRequirement::where('user_id', 1)
                ->with([
                    'requirement',
                    'user.college',
                    'user.department',
                    'semester',
                ])->get();

            $requirements = App\Models\User::where('id', 1)->first()->requirements()->get();

            return view('testPage', [
                'submittedRequirements' => $submittedRequirements,
                'requirements' => $requirements,
                'user' => $user,
                'semester' => $semester,
            ]);
        })->name('test');
    });

// File download and preview routes
Route::get('/file/download/{submission}', [FileController::class, 'download'])
    ->name('file.download')
    ->middleware('auth');

Route::get('/file/preview/{submission}', [FileController::class, 'preview'])
    ->name('file.preview')
    ->middleware('auth');

Route::get('/guide/download/{media}', [FileController::class, 'downloadGuide'])
    ->name('guide.download')
    ->middleware('auth');

Route::get('/guide/preview/{media}', [FileController::class, 'previewGuide'])
    ->name('guide.preview')
    ->middleware('auth');

// Notification routes
Route::middleware('auth')->group(function () {
    Route::post('/notifications/mark-all-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read');
    })->name('notifications.markAllRead');

    Route::post('/notifications/{notification}/mark-as-read', function ($notificationId) {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
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
    // Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

    // Email verification
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

require __DIR__ . '/auth.php';