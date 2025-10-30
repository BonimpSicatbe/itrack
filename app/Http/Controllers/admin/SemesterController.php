<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\User;
use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use ZipArchive;

class SemesterController extends Controller
{
    public function index()
    {
        return view('admin.pages.semester.semester_index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Deactivate all other semesters if this one is being activated
        if ($request->is_active) {
            Semester::query()->update(['is_active' => false]);
        }

        Semester::create($validated);

        return redirect()->back()->with('success', 'Semester created successfully');
    }

    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Deactivate all other semesters if this one is being activated
        if ($request->is_active) {
            Semester::where('id', '!=', $semester->id)->update(['is_active' => false]);
        }

        $semester->update($validated);

        return redirect()->back()->with('success', 'Semester updated successfully');
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();
        return redirect()->back()->with('success', 'Semester deleted successfully');
    }

    public function setActive($id)
    {
        Semester::query()->update(['is_active' => false]);
        $semester = Semester::findOrFail($id);
        $semester->update(['is_active' => true]);
        session()->flash('success', 'Semester activated successfully');
    }

    public function downloadZippedSemester(Semester $semester)
    {
        $zipFileName = preg_replace('/[^A-Za-z0-9_\- ]/', '_', $semester->name) . '_requirements.zip';
        $zipFilePath = storage_path('app/temp/semesters/' . $zipFileName);

        // Ensure directory exists
        $directory = storage_path('app/temp/semesters');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $zip = new ZipArchive();
        $result = $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== TRUE) {
            abort(500, 'Failed to create zip file. Error code: ' . $result);
        }

        $fileCount = 0;

        // Add requirement files to zip
        foreach ($semester->requirements as $requirement) {
            // Create a folder for each requirement
            $requirementFolder = 'Requirements/' . $requirement->name;

            $requirement->media->each(function ($media) use ($zip, $requirement, &$fileCount, $requirementFolder) {
                // Use the direct path from Spatie Media Library
                $filePath = $media->getPath(); // Remove storage_path prefix!

                if (file_exists($filePath)) {
                    $archivePath = $requirementFolder . '/' . $media->file_name;
                    if ($zip->addFile($filePath, $archivePath)) {
                        $fileCount++;
                    }
                }
            });
        }

        // Add submitted requirement files to zip
        foreach ($semester->requirements as $requirement) {
            // Get all submissions for this requirement
            $submissions = $requirement->submissions()->with(['media', 'user'])->get();

            foreach ($submissions as $submission) {
                foreach ($submission->media as $media) {
                    // Use the direct path from Spatie Media Library
                    $filePath = $media->getPath(); // Remove storage_path prefix!

                    if (file_exists($filePath)) {
                        // Structure: Submissions/RequirementName/UserName/filename
                        $archivePath = 'Submissions/' . $requirement->name . '/' . $submission->user->name . '/' . $media->file_name;
                        if ($zip->addFile($filePath, $archivePath)) {
                            $fileCount++;
                        }
                    }
                }
            }
        }

        // Close the zip file
        if (!$zip->close()) {
            abort(500, 'Failed to close zip file');
        }

        // Check if zip file was actually created
        if (!file_exists($zipFilePath)) {
            abort(500, 'Zip file was not created successfully');
        }

        // If no files were added, create a simple info file
        if ($fileCount === 0) {
            $zip = new ZipArchive();
            $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFromString('no_files_found.txt', 'No files were found for this semester.');
            $zip->close();
        }

        $semester->update(['is_active' => false]);

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function downloadSemesterReport(Semester $semester)
    {
        $zipFileName = preg_replace('/[^A-Za-z0-9_\- ]/', '_', $semester->name) . '_reports.zip';
        $zipFilePath = storage_path('app/temp/reports/' . $zipFileName);

        // Ensure directory exists
        $directory = storage_path('app/temp/reports');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $zip = new ZipArchive();
        $result = $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== TRUE) {
            abort(500, 'Failed to create zip file. Error code: ' . $result);
        }

        $fileCount = 0;

        // Generate report for each requirement in the semester
        foreach ($semester->requirements as $requirement) {
            // Get assigned users for this requirement using the corrected method
            $assignedUsers = $requirement->getAssignedUsers()->load([
                'courseAssignments.course.program',
                'courseAssignments.course.courseType',
                'college'
            ]);
            
            // Get users who have submitted this requirement
            $submittedUsers = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->with([
                    'user.courseAssignments.course.program',
                    'user.courseAssignments.course.courseType',
                    'user.college',
                    'media'
                ])
                ->get();

            // Get submitted user IDs
            $submittedUserIds = $submittedUsers->pluck('user_id')->toArray();
            
            // Get users who haven't submitted
            $notSubmittedUsers = $assignedUsers->filter(function ($user) use ($submittedUserIds) {
                return !in_array($user->id, $submittedUserIds);
            })->values();

            // Generate PDF
            $pdf = Pdf::loadView('reports.requirement-report', [
                'requirement' => $requirement,
                'semester' => $semester,
                'submittedUsers' => $submittedUsers,
                'notSubmittedUsers' => $notSubmittedUsers,
                'totalAssignedUsers' => $assignedUsers->count(),
                'submittedRequirements' => $submittedUsers
            ]);

            $pdfFileName = preg_replace('/[^A-Za-z0-9_\- ]/', '_', $requirement->name) . '_' . 
                        preg_replace('/[^A-Za-z0-9_\- ]/', '_', $semester->name) . '.pdf';
            
            // Add PDF to zip
            $zip->addFromString($pdfFileName, $pdf->output());
            $fileCount++;
        }

        // Close the zip file
        if (!$zip->close()) {
            abort(500, 'Failed to close zip file');
        }

        // If no requirements were found, create an info file
        if ($fileCount === 0) {
            $zip = new ZipArchive();
            $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFromString('no_requirements_found.txt', 'No requirements were found for this semester.');
            $zip->close();
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function previewSemesterReport(Semester $semester, $requirementId = null)
    {
        // If specific requirement is provided, show only that one
        if ($requirementId) {
            $requirement = Requirement::with(['semester'])->find($requirementId);
            
            // Get assigned users with their course assignments and related data
            $assignedUsers = $requirement->getAssignedUsers()->load([
                'courseAssignments.course.program',
                'courseAssignments.course.courseType',
                'college'
            ]);
            
            $submittedUsers = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->with([
                    'user.courseAssignments.course.program', 
                    'user.courseAssignments.course.courseType',
                    'user.college',
                    'media'
                ])
                ->get();

            $submittedUserIds = $submittedUsers->pluck('user_id')->toArray();
            $notSubmittedUsers = $assignedUsers->filter(function ($user) use ($submittedUserIds) {
                return !in_array($user->id, $submittedUserIds);
            })->values();

            $pdf = Pdf::loadView('reports.requirement-report', [
                'requirement' => $requirement,
                'semester' => $semester,
                'submittedUsers' => $submittedUsers,
                'notSubmittedUsers' => $notSubmittedUsers,
                'totalAssignedUsers' => $assignedUsers->count(),
                'submittedRequirements' => $submittedUsers // Add this for easier access in blade
            ]);

            return $pdf->stream("preview_{$requirement->name}.pdf");
        }

        // Otherwise, show first requirement as sample
        $requirement = $semester->requirements()->with(['semester'])->first();
        if (!$requirement) {
            abort(404, 'No requirements found for this semester');
        }

        $assignedUsers = $requirement->getAssignedUsers()->load([
            'courseAssignments.course.program',
            'courseAssignments.course.courseType',
            'college'
        ]);
        
        $submittedUsers = SubmittedRequirement::where('requirement_id', $requirement->id)
            ->with([
                'user.courseAssignments.course.program',
                'user.courseAssignments.course.courseType', 
                'user.college',
                'media'
            ])
            ->get();
            
        $submittedUserIds = $submittedUsers->pluck('user_id')->toArray();
        $notSubmittedUsers = $assignedUsers->filter(function ($user) use ($submittedUserIds) {
            return !in_array($user->id, $submittedUserIds);
        })->values();

        $pdf = Pdf::loadView('reports.requirement-report', [
            'requirement' => $requirement,
            'semester' => $semester,
            'submittedUsers' => $submittedUsers,
            'notSubmittedUsers' => $notSubmittedUsers,
            'totalAssignedUsers' => $assignedUsers->count(),
            'submittedRequirements' => $submittedUsers // Add this for easier access in blade
        ]);

        return $pdf->stream("preview_{$semester->name}.pdf");
    }
}
