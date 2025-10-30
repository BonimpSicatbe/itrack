<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use App\Models\Semester;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaModel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.pages.users.users_index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user)
    {
        // If it's an AJAX request, return JSON with the rendered HTML
        if ($request->ajax()) {
            $html = view('livewire.admin.users.user-show', compact('user'))->render();
            return response()->json(['html' => $html]);
        }

        // For regular requests, return the full view
        return view('admin.pages.users.show', compact('user'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.pages.users.users_edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    protected function authorizeMedia(MediaModel $media): void
    {
        $model = $media->model;

        if ($model instanceof SubmittedRequirement) {
            if ((int) $model->user_id !== (int) Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'You do not have access to this file.');
            }
            return;
        }

        if ($model instanceof Requirement) {
            // Allowed: requirement-owned files (e.g., 'guides', 'requirements' collections)
            return;
        }

        abort(Response::HTTP_FORBIDDEN, 'Invalid file owner.');
    }

    /**
     * Preview file inline for images/PDF; otherwise download/redirect.
     * Route name: user.file.preview
     */
    public function preview($mediaId)
    {
        $media = MediaModel::query()->findOrFail($mediaId);
        $this->authorizeMedia($media);

        $mime    = $media->mime_type ?? '';
        $inline  = (function_exists('str_starts_with') ? str_starts_with($mime, 'image/') : strpos($mime, 'image/') === 0)
            || $mime === 'application/pdf';

        $path    = $media->getPath();     // absolute local path
        $fullUrl = $media->getFullUrl();  // public URL

        if ($inline) {
            if ($path && file_exists($path)) {
                return response()->file($path, [
                    'Content-Type'        => $mime ?: 'application/octet-stream',
                    'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
                ]);
            }
            return redirect()->away($fullUrl);
        }

        if ($path && file_exists($path)) {
            return response()->download($path, $media->file_name);
        }
        return redirect()->away($fullUrl);
    }

    /**
     * Force download; fallback to public URL if stored remotely.
     * Route name: user.file.download
     */
    public function download($mediaId)
    {
        $media = MediaModel::query()->findOrFail($mediaId);
        $this->authorizeMedia($media);

        $path    = $media->getPath();
        $fullUrl = $media->getFullUrl();

        if ($path && file_exists($path)) {
            return response()->download($path, $media->file_name);
        }
        return redirect()->away($fullUrl);
    }

    /**
     * Preview user report in browser
     */
    public function previewUserReport(User $user)
    {
        // Get active semester
        $semester = Semester::getActiveSemester();
        
        // Get user's assigned courses for the active semester
        $assignedCourses = \App\Models\CourseAssignment::where('professor_id', $user->id)
            ->where('semester_id', $semester->id)
            ->with(['course.program', 'course.courseType'])
            ->get();
        
        // Get all requirements for the active semester
        $requirements = \App\Models\Requirement::where('semester_id', $semester->id)
            ->orderBy('id')
            ->get();
        
        // Get ALL submitted requirements for this user in active semester
        $allSubmissions = \App\Models\SubmittedRequirement::where('user_id', $user->id)
            ->whereHas('requirement', function($query) use ($semester) {
                $query->where('semester_id', $semester->id);
            })
            ->where('status', '!=', 'uploaded')
            ->with(['requirement', 'course.program', 'media'])
            ->get();

        // Group by course_id AND requirement_id to ensure proper grouping
        $groupedSubmissions = [];
        foreach ($allSubmissions as $submission) {
            $key = $submission->course_id . '_' . $submission->requirement_id;
            if (!isset($groupedSubmissions[$key])) {
                $groupedSubmissions[$key] = [];
            }
            $groupedSubmissions[$key][] = $submission;
        }

        $pdf = Pdf::loadView('reports.testPage', [
            'assignedCourses' => $assignedCourses,
            'requirements' => $requirements,
            'groupedSubmissions' => $groupedSubmissions,
            'user' => $user,
            'semester' => $semester,
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Preview in browser instead of downloading
        return $pdf->stream("Faculty_Report_{$user->lastname}_{$user->firstname}_" . now()->format('F_d_Y') . ".pdf");
    }

    /**
     * Download user report (kept for backward compatibility)
     */
    public function downloadUserReport(User $user)
    {
        // Get active semester
        $semester = Semester::getActiveSemester();
        
        // Get user's assigned courses for the active semester
        $assignedCourses = \App\Models\CourseAssignment::where('professor_id', $user->id)
            ->where('semester_id', $semester->id)
            ->with(['course.program', 'course.courseType'])
            ->get();
        
        // Get all requirements for the active semester
        $requirements = \App\Models\Requirement::where('semester_id', $semester->id)
            ->orderBy('id')
            ->get();
        
        // Get ALL submitted requirements for this user in active semester
        $allSubmissions = \App\Models\SubmittedRequirement::where('user_id', $user->id)
            ->whereHas('requirement', function($query) use ($semester) {
                $query->where('semester_id', $semester->id);
            })
            ->where('status', '!=', 'uploaded')
            ->with(['requirement', 'course.program', 'media'])
            ->get();

        // Group by course_id AND requirement_id to ensure proper grouping
        $groupedSubmissions = [];
        foreach ($allSubmissions as $submission) {
            $key = $submission->course_id . '_' . $submission->requirement_id;
            if (!isset($groupedSubmissions[$key])) {
                $groupedSubmissions[$key] = [];
            }
            $groupedSubmissions[$key][] = $submission;
        }

        $pdf = Pdf::loadView('reports.testPage', [
            'assignedCourses' => $assignedCourses,
            'requirements' => $requirements,
            'groupedSubmissions' => $groupedSubmissions,
            'user' => $user,
            'semester' => $semester,
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download("Faculty_Report_{$user->lastname}_{$user->firstname}_" . now()->format('F_d_Y') . ".pdf");
    }
}