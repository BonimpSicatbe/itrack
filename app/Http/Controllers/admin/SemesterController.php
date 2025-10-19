<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
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
}
