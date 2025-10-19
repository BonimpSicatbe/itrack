<?php

namespace App\Http\Controllers\User;

use App\Models\Semester;
use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        // Initialize variables with default values
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', '');
        $semesterFilter = $request->input('semester', '');
        
        // Get all archived (inactive) semesters
        $semesters = Semester::query()
            ->where('is_active', false)
            ->orderByDesc('end_date')
            ->get();

        // Get files with filters applied
        $filesQuery = SubmittedRequirement::with(['requirement', 'submissionFile', 'requirement.semester'])
            ->where('user_id', Auth::id())
            ->whereHas('requirement.semester', function($query) {
                $query->where('is_active', false);
            });
        
        // Apply search filter
        if (!empty($search)) {
            $filesQuery->where(function($q) use ($search) {
                $q->whereHas('submissionFile', function($q2) use ($search) {
                    $q2->where('file_name', 'like', '%' . $search . '%');
                })->orWhereHas('requirement', function($q2) use ($search) {
                    $q2->where('name', 'like', '%' . $search . '%');
                });
            });
        }
        
        // Apply status filter
        if (!empty($statusFilter)) {
            $filesQuery->where('status', $statusFilter);
        }
        
        // Apply semester filter
        if (!empty($semesterFilter)) {
            $filesQuery->whereHas('requirement', function($q) use ($semesterFilter) {
                $q->where('semester_id', $semesterFilter);
            });
        }
        
        $files = $filesQuery->orderBy('created_at', 'desc')->get();

        // Pass all variables to the view - MAKE SURE ALL VARIABLES ARE INCLUDED
        return view('user.archive', compact(
            'semesters', 
            'files', 
            'search', 
            'statusFilter', 
            'semesterFilter' // This was missing!
        ));
    }
    
    public function show($semesterId)
    {
        $semester = Semester::findOrFail($semesterId);
        
        // Make sure the semester is archived
        if ($semester->is_active) {
            return redirect()->route('user.archive')->with('error', 'Cannot view active semester in archive.');
        }
        
        $files = SubmittedRequirement::with(['requirement', 'submissionFile'])
            ->where('user_id', Auth::id())
            ->whereHas('requirement', function($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('user.archive-semester', compact('semester', 'files'));
    }
    
    // Archive the current active semester (Admin only)
    public function archiveActiveSemester()
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            return redirect()->route('user.file-manager')
                ->with('error', 'Only administrators can archive semesters.');
        }
        
        // Get the active semester
        $activeSemester = Semester::where('is_active', true)->first();
        
        if (!$activeSemester) {
            return redirect()->route('user.file-manager')
                ->with('error', 'No active semester to archive.');
        }
        
        // Mark semester as archived
        $activeSemester->update(['is_active' => false]);
        
        return redirect()->route('user.archive')
            ->with('success', 'Semester "' . $activeSemester->name . '" archived successfully!');
    }
    
    // Get files for a specific archived semester
    public function getSemesterFiles($semesterId)
    {
        $semester = Semester::findOrFail($semesterId);
        
        $files = SubmittedRequirement::with(['requirement', 'submissionFile', 'user'])
            ->whereHas('requirement', function($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($files);
    }
    
    // Debug method to check archive status
    public function debugArchive()
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $archivedSemesters = Semester::where('is_active', false)->get();
        $debugInfo = [];
        
        foreach ($archivedSemesters as $semester) {
            $requirementsCount = Requirement::where('semester_id', $semester->id)->count();
            $filesCount = SubmittedRequirement::whereHas('requirement', function($q) use ($semester) {
                $q->where('semester_id', $semester->id);
            })->count();
            
            $debugInfo[] = [
                'semester' => $semester->name,
                'requirements_count' => $requirementsCount,
                'files_count' => $filesCount,
                'is_active' => $semester->is_active
            ];
        }
        
        return response()->json($debugInfo);
    }

    public function organizeArchivedFiles()
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('user.archive')
                ->with('error', 'Only administrators can organize archived files.');
        }
        
        // Get all archived files
        $archivedFiles = SubmittedRequirement::whereHas('requirement.semester', function($query) {
            $query->where('is_active', false);
        })->get();
        
        $organizedCount = 0;
        
        foreach ($archivedFiles as $file) {
            $media = $file->getFirstMedia('submission_files');
            
            if ($media) {
                // Create directory path based on semester
                $semester = $file->requirement->semester;
                $desiredDirectory = 'archived/' . $semester->id;
                
                // Get current path and new path
                $currentPath = $media->getPath();
                $newPath = $desiredDirectory . '/' . $media->file_name;
                
                // Check if file exists and needs to be moved
                if (Storage::disk('public')->exists($currentPath) && $currentPath !== $newPath) {
                    try {
                        // Ensure the destination directory exists
                        Storage::disk('public')->makeDirectory($desiredDirectory);
                        
                        // Move the file
                        Storage::disk('public')->move($currentPath, $newPath);
                        
                        // Update the media record
                        $media->update([
                            'directory' => $desiredDirectory,
                            'file_path' => $newPath
                        ]);
                        
                        $organizedCount++;
                    } catch (\Exception $e) {
                        Log::error('Failed to move file: ' . $e->getMessage());
                    }
                }
            }
        }
        
        return redirect()->route('user.archive')
            ->with('success', "Organized {$organizedCount} files into semester folders.");
    }

    // Activate a semester (Admin only)
    public function activateSemester($semesterId)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('user.archive')
                ->with('error', 'Only administrators can activate semesters.');
        }
        
        $semester = Semester::findOrFail($semesterId);
        
        // Deactivate all other semesters
        Semester::where('id', '!=', $semesterId)->update(['is_active' => false]);
        
        // Activate the selected semester
        $semester->update(['is_active' => true]);
        
        return redirect()->route('user.archive')
            ->with('success', 'Semester "' . $semester->name . '" activated successfully!');
    }

    // Delete an archived semester (Admin only)
    public function deleteSemester($semesterId)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('user.archive')
                ->with('error', 'Only administrators can delete semesters.');
        }
        
        $semester = Semester::findOrFail($semesterId);
        
        // Check if semester is active
        if ($semester->is_active) {
            return redirect()->route('user.archive')
                ->with('error', 'Cannot delete active semester. Please deactivate it first.');
        }
        
        // Check if semester has files
        $filesCount = SubmittedRequirement::whereHas('requirement', function($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        })->count();
        
        if ($filesCount > 0) {
            return redirect()->route('user.archive')
                ->with('error', 'Cannot delete semester with files. Please delete the files first.');
        }
        
        // Delete the semester
        $semesterName = $semester->name;
        $semester->delete();
        
        return redirect()->route('user.archive')
            ->with('success', 'Semester "' . $semesterName . '" deleted successfully!');
    }

    // Export archived files for a semester
    public function exportSemester($semesterId)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('user.archive')
                ->with('error', 'Only administrators can export semester data.');
        }
        
        $semester = Semester::findOrFail($semesterId);
        
        $files = SubmittedRequirement::with(['requirement', 'submissionFile', 'user'])
            ->whereHas('requirement', function($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Prepare CSV data
        $csvData = [];
        $csvData[] = ['File Name', 'Requirement', 'User', 'Status', 'Submitted At', 'Size'];
        
        foreach ($files as $file) {
            $csvData[] = [
                $file->submissionFile->file_name ?? 'N/A',
                $file->requirement->name ?? 'N/A',
                $file->user->name ?? 'N/A',
                $file->status,
                $file->created_at->format('Y-m-d H:i:s'),
                $file->submissionFile->size ?? '0'
            ];
        }
        
        // Generate CSV file
        $filename = 'semester_' . $semester->name . '_export_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'w');
        
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // Bulk actions for archived files
    public function bulkAction(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $action = $request->input('action');
        $fileIds = $request->input('file_ids', []);
        
        if (empty($fileIds)) {
            return redirect()->route('user.archive')
                ->with('error', 'No files selected for bulk action.');
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($fileIds as $fileId) {
            try {
                $file = SubmittedRequirement::findOrFail($fileId);
                
                switch ($action) {
                    case 'delete':
                        if ($file->deleteFile()) {
                            $file->delete();
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                        break;
                    
                    case 'change_status':
                        $newStatus = $request->input('new_status');
                        if (in_array($newStatus, ['approved', 'rejected', 'revision_needed', 'under_review'])) {
                            $file->update(['status' => $newStatus]);
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                        break;
                    
                    default:
                        $errorCount++;
                        break;
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Bulk action error: ' . $e->getMessage());
            }
        }
        
        $message = "Bulk action completed: {$successCount} successful, {$errorCount} failed.";
        
        return redirect()->route('user.archive')
            ->with('success', $message);
    }
}