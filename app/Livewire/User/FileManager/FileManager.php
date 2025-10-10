<?php

namespace App\Livewire\User\FileManager;

use Livewire\Component;
use Livewire\WithUrlPagination;
use App\Models\SubmittedRequirement;
use App\Models\Semester;
use App\Models\Requirement;
use App\Models\Course;
use App\Models\CourseAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManager extends Component
{
    public $search = '';
    public $searchResults = [];
    public $showSearchResults = false;
    public $searchQuery = '';
    public $statusFilter = '';
    public $isNavigating = false;
    public $viewMode = 'grid';
    
    // Navigation properties
    public $breadcrumb = [];
    public $currentLevel = 'semesters'; 
    public $currentSemester = null;
    public $currentCourse = null;
    public $currentParentFolder = null;
    public $currentSubFolder = null;

    // URL query parameters
    public $semesterId = null;
    public $courseId = null;
    public $folderId = null;
    public $subFolderId = null;
    
    // Semester properties
    public $activeSemester = null;
    public $selectedSemesterId = null;
    public $allSemesters = [];
    public $semesterMessage = null;
    public $daysRemaining;
    public $semesterProgress;

    // Course properties
    public $assignedCourses = [];
    
    // Folder content properties
    public $parentFolderContents = [];
    public $contentType = ''; 

    // File selection properties
    public $selectedFile = null;
    
    public $showDeleteModal = false;
    public $fileToDelete = null;
    
    protected $listeners = [
        'refreshFiles' => '$refresh', 
        'semesterActivated' => 'refreshSemesterData',
        'semesterArchived' => 'refreshSemesterData',
        'navigateTo' => 'handleNavigation',
        'updateBreadcrumb' => 'updateBreadcrumb',
        'navigateAfterSearch' => 'handleNavigateAfterSearch',
        'navigateToFileAfterSearch' => 'handleNavigateToFileAfterSearch'
    ];

    protected $queryString = [
        'semesterId' => ['except' => ''],
        'courseId' => ['except' => ''],
        'folderId' => ['except' => ''],
        'subFolderId' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
        'searchQuery' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->initializeFromUrl();
        
        $this->refreshSemesterData();
        $this->loadAllSemesters();
        
        $this->viewMode = session()->get('fileManagerViewMode', 'grid');
        
        if (!$this->semesterId && $this->activeSemester) {
            $this->selectedSemesterId = $this->activeSemester->id;
            $this->currentSemester = $this->activeSemester;
            $this->loadAssignedCourses();
            $this->initializeBreadcrumb();
        }
    }

    /**
     * Initialize component state from URL parameters
     */
    public function initializeFromUrl()
    {
        if ($this->subFolderId) {
            // We're at a sub-folder level
            $this->handleNavigationFromUrl('parent_folder_contents', $this->subFolderId, true);
        } elseif ($this->folderId) {
            // We're at a parent folder level
            $this->handleNavigationFromUrl('parent_folder_contents', $this->folderId, true);
        } elseif ($this->courseId) {
            // We're at a course level
            $this->handleNavigationFromUrl('parent_folders', $this->courseId, true);
        } elseif ($this->semesterId) {
            // We're at a semester level
            $this->handleNavigationFromUrl('courses', $this->semesterId, true);
        } else {
            // We're at the root level
            $this->currentLevel = 'semesters';
            $this->initializeBreadcrumb();
        }
    }

    /**
     * Handle navigation from URL parameters (without updating URL again)
     */
    public function handleNavigationFromUrl($level, $id = null, $fromUrl = false)
    {
        $this->statusFilter = '';
        if ($this->isNavigating) return;

        $this->isNavigating = true; 

        try {
            $this->currentLevel = $level;
            
            if ($level === 'semesters') {
                $this->currentSemester = null;
                $this->currentCourse = null;
                $this->currentParentFolder = null;
                $this->currentSubFolder = null;
                $this->selectedSemesterId = null;
                $this->assignedCourses = [];
                $this->deselectFile();
                $this->breadcrumb = [['name' => 'File Manager', 'level' => 'semesters', 'id' => null]];
                
                // Clear URL parameters
                if (!$fromUrl) {
                    $this->updateUrlParameters('', '', '', '');
                }
            } elseif ($level === 'courses' && $id) {
                // Load semester with assigned courses
                $this->currentSemester = Semester::find($id);
                
                if (!$this->currentSemester) {
                    $this->currentLevel = 'semesters';
                    return;
                }
                
                $this->selectedSemesterId = $id;
                $this->loadAssignedCourses();
                $this->currentCourse = null;
                $this->currentParentFolder = null;
                $this->currentSubFolder = null;
                $this->deselectFile();
                
                // Update breadcrumb
                $this->breadcrumb = [
                    ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                    ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $id]
                ];
                
                // Update URL parameters
                if (!$fromUrl) {
                    $this->updateUrlParameters($id, null, null, null);
                }
            } elseif ($level === 'parent_folders' && $id) {
                // Load course
                $this->currentCourse = Course::with('program')->find($id);
                
                if (!$this->currentCourse) {
                    if ($this->currentSemester) {
                        $this->currentLevel = 'courses';
                    } else {
                        $this->currentLevel = 'semesters';
                    }
                    return;
                }
                
                // Make sure we have the current semester set
                if (!$this->currentSemester) {
                    $this->currentSemester = $this->activeSemester;
                    $this->selectedSemesterId = $this->currentSemester->id;
                }
                
                $this->currentParentFolder = null;
                $this->currentSubFolder = null;
                $this->deselectFile();
                
                // Update breadcrumb
                $this->breadcrumb = [
                    ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                    ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $this->currentSemester->id],
                    ['name' => $this->currentCourse->course_code, 'level' => 'parent_folders', 'id' => $id]
                ];
                
                // Update URL parameters
                if (!$fromUrl) {
                    $this->updateUrlParameters($this->currentSemester->id, $id, null, null);
                }
            } elseif ($level === 'parent_folder_contents' && $id) {
                // Check if this is a parent folder or sub-folder
                $folder = \App\Models\RequirementType::find($id);
                
                if (!$folder) {
                    $this->currentLevel = 'parent_folders';
                    return;
                }
                
                if ($folder->parent_id === null) {
                    // This is a parent folder
                    $this->currentParentFolder = $folder;
                    $this->currentSubFolder = null;
                    
                    // Update URL parameters for parent folder
                    if (!$fromUrl) {
                        $this->updateUrlParameters(
                            $this->currentSemester->id, 
                            $this->currentCourse->id, 
                            $id, 
                            null
                        );
                    }
                } else {
                    // This is a sub-folder - find the parent folder
                    $this->currentSubFolder = $folder;
                    $this->currentParentFolder = \App\Models\RequirementType::find($folder->parent_id);
                    
                    // Update URL parameters for sub-folder
                    if (!$fromUrl) {
                        $this->updateUrlParameters(
                            $this->currentSemester->id, 
                            $this->currentCourse->id, 
                            $this->currentParentFolder->id, 
                            $id
                        );
                    }
                }
                
                // Determine if this folder has sub-folders or direct files
                $this->loadParentFolderContents($id);
                
                $this->deselectFile();
                
                // Update breadcrumb with proper hierarchy
                $this->breadcrumb = [
                    ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                    ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $this->currentSemester->id],
                    ['name' => $this->currentCourse->course_code, 'level' => 'parent_folders', 'id' => $this->currentCourse->id],
                ];
                
                // Add parent folder to breadcrumb
                if ($this->currentParentFolder) {
                    $this->breadcrumb[] = [
                        'name' => $this->currentParentFolder->name, 
                        'level' => 'parent_folder_contents', 
                        'id' => $this->currentParentFolder->id
                    ];
                }
                
                // Add sub-folder to breadcrumb if viewing a sub-folder
                if ($this->currentSubFolder) {
                    $this->breadcrumb[] = [
                        'name' => $this->currentSubFolder->name, 
                        'level' => 'parent_folder_contents', 
                        'id' => $this->currentSubFolder->id
                    ];
                }
            }
            
            $this->dispatch('levelChanged', $level, $id);
        } catch (\Exception $e) {
            \Log::error('Navigation error: ' . $e->getMessage());
            $this->currentLevel = 'semesters';
            $this->currentSemester = null;
            $this->currentCourse = null;
            $this->currentParentFolder = null;
            $this->currentSubFolder = null;
            $this->breadcrumb = [['name' => 'File Manager', 'level' => 'semesters', 'id' => null]];
            $this->updateUrlParameters(null, null, null, null);
        } finally {
            $this->isNavigating = false;
        }
    }

    /**
     * Update URL parameters
     */
    private function updateUrlParameters($semesterId = null, $courseId = null, $folderId = null, $subFolderId = null)
    {
        $this->semesterId = $semesterId ?: '';
        $this->courseId = $courseId ?: '';
        $this->folderId = $folderId ?: '';
        $this->subFolderId = $subFolderId ?: '';
    }

    public function initializeBreadcrumb()
    {
        $this->breadcrumb = [
            ['name' => 'File Manager', 'level' => 'semesters', 'id' => null]
        ];
        
        if ($this->currentLevel === 'courses' && $this->currentSemester) {
            $this->breadcrumb[] = [
                'name' => $this->currentSemester->name, 
                'level' => 'courses', 
                'id' => $this->currentSemester->id
            ];
        }
        
        if ($this->currentLevel === 'parent_folders' && $this->currentCourse) {
            $this->breadcrumb[] = [
                'name' => $this->currentSemester->name, 
                'level' => 'courses', 
                'id' => $this->currentSemester->id
            ];
            $this->breadcrumb[] = [
                'name' => $this->currentCourse->course_code, 
                'level' => 'parent_folders', 
                'id' => $this->currentCourse->id
            ];
        }
        
        if ($this->currentLevel === 'parent_folder_contents' && $this->currentParentFolder) {
            $this->breadcrumb[] = [
                'name' => $this->currentSemester->name, 
                'level' => 'courses', 
                'id' => $this->currentSemester->id
            ];
            $this->breadcrumb[] = [
                'name' => $this->currentCourse->course_code, 
                'level' => 'parent_folders', 
                'id' => $this->currentCourse->id
            ];
            $this->breadcrumb[] = [
                'name' => $this->currentParentFolder->name, 
                'level' => 'parent_folder_contents', 
                'id' => $this->currentParentFolder->id
            ];
            
            if ($this->currentSubFolder) {
                $this->breadcrumb[] = [
                    'name' => $this->currentSubFolder->name, 
                    'level' => 'parent_folder_contents', 
                    'id' => $this->currentSubFolder->id
                ];
            }
        }
    }

    public function loadAllSemesters()
    {
        $this->allSemesters = Semester::orderBy('start_date', 'desc')->get();
    }

    public function loadAssignedCourses()
    {
        if (!$this->currentSemester) {
            $this->assignedCourses = [];
            return;
        }

        $userId = Auth::id();
        
        $this->assignedCourses = CourseAssignment::where('professor_id', $userId)
            ->where('semester_id', $this->currentSemester->id)
            ->with(['course', 'course.program'])
            ->get()
            ->pluck('course')
            ->unique('id')
            ->values();
    }

    public function refreshSemesterData()
    {
        $this->activeSemester = Semester::getActiveSemester();
        
        if ($this->activeSemester) {
            $this->calculateSemesterStats();
            $this->setSemesterMessage();
        } else {
            $this->semesterMessage = 'No active semester found. Please contact administrator.';
        }
    }

    private function calculateSemesterStats()
    {
        $now = now();
        $startDate = $this->activeSemester->start_date;
        $endDate = $this->activeSemester->end_date;

        // Calculate days remaining
        $this->daysRemaining = $now->diffInDays($endDate, false);
        
        // If semester has ended, set days remaining to 0
        if ($this->daysRemaining < 0) {
            $this->daysRemaining = 0;
        }

        // Calculate semester progress percentage
        $totalDays = $startDate->diffInDays($endDate);
        $daysPassed = $startDate->diffInDays($now);
        
        if ($totalDays > 0) {
            $this->semesterProgress = min(100, max(0, ($daysPassed / $totalDays) * 100));
        } else {
            $this->semesterProgress = 0;
        }
    }

    private function setSemesterMessage()
    {
        if ($this->daysRemaining < 0) {
            $this->semesterMessage = 'Current semester has ended. Submissions may be restricted.';
        } elseif ($this->daysRemaining <= 30) {
            $this->semesterMessage = "Semester ends in {$this->daysRemaining} days ({$this->activeSemester->end_date->format('M d, Y')}).";
        } else {
            $this->semesterMessage = null;
        }
    }

    public function getProgressColorProperty()
    {
        if ($this->semesterProgress >= 90) {
            return 'bg-red-500';
        } elseif ($this->semesterProgress >= 70) {
            return 'bg-orange-500';
        } else {
            return 'bg-green-500';
        }
    }

    public function getStatusColorProperty()
    {
        if (!$this->activeSemester) {
            return 'text-gray-500';
        }

        if ($this->daysRemaining <= 0) {
            return 'text-red-500';
        } elseif ($this->daysRemaining <= 30) {
            return 'text-orange-500';
        } else {
            return 'text-green-500';
        }
    }

    public function handleNavigation($level, $id = null)
    {
        $this->handleNavigationFromUrl($level, $id, false);
    }

    /**
     * Load contents of a parent folder - either sub-folders or files
     */
    private function loadParentFolderContents($folderId)
    {
        $this->parentFolderContents = [];
        $this->contentType = '';
        
        // Check if this folder has sub-folders that actually have files
        $subFolders = \App\Models\RequirementType::where('parent_id', $folderId)
            ->where('is_folder', true)
            ->get()
            ->filter(function ($subFolder) {
                // Only keep sub-folders that have files (in the sub-folder or its children)
                return $this->folderHasFiles($subFolder->id);
            });
        
        if ($subFolders->count() > 0) {
            // Show only sub-folders that have files
            $this->contentType = 'sub_folders';
            $this->parentFolderContents = $subFolders;
        } else {
            // Show files (submitted requirements for requirements assigned to this folder or its sub-folders)
            $this->contentType = 'files';
            $this->parentFolderContents = $this->getSubmittedFilesForFolder($folderId);
        }
    }

    /**
     * Check if a folder (or its sub-folders) has any submitted files
     */
    private function folderHasFiles($folderId)
    {
        $user = Auth::user();
        $courseId = $this->currentCourse->id;
        $semesterId = $this->currentSemester->id;
        
        // Get all folder IDs to check (current folder + all its sub-folders)
        $folderIdsToCheck = $this->getAllSubFolderIds($folderId);
        $folderIdsToCheck[] = $folderId;

        // Check if any requirements assigned to these folders have submissions
        $requirementsWithSubmissions = Requirement::where('semester_id', $semesterId)
            ->where(function($query) use ($folderIdsToCheck) {
                foreach ($folderIdsToCheck as $checkFolderId) {
                    $query->orWhereJsonContains('requirement_type_ids', $checkFolderId);
                }
            })
            ->whereHas('userSubmissions', function($query) use ($user, $courseId) {
                $query->where('user_id', $user->id)
                    ->where('course_id', $courseId);
            })
            ->exists();

        return $requirementsWithSubmissions;
    }

    private function getAllSubFolderIds($parentFolderId)
    {
        $allSubFolderIds = [];
        
        // Get direct sub-folders
        $subFolders = \App\Models\RequirementType::where('parent_id', $parentFolderId)
            ->where('is_folder', true)
            ->get();
        
        foreach ($subFolders as $subFolder) {
            $allSubFolderIds[] = $subFolder->id;
            
            // Recursively get sub-folders of sub-folders
            $nestedSubFolderIds = $this->getAllSubFolderIds($subFolder->id);
            $allSubFolderIds = array_merge($allSubFolderIds, $nestedSubFolderIds);
        }
        
        return $allSubFolderIds;
    }

    /**
     * Get submitted files for requirements assigned to a specific folder
     */
    private function getSubmittedFilesForFolder($folderId)
    {
        $user = Auth::user();
        
        // Get all requirements for the current semester
        $allRequirements = Requirement::where('semester_id', $this->currentSemester->id)
            ->with(['userSubmissions' => function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('course_id', $this->currentCourse->id)
                    ->with('submissionFile')
                    ->orderBy('submitted_at', 'desc');
            }])
            ->with('guides')
            ->orderBy('due', 'asc')
            ->get();

        // Filter requirements that are assigned to this folder via requirement_type_ids
        $folderRequirements = $allRequirements->filter(function ($requirement) use ($user, $folderId) {
            if (!$this->isUserAssignedToRequirement($requirement, $user)) {
                return false;
            }
            
            // Check if requirement is assigned to this folder via requirement_type_ids
            $requirementTypeIds = $requirement->requirement_type_ids ?? [];
            if (empty($requirementTypeIds)) {
                return false;
            }
            
            // Convert to array if it's a JSON string
            if (is_string($requirementTypeIds)) {
                $requirementTypeIds = json_decode($requirementTypeIds, true);
            }
            
            return in_array($folderId, $requirementTypeIds);
        });

        // Collect all submitted files from the filtered requirements
        $submittedFiles = collect();
        
        foreach ($folderRequirements as $requirement) {
            foreach ($requirement->userSubmissions as $submission) {
                $submission->requirement_name = $requirement->name;
                $submittedFiles->push($submission);
            }
        }

        return $submittedFiles;
    }

    public function updateBreadcrumb($breadcrumb)
    {
        $this->breadcrumb = $breadcrumb;
    }

    public function selectFile($submissionId)
    {
        try {
            // Find and set the selected file directly
            $this->selectedFile = SubmittedRequirement::where('user_id', Auth::id())
                ->with(['requirement', 'submissionFile', 'user'])
                ->findOrFail($submissionId);

            $this->dispatch('fileSelected');

        } catch (\Exception $e) {
            \Log::error('Error selecting file: ' . $e->getMessage());
            $this->selectedFile = null;
        }
    }

    public function deselectFile()
    {
        $this->selectedFile = null;
    }

    public function closeFileDetails()
    {
        $this->selectedFile = null;
        $this->dispatch('fileDetailsClosed');
    }

    public function getDownloadRoute($submissionId)
    {
        return route('file.download', $submissionId);
    }

    public function getPreviewRoute($submissionId)
    {
        return route('file.preview', $submissionId);
    }

    public function canPreview($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $previewableTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        
        return in_array($extension, $previewableTypes);
    }

    public function getFileIcon($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $icons = SubmittedRequirement::FILE_ICONS;
        return $icons[$extension]['icon'] ?? $icons['default']['icon'];
    }

    public function getFileIconColor($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $icons = SubmittedRequirement::FILE_ICONS;
        return $icons[$extension]['color'] ?? $icons['default']['color'];
    }

    public function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function closeSearchResults()
    {
        $this->showSearchResults = false;
    }

    public function clearFilters()
    {
        $this->searchQuery = '';
        $this->statusFilter = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
    }

    public function confirmDelete($fileId)
    {
        $this->fileToDelete = $fileId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->fileToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteSubmission()
    {
        if ($this->fileToDelete) {
            try {
                $submission = SubmittedRequirement::where('user_id', Auth::id())
                    ->findOrFail($this->fileToDelete);
                
                // Delete the associated file from storage
                if ($submission->submissionFile) {
                    Storage::delete($submission->submissionFile->file_path);
                    $submission->submissionFile->delete();
                }
                
                $submission->delete();
                
                $this->showDeleteModal = false;
                $this->fileToDelete = null;
                $this->selectedFile = null;
                
                session()->flash('success', 'File deleted successfully.');
                $this->dispatch('refreshFiles');
                
            } catch (\Exception $e) {
                \Log::error('Error deleting file: ' . $e->getMessage());
                session()->flash('error', 'Error deleting file: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        $this->ensureNavigationData();

        $allSemesters = $this->allSemesters;
        $currentSemester = $this->currentSemester;
        $currentCourse = $this->currentCourse;
        $currentParentFolder = $this->currentParentFolder;
        $currentSubFolder = $this->currentSubFolder;
        $parentFolders = [];
        $parentFolderContents = $this->parentFolderContents;
        $contentType = $this->contentType;

        // Load parent folders for current course if at parent_folders level
        if ($this->currentLevel === 'parent_folders' && $currentCourse && $currentSemester) {
            $parentFolders = $this->getParentFoldersWithSubmissions();
        }

        // Filter files if at parent_folder_contents level with files content type
        if ($this->currentLevel === 'parent_folder_contents' && $contentType === 'files' && !empty($this->statusFilter)) {
            $parentFolderContents = $parentFolderContents->filter(function ($submission) {
                return $submission->status === $this->statusFilter;
            });
        }
        
        return view('livewire.user.file-manager.file-manager', [
            'totalFiles' => $this->getTotalFiles(),
            'totalSize' => $this->getTotalSize(),
            'statuses' => SubmittedRequirement::statuses(),
            'allSemesters' => $allSemesters,
            'currentSemester' => $currentSemester,
            'currentCourse' => $currentCourse,
            'currentParentFolder' => $currentParentFolder,
            'currentSubFolder' => $currentSubFolder,
            'parentFolders' => $parentFolders,
            'parentFolderContents' => $parentFolderContents,
            'contentType' => $contentType,
            'assignedCourses' => $this->assignedCourses,
            'archiveRoute' => route('user.archive'),
        ]);
    }

    /**
     * Get parent folders that have submitted files (including files in sub-folders)
     */
    private function getParentFoldersWithSubmissions()
    {
        $userId = Auth::id();
        $courseId = $this->currentCourse->id;
        $semesterId = $this->currentSemester->id;

        // Get all parent folders
        $parentFolders = \App\Models\RequirementType::whereNull('parent_id')
            ->where('is_folder', true)
            ->orderBy('id')
            ->get();

        // Filter to only include folders that have files (in themselves or their sub-folders)
        return $parentFolders->filter(function ($folder) use ($userId, $courseId, $semesterId) {
            return $this->folderHasFiles($folder->id);
        });
    }

    /**
     * Get all folder IDs including parent folders of sub-folders that have submissions
     */
    private function getAllRelevantFolderIds($folderIds)
    {
        $allIds = [];
        
        foreach ($folderIds as $folderId) {
            $this->addFolderAndAncestors($folderId, $allIds);
        }
        
        return array_unique($allIds);
    }

    /**
     * Recursively add folder and all its parent folders to the list
     */
    private function addFolderAndAncestors($folderId, &$allIds)
    {
        if (in_array($folderId, $allIds)) {
            return;
        }
        
        $folder = \App\Models\RequirementType::find($folderId);
        if (!$folder) {
            return;
        }
        
        $allIds[] = $folderId;
        
        // If this folder has a parent, add it too
        if ($folder->parent_id) {
            $this->addFolderAndAncestors($folder->parent_id, $allIds);
        }
    }


    /**
     * Check if a folder (or its sub-folders) has any submitted files
     */
    private function folderHasSubmissions($folderId, $userId, $courseId, $semesterId)
    {
        // Get all requirements for this semester
        $requirements = Requirement::where('semester_id', $semesterId)
            ->where(function($query) use ($folderId) {
                // Check if requirement is assigned to this folder or any of its sub-folders
                $query->whereJsonContains('requirement_type_ids', $folderId)
                    ->orWhereJsonContains('requirement_type_ids', (string)$folderId);
            })
            ->get();

        // Check if any of these requirements have submissions from this user/course
        foreach ($requirements as $requirement) {
            $hasSubmissions = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->exists();
                
            if ($hasSubmissions) {
                return true;
            }
        }

        // Check sub-folders recursively
        $subFolders = \App\Models\RequirementType::where('parent_id', $folderId)
            ->where('is_folder', true)
            ->get();

        foreach ($subFolders as $subFolder) {
            if ($this->folderHasSubmissions($subFolder->id, $userId, $courseId, $semesterId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is assigned to a requirement based on program
     */
    private function isUserAssignedToRequirement($requirement, $user)
    {
        $rawAssignedTo = $requirement->getRawOriginal('assigned_to');
        
        if (is_string($rawAssignedTo)) {
            $assignedTo = json_decode($rawAssignedTo, true);
        } else {
            $assignedTo = $requirement->assigned_to;
        }
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $assignedTo = [];
        }

        $programs = $assignedTo['programs'] ?? [];
        $selectAllPrograms = $assignedTo['selectAllPrograms'] ?? false;

        // Check if user's course has a program
        if (!$this->currentCourse || !$this->currentCourse->program_id) {
            return false;
        }

        // Convert program ID to string for comparison
        $userProgramId = (string)$this->currentCourse->program_id;

        // Check program assignment
        $programAssigned = $selectAllPrograms || 
                          (is_array($programs) && in_array($userProgramId, $programs));

        return $programAssigned;
    }

    protected function getTotalFiles()
    {
        $query = SubmittedRequirement::where('user_id', Auth::id())
            ->whereHas('submissionFile');
        
        if ($this->selectedSemesterId) {
            $query->whereHas('requirement', function($q) {
                $q->where('semester_id', $this->selectedSemesterId);
            });
        } else {
            $query->whereHas('requirement.semester', function($q) {
                $q->where('is_active', true);
            });
        }
        
        return $query->count();
    }

    protected function getTotalSize()
    {
        $query = SubmittedRequirement::where('user_id', Auth::id());
        
        if ($this->selectedSemesterId) {
            $query->whereHas('requirement', function($q) {
                $q->where('semester_id', $this->selectedSemesterId);
            });
        } else {
            $query->whereHas('requirement.semester', function($q) {
                $q->where('is_active', true);
            });
        }
        
        $submissions = $query->with('submissionFile')->get();
        
        $totalSize = 0;
        foreach ($submissions as $submission) {
            if ($submission->submissionFile) {
                $totalSize += $submission->submissionFile->size ?? 0;
            }
        }
        
        return $this->formatFileSize($totalSize);
    }

    public function changeViewMode($mode)
    {
        $this->viewMode = $mode;
        session()->put('fileManagerViewMode', $mode);
    }

    public function updatedSearchQuery($value)
    {
        if (empty($value)) {
            $this->searchResults = [];
            $this->showSearchResults = false;
            return;
        }

        $this->searchResults = $this->performSearch($value);
        $this->showSearchResults = !empty($this->searchResults);
    }

    protected function performSearch($query)
    {
        $results = [];
        $userId = Auth::id();
        
        // Search semesters (no changes needed)
        $semesters = Semester::where('name', 'like', "%{$query}%")
            ->orderBy('start_date', 'desc')
            ->get();
        
        foreach ($semesters as $semester) {
            $results[] = [
                'type' => 'semester',
                'id' => $semester->id,
                'name' => $semester->name,
                'description' => $semester->start_date->format('M Y') . ' - ' . $semester->end_date->format('M Y'),
                'icon' => 'fa-folder',
                'icon_color' => 'text-green-700',
                'semester_id' => $semester->id
            ];
        }
        
        // Search courses assigned to the user (no changes needed)
        $courses = Course::whereHas('assignments', function($q) use ($userId) {
                $q->where('professor_id', $userId);
            })
            ->where(function($q) use ($query) {
                $q->where('course_code', 'like', "%{$query}%")
                ->orWhere('course_name', 'like', "%{$query}%");
            })
            ->with('program')
            ->get();
        
        foreach ($courses as $course) {
            $programName = $course->program ? $course->program->program_name : 'No Program';
            $results[] = [
                'type' => 'course',
                'id' => $course->id,
                'name' => $course->course_code,
                'description' => $course->course_name . ' • ' . $programName,
                'icon' => 'fa-folder',
                'icon_color' => 'text-green-700',
                'course_id' => $course->id
            ];
        }
        
        // Search parent folders WITH COURSE CONTEXT
        $this->searchParentFoldersWithContext($query, $userId, $results);
        
        // Search sub-folders WITH COURSE CONTEXT  
        $this->searchSubFoldersWithContext($query, $userId, $results);
        
        // Search files WITH COURSE CONTEXT
        $this->searchFilesWithContext($query, $userId, $results);
        
        return $results;
    }

    /**
     * Search parent folders with course context for duplicates
     */
    private function searchParentFoldersWithContext($query, $userId, &$results)
    {
        // Get all courses assigned to the user
        $userCourses = CourseAssignment::where('professor_id', $userId)
            ->with(['course', 'course.program', 'semester'])
            ->get();

        foreach ($userCourses as $assignment) {
            $course = $assignment->course;
            $semester = $assignment->semester;
            
            // Get all parent folders matching the search query
            $parentFolders = \App\Models\RequirementType::whereNull('parent_id')
                ->where('is_folder', true)
                ->where('name', 'like', "%{$query}%")
                ->get()
                ->filter(function ($folder) use ($userId, $course, $semester) {
                    // Check if this folder OR ANY OF ITS SUB-FOLDERS has submissions for the current course
                    return $this->folderOrSubfoldersHaveSubmissions($folder->id, $userId, $course->id, $semester->id);
                });

            foreach ($parentFolders as $folder) {
                $programName = $course->program ? $course->program->program_name : 'No Program';
                $results[] = [
                    'type' => 'parent_folder',
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => '',
                    'icon' => 'fa-folder',
                    'icon_color' => 'text-green-700',
                    'folder_id' => $folder->id,
                    'semester_id' => $semester->id,
                    'course_id' => $course->id,
                    'course_code' => $course->course_code
                ];
            }
        }
    }

    /**
     * Check if a folder OR ANY OF ITS SUB-FOLDERS has submissions for a specific course
     */
    private function folderOrSubfoldersHaveSubmissions($folderId, $userId, $courseId, $semesterId)
    {
        // First check if the current folder itself has submissions
        if ($this->folderHasDirectSubmissions($folderId, $userId, $courseId, $semesterId)) {
            return true;
        }

        // Then check all sub-folders recursively
        $subFolders = \App\Models\RequirementType::where('parent_id', $folderId)
            ->where('is_folder', true)
            ->get();

        foreach ($subFolders as $subFolder) {
            if ($this->folderOrSubfoldersHaveSubmissions($subFolder->id, $userId, $courseId, $semesterId)) {
                return true;
            }
        }

        return false;
    } 

    /**
     * Check if a specific folder has direct submissions (not including sub-folders)
     */
    private function folderHasDirectSubmissions($folderId, $userId, $courseId, $semesterId)
    {
        // Get requirements for this semester that are assigned to this specific folder
        $requirements = Requirement::where('semester_id', $semesterId)
            ->where(function($query) use ($folderId) {
                // Handle JSON array containing the folder ID
                $query->whereJsonContains('requirement_type_ids', $folderId)
                    ->orWhereJsonContains('requirement_type_ids', (string)$folderId);
            })
            ->get();

        // Check if any of these requirements have submissions from this user/course
        foreach ($requirements as $requirement) {
            $hasSubmissions = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->exists();
                
            if ($hasSubmissions) {
                return true;
            }
        }

        return false;
    }

    /**
     * Search sub-folders with course context for duplicates
     */
    private function searchSubFoldersWithContext($query, $userId, &$results)
    {
        // Get all courses assigned to the user
        $userCourses = CourseAssignment::where('professor_id', $userId)
            ->with(['course', 'course.program', 'semester'])
            ->get();

        foreach ($userCourses as $assignment) {
            $course = $assignment->course;
            $semester = $assignment->semester;
            
            // Get all sub-folders matching the search query (at any level)
            $subFolders = \App\Models\RequirementType::whereNotNull('parent_id')
                ->where('is_folder', true)
                ->where('name', 'like', "%{$query}%")
                ->with('parent')
                ->get()
                ->filter(function ($folder) use ($userId, $course, $semester) {
                    // Check if this sub-folder OR ANY OF ITS CHILD FOLDERS has submissions for the current course
                    return $this->folderOrSubfoldersHaveSubmissions($folder->id, $userId, $course->id, $semester->id);
                });

            foreach ($subFolders as $folder) {
                $parentName = $folder->parent ? $folder->parent->name : 'Unknown Parent';
                $programName = $course->program ? $course->program->program_name : 'No Program';
                $results[] = [
                    'type' => 'sub_folder',
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => 'Sub-folder in ' . $parentName,
                    'icon' => 'fa-folder',
                    'icon_color' => 'text-green-700',
                    'folder_id' => $folder->id,
                    'parent_folder_id' => $folder->parent_id,
                    'semester_id' => $semester->id,
                    'course_id' => $course->id,
                    'course_code' => $course->course_code
                ];
            }
        }
    }

    /**
     * Check if a folder has submissions for a specific course
     */
    private function folderHasSubmissionsForCourse($folderId, $userId, $courseId, $semesterId)
    {
        // Get requirements for this semester that are assigned to this folder
        $requirements = Requirement::where('semester_id', $semesterId)
            ->where(function($query) use ($folderId) {
                // Handle JSON array containing the folder ID
                $query->whereJsonContains('requirement_type_ids', $folderId)
                    ->orWhereJsonContains('requirement_type_ids', (string)$folderId);
            })
            ->get();

        // Check if any of these requirements have submissions from this user/course
        foreach ($requirements as $requirement) {
            $hasSubmissions = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->exists();
                
            if ($hasSubmissions) {
                return true;
            }
        }

        return false;
    }

    /**
     * Search files with course context for duplicates
     */
    private function searchFilesWithContext($query, $userId, &$results)
    {
        $files = SubmittedRequirement::where('user_id', $userId)
            ->whereHas('submissionFile', function($q) use ($query) {
                $q->where('file_name', 'like', "%{$query}%");
            })
            ->with(['submissionFile', 'requirement.semester', 'course', 'course.program'])
            ->get();

        foreach ($files as $file) {
            $fileName = $file->submissionFile->file_name ?? 'Untitled';
            $fileSize = $file->submissionFile->size ?? 0;
            $requirementName = $file->requirement->name ?? 'Unknown Requirement';
            $semesterId = $file->requirement->semester_id ?? null;
            $courseId = $file->course_id ?? null;
            $courseCode = $file->course->course_code ?? 'Unknown Course';
            $programName = $file->course->program->program_name ?? 'No Program';
            
            $results[] = [
                'type' => 'file',
                'id' => $file->id,
                'name' => $fileName,
                'description' => 'In ' . $requirementName,
                'icon' => $this->getFileIcon($fileName),
                'icon_color' => $this->getFileIconColor($fileName),
                'requirement_id' => $file->requirement_id,
                'semester_id' => $semesterId,
                'course_id' => $courseId,
                'course_code' => $courseCode
            ];
        }
    }


    public function selectSearchResult($result)
    {
        $this->showSearchResults = false;
        $this->searchQuery = '';
        
        try {
            switch ($result['type']) {
                case 'semester':
                    $this->handleNavigation('courses', $result['id']);
                    break;
                    
                case 'course':
                    $this->handleNavigation('parent_folders', $result['id']);
                    break;
                    
                case 'parent_folder':
                    // Use the course context from search result
                    if (isset($result['semester_id']) && isset($result['course_id'])) {
                        $this->handleNavigation('courses', $result['semester_id']);
                        $this->handleNavigation('parent_folders', $result['course_id']);
                        $this->handleNavigation('parent_folder_contents', $result['folder_id']);
                    } else {
                        // Fallback to old method if no context
                        $context = $this->findContextForFolder($result['id']);
                        if ($context) {
                            $this->handleNavigation('courses', $context['semester_id']);
                            $this->handleNavigation('parent_folders', $context['course_id']);
                            $this->handleNavigation('parent_folder_contents', $result['id']);
                        }
                    }
                    break;
                    
                case 'sub_folder':
                    // Use the course context from search result
                    if (isset($result['semester_id']) && isset($result['course_id'])) {
                        $this->handleNavigation('courses', $result['semester_id']);
                        $this->handleNavigation('parent_folders', $result['course_id']);
                        $this->handleNavigation('parent_folder_contents', $result['parent_folder_id']);
                        $this->handleNavigation('parent_folder_contents', $result['folder_id']);
                    } else {
                        // Fallback to old method if no context
                        $context = $this->findContextForFolder($result['parent_folder_id']);
                        if ($context) {
                            $this->handleNavigation('courses', $context['semester_id']);
                            $this->handleNavigation('parent_folders', $context['course_id']);
                            $this->handleNavigation('parent_folder_contents', $result['parent_folder_id']);
                            $this->handleNavigation('parent_folder_contents', $result['id']);
                        }
                    }
                    break;
                    
                case 'file':
                    $semesterId = $result['semester_id'];
                    $courseId = $result['course_id'];
                    $fileId = $result['id'];
                    
                    if ($semesterId && $courseId) {
                        $this->handleNavigation('courses', $semesterId);
                        $this->handleNavigation('parent_folders', $courseId);
                        
                        // Find the folder that contains this file
                        $folderContext = $this->findFolderForFile($fileId);
                        if ($folderContext) {
                            $this->handleNavigation('parent_folder_contents', $folderContext['folder_id']);
                            $this->dispatch('navigateToFileAfterSearch', fileId: $fileId);
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            \Log::error('Search navigation error: ' . $e->getMessage());
            $this->handleNavigation('semesters');
            session()->flash('error', 'Error navigating to search result.');
        }
    }

    /**
     * Find a suitable context (semester and course) for a folder
     */
    private function findContextForFolder($folderId)
    {
        $userId = Auth::id();
        
        // Try to find a course where this folder is used
        $courseAssignment = CourseAssignment::where('professor_id', $userId)
            ->with(['course', 'semester'])
            ->first();
            
        if ($courseAssignment) {
            return [
                'semester_id' => $courseAssignment->semester_id,
                'course_id' => $courseAssignment->course_id
            ];
        }
        
        return null;
    }

    /**
     * Find the folder that contains a specific file
     */
    private function findFolderForFile($fileId)
    {
        $submission = SubmittedRequirement::with('requirement')->find($fileId);
        
        if (!$submission || !$submission->requirement) {
            return null;
        }

        $requirementTypeIds = $submission->requirement->requirement_type_ids ?? [];
        
        if (empty($requirementTypeIds)) {
            return null;
        }

        // Convert to array if it's a JSON string
        if (is_string($requirementTypeIds)) {
            $requirementTypeIds = json_decode($requirementTypeIds, true);
        }

        // Return the first folder ID
        return !empty($requirementTypeIds) ? ['folder_id' => $requirementTypeIds[0]] : null;
    }

    public function handleNavigateAfterSearch($level, $id)
    {
        $this->handleNavigation($level, $id);
    }

    public function handleNavigateToFileAfterSearch($fileId)
    {
        usleep(300000);
        $this->selectFile($fileId);
    }

    public function ensureNavigationData()
    {
        if ($this->currentLevel === 'courses' && $this->currentSemester) {
            $this->loadAssignedCourses();
        }
        
        if (($this->currentLevel === 'parent_folders' || $this->currentLevel === 'parent_folder_contents') && !$this->currentSemester) {
            $this->currentSemester = $this->activeSemester;
            $this->selectedSemesterId = $this->currentSemester->id;
        }
        
        // Reload parent folder contents if needed
        if ($this->currentLevel === 'parent_folder_contents' && ($this->currentParentFolder || $this->currentSubFolder)) {
            $folderId = $this->currentSubFolder ? $this->currentSubFolder->id : $this->currentParentFolder->id;
            $this->loadParentFolderContents($folderId);
        }
    }
}