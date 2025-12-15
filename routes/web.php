<?php

use App\Http\Controllers\Admin\RequirementController;
use App\Http\Controllers\Admin\SubmittedRequirementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\ManagementController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('register');
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
            return view('admin.dashboard', [
                'unreadCount' => Auth::user()->unreadNotifications->count() ?? 0,
                'navLinks' => [],
        ]);
        })->name('dashboard');

        // Notifications
        Route::get('/notifications', function () {
            return view('admin.pages.notification.notifications');
        })->name('notifications');

        // File Manager
        Route::get('/file-manager', [FileManagerController::class, 'index'])
            ->name('file-manager.index');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');
        Route::get('/reports/preview-semester', [ReportController::class, 'previewSemesterReport'])
            ->name('reports.preview-semester');

        // Faculty Report Routes
        Route::get('/reports/preview-faculty', [ReportController::class, 'previewFacultyReport'])
            ->name('reports.preview-faculty');
        Route::get('/reports/download-faculty', [ReportController::class, 'downloadFacultyReport'])
            ->name('reports.download-faculty');

        // Requirement Report Routes
        Route::get('/reports/preview-requirement', [ReportController::class, 'previewRequirementReport'])
            ->name('reports.preview-requirement');
        Route::get('/reports/download-requirement', [ReportController::class, 'downloadRequirementReport'])
            ->name('reports.download-requirement');

        // Custom Report Routes
        Route::get('/reports/preview-custom', [ReportController::class, 'previewCustomReport'])
            ->name('reports.preview-custom');
        Route::get('/reports/download-custom', [ReportController::class, 'downloadCustomReport'])
            ->name('reports.download-custom');

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

        // Add the missing route for requirement view
        Route::get('/submitted-requirements/requirement/{requirement_id}', function ($requirement_id) {
            return view('admin.pages.submitted-requirements.requirement', [
                'requirement_id' => $requirement_id,
            ]);
        })->name('submitted-requirements.requirement');

        // Context-based requirement view
        Route::get('/submitted-requirements/requirement/{requirement_id}/{user_id?}/{course_id?}', function ($requirement_id, $user_id = null, $course_id = null) {
            return view('admin.pages.submitted-requirements.requirement', [
                'requirement_id' => $requirement_id,
                'user_id' => $user_id,
                'course_id' => $course_id
            ]);
        })->name('submitted-requirements.requirement-context');

        Route::get('/users/{user}/preview-report', [UserController::class, 'previewUserReport'])->name('users.preview-report');
        Route::get('/users/{user}/report', [UserController::class, 'downloadUserReport'])->name('users.report');

        Route::prefix('management')->group(function () {
            // Main management dashboard
            Route::get('/', [ManagementController::class, 'index'])
                ->name('management.index');
        });

        Route::get('/test', function () {
            $user = App\Models\User::where('id', 1)->with('college')->first();
            $semester = App\Models\Semester::where('is_active', true)->first();

            $submittedRequirements = App\Models\SubmittedRequirement::where('user_id', 1)
                ->with([
                    'requirement',
                    'user.college',
                    'semester',
                ])->get();

            $requirements = App\Models\User::where('id', 1)->first()->requirements()->get();

            return view('reports.testPage', [
                'submittedRequirements' => $submittedRequirements,
                'requirements' => $requirements,
                'user' => $user,
                'semester' => $semester,
            ]);
        })->name('test');
    });

// File download and preview routes - PUBLIC ROUTES
Route::middleware('auth')->group(function () {
    // ✅ Main routes that handle both original and signed files based on status
    Route::get('/file/download/{submission}', [FileController::class, 'download'])
        ->name('file.download');

    Route::get('/file/preview/{submission}', [FileController::class, 'preview'])
        ->name('file.preview');

    // ✅ NEW: Routes for ORIGINAL files (bypass signed document logic)
    Route::get('/file/download-original/{submission}', [FileController::class, 'downloadOriginal'])
        ->name('file.download.original');

    Route::get('/file/preview-original/{submission}', [FileController::class, 'previewOriginal'])
        ->name('file.preview.original');

    // ✅ NEW: Routes for SIGNED documents only
    Route::get('/file/signed-download/{submission}', [FileController::class, 'downloadSigned'])
        ->name('file.download.signed');

    Route::get('/file/preview-signed/{submission}', [FileController::class, 'previewSigned'])
        ->name('file.preview.signed');

    // Guide routes
    Route::get('/guide/download/{media}', [FileController::class, 'downloadGuide'])
        ->name('guide.download');

    Route::get('/guide/preview/{media}', [FileController::class, 'previewGuide'])
        ->name('guide.preview');

    // User file routes
    Route::get('/user/files/{media}/preview', [UserController::class, 'preview'])
        ->whereNumber('media')
        ->name('user.file.preview');

    Route::get('/user/files/{media}/download', [UserController::class, 'download'])
        ->whereNumber('media')
        ->name('user.file.download');
});

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

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Email verification
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');
}); 

require __DIR__ . '/auth.php';