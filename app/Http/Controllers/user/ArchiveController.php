<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->withCount(['requirements as total_requirements'])
            ->get();

        // Get files with filters applied - query files from ALL archived semesters
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

        // Debug information - you can remove this after testing
        if ($semesters->count() > 0 && $files->count() === 0) {
            // Check if there are any files in archived semesters
            $totalArchivedFiles = SubmittedRequirement::with(['requirement', 'submissionFile', 'requirement.semester'])
                ->where('user_id', Auth::id())
                ->whereHas('requirement.semester', function($query) {
                    $query->where('is_active', false);
                })
                ->count();
                
            // Log debug information
            \Log::info('Archive debug', [
                'user_id' => Auth::id(),
                'archived_semesters_count' => $semesters->count(),
                'total_archived_files' => $totalArchivedFiles,
                'filtered_files_count' => $files->count(),
                'search_filter' => $search,
                'status_filter' => $statusFilter,
                'semester_filter' => $semesterFilter
            ]);
        }

        // Pass all variables to the view
        return view('archive', compact(
            'semesters', 
            'files', 
            'search', 
            'statusFilter', 
            'semesterFilter'
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
            
        return view('file-manager.archive-semester', compact('semester', 'files'));
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
    
    // NEW: Debug method to check archive status
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
            $desiredDirectory = $file->getMediaDirectory();
            
            // Check if media needs to be moved
            if ($media->directory !== $desiredDirectory) {
                // Update the directory in the database
                $media->update(['directory' => $desiredDirectory]);
                
                // Physically move the file if it exists
                $currentPath = $media->getPath();
                $newPath = $desiredDirectory . '/' . $media->file_name;
                
                if (Storage::disk('public')->exists($currentPath)) {
                    // Ensure the destination directory exists
                    Storage::disk('public')->makeDirectory($desiredDirectory);
                    
                    // Move the file
                    Storage::disk('public')->move($currentPath, $newPath);
                    $organizedCount++;
                }
            }
        }
    }
    
    return redirect()->route('user.archive')
        ->with('success', "Organized {$organizedCount} files into semester folders.");
}
}