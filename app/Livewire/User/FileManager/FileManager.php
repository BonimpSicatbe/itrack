<?php

namespace App\Livewire\User\FileManager;

use Livewire\Component;
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
    public $currentLevel = 'semesters'; // semesters, courses, requirements, files, folder_requirements
    public $currentSemester = null;
    public $currentCourse = null;
    public $currentRequirement = null;
    public $currentFolder = null;
    
    // File selection properties
    public $selectedFile = null;
    
    // Semester properties
    public $activeSemester = null;
    public $selectedSemesterId = null;
    public $allSemesters = [];
    public $semesterMessage = null;
    public $daysRemaining;
    public $semesterProgress;

    // Course properties
    public $assignedCourses = [];
    
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

    public function mount()
    {
        $this->refreshSemesterData();
        $this->loadAllSemesters();
        
        // Retrieve view mode preference from session if exists
        $this->viewMode = session()->get('fileManagerViewMode', 'grid');
        
        // Set default selected semester to active semester
        if ($this->activeSemester) {
            $this->selectedSemesterId = $this->activeSemester->id;
            $this->currentSemester = $this->activeSemester;
            $this->loadAssignedCourses();
            $this->initializeBreadcrumb();
        }
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
        
        if ($this->currentLevel === 'requirements' && $this->currentCourse) {
            $this->breadcrumb[] = [
                'name' => $this->currentSemester->name, 
                'level' => 'courses', 
                'id' => $this->currentSemester->id
            ];
            $this->breadcrumb[] = [
                'name' => $this->currentCourse->course_code, 
                'level' => 'requirements', 
                'id' => $this->currentCourse->id
            ];
        }
        
        if ($this->currentLevel === 'files' && $this->currentRequirement) {
            $this->breadcrumb[] = [
                'name' => $this->currentSemester->name, 
                'level' => 'courses', 
                'id' => $this->currentSemester->id
            ];
            $this->breadcrumb[] = [
                'name' => $this->currentCourse->course_code, 
                'level' => 'requirements', 
                'id' => $this->currentCourse->id
            ];
            $this->breadcrumb[] = [
                'name' => $this->currentRequirement->name, 
                'level' => 'files', 
                'id' => $this->currentRequirement->id
            ];
        }
        
        if ($this->currentLevel === 'folder_requirements' && $this->currentFolder) {
            $this->breadcrumb[] = [
                'name' => $this->currentSemester->name, 
                'level' => 'courses', 
                'id' => $this->currentSemester->id
            ];
            $this->breadcrumb[] = [
                'name' => $this->currentCourse->course_code, 
                'level' => 'requirements', 
                'id' => $this->currentCourse->id
            ];
            $this->breadcrumb[] = [
                'name' => $this->currentFolder->name, 
                'level' => 'folder_requirements', 
                'id' => $this->currentFolder->id
            ];
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
            ->with('course')
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
        $this->statusFilter = ''; // Reset the status filter
        if ($this->isNavigating) return;

        $this->isNavigating = true; 

        try {
            $this->currentLevel = $level;
            
            if ($level === 'semesters') {
                $this->currentSemester = null;
                $this->currentCourse = null;
                $this->currentRequirement = null;
                $this->currentFolder = null;
                $this->selectedSemesterId = null;
                $this->assignedCourses = [];
                $this->deselectFile();
                $this->breadcrumb = [['name' => 'File Manager', 'level' => 'semesters', 'id' => null]];
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
                $this->currentRequirement = null;
                $this->currentFolder = null;
                $this->deselectFile();
                
                // Update breadcrumb
                $this->breadcrumb = [
                    ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                    ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $id]
                ];
            } elseif ($level === 'requirements' && $id) {
                // Load course and its requirements
                $this->currentCourse = Course::find($id);
                
                if (!$this->currentCourse) {
                    // Fall back to courses level if course doesn't exist
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
                
                $this->currentRequirement = null;
                $this->currentFolder = null;
                $this->deselectFile();
                
                // Update breadcrumb
                $this->breadcrumb = [
                    ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                    ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $this->currentSemester->id],
                    ['name' => $this->currentCourse->course_code, 'level' => 'requirements', 'id' => $id]
                ];
            } elseif ($level === 'files' && $id) {
                // Check if this is a folder (requirement type) or a regular requirement
                $requirementType = \App\Models\RequirementType::find($id);
                
                if ($requirementType && $requirementType->is_folder) {
                    // Handle folder navigation - show requirements within this folder
                    $this->currentFolder = $requirementType;
                    $this->currentLevel = 'folder_requirements';
                    
                    // Update breadcrumb for folder
                    $this->breadcrumb = [
                        ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                        ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $this->currentSemester->id],
                        ['name' => $this->currentCourse->course_code, 'level' => 'requirements', 'id' => $this->currentCourse->id],
                        ['name' => $requirementType->name, 'level' => 'folder_requirements', 'id' => $id]
                    ];
                } else {
                    // Load requirement with user's submissions for this specific course
                    $this->currentRequirement = Requirement::with([
                        'submittedRequirements' => function($query) {
                            $query->where('user_id', Auth::id())
                                ->where('course_id', $this->currentCourse->id) // Filter by current course
                                ->with(['submissionFile', 'user', 'requirement.semester']);
                        }
                    ])->find($id);
                    
                    if (!$this->currentRequirement) {
                        // Fall back to requirements level if requirement doesn't exist
                        if ($this->currentCourse) {
                            $this->currentLevel = 'requirements';
                        } else {
                            $this->currentLevel = 'semesters';
                        }
                        return;
                    }
                    
                    // Make sure we have the current semester and course set
                    if (!$this->currentSemester && $this->currentRequirement->semester) {
                        $this->currentSemester = $this->currentRequirement->semester;
                        $this->selectedSemesterId = $this->currentSemester->id;
                    }
                    
                    if (!$this->currentCourse) {
                        // Try to get the course from the first submission or fall back
                        $firstSubmission = $this->currentRequirement->submittedRequirements->first();
                        if ($firstSubmission) {
                            $this->currentCourse = Course::find($firstSubmission->course_id);
                        }
                    }
                    
                    // Find the parent folder for this requirement
                    $parentFolder = $this->findParentFolderForRequirement($this->currentRequirement);
                    
                    if ($parentFolder) {
                        // This requirement belongs to a folder - preserve folder in breadcrumb
                        $this->breadcrumb = [
                            ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                            ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $this->currentSemester->id],
                            ['name' => $this->currentCourse->course_code, 'level' => 'requirements', 'id' => $this->currentCourse->id],
                            ['name' => $parentFolder->name, 'level' => 'folder_requirements', 'id' => $parentFolder->id],
                            ['name' => $this->currentRequirement->name, 'level' => 'files', 'id' => $id]
                        ];
                    } else {
                        // This is a standalone requirement
                        $this->breadcrumb = [
                            ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                            ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $this->currentSemester->id],
                            ['name' => $this->currentCourse->course_code, 'level' => 'requirements', 'id' => $this->currentCourse->id],
                            ['name' => $this->currentRequirement->name, 'level' => 'files', 'id' => $id]
                        ];
                    }
                }
            } elseif ($level === 'folder_requirements' && $id) {
                // Check if this is a folder navigation (from breadcrumb) or requirement within folder
                $requirementType = \App\Models\RequirementType::find($id);
                
                if ($requirementType && $requirementType->is_folder) {
                    // This is folder navigation from breadcrumb
                    $this->currentFolder = $requirementType;
                    $this->currentLevel = 'folder_requirements';
                    
                    // Update breadcrumb
                    $this->breadcrumb = [
                        ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                        ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $this->currentSemester->id],
                        ['name' => $this->currentCourse->course_code, 'level' => 'requirements', 'id' => $this->currentCourse->id],
                        ['name' => $requirementType->name, 'level' => 'folder_requirements', 'id' => $id]
                    ];
                } else {
                    // Load individual requirement within a folder
                    $this->currentRequirement = Requirement::with([
                        'submittedRequirements' => function($query) {
                            $query->where('user_id', Auth::id())
                                ->where('course_id', $this->currentCourse->id)
                                ->with(['submissionFile', 'user', 'requirement.semester']);
                        }
                    ])->find($id);
                    
                    if (!$this->currentRequirement) {
                        // Fall back to folder level if requirement doesn't exist
                        $this->currentLevel = 'folder_requirements';
                        return;
                    }
                    
                    $this->currentLevel = 'files';
                    
                    // Find the parent folder for this requirement
                    $parentFolder = $this->findParentFolderForRequirement($this->currentRequirement);
                    
                    if ($parentFolder) {
                        // Update breadcrumb - preserve the folder context
                        $this->breadcrumb = [
                            ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                            ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $this->currentSemester->id],
                            ['name' => $this->currentCourse->course_code, 'level' => 'requirements', 'id' => $this->currentCourse->id],
                            ['name' => $parentFolder->name, 'level' => 'folder_requirements', 'id' => $parentFolder->id],
                            ['name' => $this->currentRequirement->name, 'level' => 'files', 'id' => $id]
                        ];
                    } else {
                        // Fallback if no parent folder found
                        $this->breadcrumb = [
                            ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                            ['name' => $this->currentSemester->name, 'level' => 'courses', 'id' => $this->currentSemester->id],
                            ['name' => $this->currentCourse->course_code, 'level' => 'requirements', 'id' => $this->currentCourse->id],
                            ['name' => $this->currentRequirement->name, 'level' => 'files', 'id' => $id]
                        ];
                    }
                }
            }
            
            $this->dispatch('levelChanged', $level, $id);
        } catch (\Exception $e) {
            \Log::error('Navigation error: ' . $e->getMessage());
            $this->currentLevel = 'semesters';
            $this->currentSemester = null;
            $this->currentCourse = null;
            $this->currentRequirement = null;
            $this->currentFolder = null;
            $this->breadcrumb = [['name' => 'File Manager', 'level' => 'semesters', 'id' => null]];
        } finally {
            $this->isNavigating = false;
        }
    }

    private function findParentFolderForRequirement($requirement)
    {
        if (empty($requirement->requirement_type_ids)) {
            return null;
        }
        
        $requirementTypeIds = $requirement->requirement_type_ids;
        
        // Get all requirement types associated with this requirement
        $requirementTypes = \App\Models\RequirementType::whereIn('id', $requirementTypeIds)->get();
        
        foreach ($requirementTypes as $type) {
            // If this type is a folder, return it
            if ($type->is_folder) {
                return $type;
            }
            
            // If this type has a parent that is a folder, return the parent
            if ($type->parent_id) {
                $parent = \App\Models\RequirementType::find($type->parent_id);
                if ($parent && $parent->is_folder) {
                    return $parent;
                }
            }
        }
        
        return null;
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

            // The file has been selected, no need to dispatch an event back to self.
            // Dispatch a general event if other components need to know.
            // For example:
            $this->dispatch('fileSelected'); // Dispatch a generic 'file selected' event

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
        // Get the extension
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Use the FILE_ICONS constant from SubmittedRequirement model
        $icons = SubmittedRequirement::FILE_ICONS;
        
        return $icons[$extension]['icon'] ?? $icons['default']['icon'];
    }

    public function getFileIconColor($filename)
    {
        // Get the extension
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Use the FILE_ICONS constant from SubmittedRequirement model
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

    // Missing methods added here
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

    // Delete methods (if not already present)
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
    $currentRequirement = $this->currentRequirement;
    $currentFolder = $this->currentFolder;
    $folderRequirements = [];

    // Load requirements for current course if at requirements level
    if ($this->currentLevel === 'requirements' && $currentCourse && $currentSemester) {
        $user = Auth::user();
        
        // Get all requirements for the current semester
        $allRequirements = Requirement::where('semester_id', $currentSemester->id)
            ->with(['userSubmissions' => function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('course_id', $this->currentCourse->id)
                      ->with('submissionFile')
                      ->orderBy('submitted_at', 'desc');
            }])
            ->with('guides')
            ->orderBy('due', 'asc')
            ->get();

        // Filter requirements where user's college AND department are both present in assigned_to
        $courseRequirements = $allRequirements->filter(function ($requirement) use ($user) {
            return $this->isUserAssignedToRequirement($requirement, $user);
        })
        ->map(function ($requirement) use ($user) {
            $userSubmitted = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->where('user_id', $user->id)
                ->where('course_id', $this->currentCourse->id)
                ->exists();
            
            $requirement->user_has_submitted = $userSubmitted;
            
            return $requirement;
        });

        // Organize requirements by folders and standalone requirements
        $organizedRequirements = $this->organizeRequirementsByFolders($courseRequirements);
        
        // Add organized requirements to current course for the view
        $currentCourse->organizedRequirements = $organizedRequirements;
    } else {
        // Initialize empty organizedRequirements if not set
        if ($currentCourse && !isset($currentCourse->organizedRequirements)) {
            $currentCourse->organizedRequirements = [
                'folders' => [],
                'standalone' => []
            ];
        }
    }

        // Load folder requirements if at folder_requirements level
        if ($this->currentLevel === 'folder_requirements' && $currentCourse && $currentSemester && $currentFolder) {
            $folderRequirements = $this->getFolderRequirements($currentFolder->id, $currentCourse->id);
        }

        // Filter files by course if at files level
        if ($this->currentLevel === 'files' && $currentRequirement && $currentCourse) {
            $submittedRequirementsQuery = SubmittedRequirement::query()
                ->where('requirement_id', $currentRequirement->id)
                ->where('user_id', Auth::id())
                ->where('course_id', $currentCourse->id) // Filter by current course
                ->with(['submissionFile', 'user', 'requirement.semester']);

            // Apply status filter if it's set
            if (!empty($this->statusFilter)) {
                $submittedRequirementsQuery->where('status', $this->statusFilter);
            }

            // Replace the collection on the currentRequirement object
            $currentRequirement->setRelation('submittedRequirements', $submittedRequirementsQuery->get());
        }
        
        return view('livewire.user.file-manager.file-manager', [
            'totalFiles' => $this->getTotalFiles(),
            'totalSize' => $this->getTotalSize(),
            'statuses' => SubmittedRequirement::statuses(),
            'allSemesters' => $allSemesters,
            'currentSemester' => $currentSemester,
            'currentCourse' => $currentCourse,
            'currentRequirement' => $currentRequirement,
            'currentFolder' => $currentFolder,
            'folderRequirements' => $folderRequirements,
            'assignedCourses' => $this->assignedCourses,
            'archiveRoute' => route('user.archive'),
        ]);
    }

    /**
     * Get requirements for a specific folder
     */
    private function getFolderRequirements($folderId, $courseId)
    {
        $user = Auth::user();
        
        // Get all requirements for the current semester
        $allRequirements = Requirement::where('semester_id', $this->currentSemester->id)
            ->with(['userSubmissions' => function($query) use ($user, $courseId) {
                $query->where('user_id', $user->id)
                    ->where('course_id', $courseId)
                    ->with('submissionFile')
                    ->orderBy('submitted_at', 'desc');
            }])
            ->with('guides')
            ->orderBy('due', 'asc')
            ->get();

        // Filter requirements that belong to this folder using the same parent finding logic
        $folderRequirements = $allRequirements->filter(function ($requirement) use ($user, $folderId) {
            if (!$this->isUserAssignedToRequirement($requirement, $user)) {
                return false;
            }
            
            // Use the same logic to find parent folder
            $parentFolder = $this->findParentFolderForRequirement($requirement);
            
            // Check if requirement belongs to the specified folder
            return $parentFolder && $parentFolder->id == $folderId;
        })
        ->map(function ($requirement) use ($user, $courseId) {
            $userSubmitted = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->exists();
            
            $requirement->user_has_submitted = $userSubmitted;
            
            return $requirement;
        });

        return $folderRequirements;
    }

    /**
     * Check if user is assigned to a requirement based on college AND department
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

        $colleges = $assignedTo['colleges'] ?? [];
        $departments = $assignedTo['departments'] ?? [];
        $selectAllColleges = $assignedTo['selectAllColleges'] ?? false;
        $selectAllDepartments = $assignedTo['selectAllDepartments'] ?? false;

        // Check if user has college and department
        if (!$user->college_id || !$user->department_id) {
            return false;
        }

        // Convert user IDs to string for comparison
        $userCollegeId = (string)$user->college_id;
        $userDepartmentId = (string)$user->department_id;

        // Check college assignment
        $collegeAssigned = $selectAllColleges || 
                          (is_array($colleges) && in_array($userCollegeId, $colleges));

        // Check department assignment
        $departmentAssigned = $selectAllDepartments ||
                            (is_array($departments) && in_array($userDepartmentId, $departments));

        return $collegeAssigned && $departmentAssigned;
    }

    /**
     * Organize requirements into folder structure
     */
    private function organizeRequirementsByFolders($requirements)
    {
        $organized = [
            'folders' => [],
            'standalone' => []
        ];

        foreach ($requirements as $requirement) {
            $hasFolderAssignment = false;
            
            // Check if requirement has requirement_type_ids
            if (!empty($requirement->requirement_type_ids)) {
                $requirementTypes = \App\Models\RequirementType::whereIn('id', $requirement->requirement_type_ids)->get();
                
                foreach ($requirementTypes as $type) {
                    if ($type->is_folder) {
                        // This requirement is directly assigned to a folder
                        if (!isset($organized['folders'][$type->id])) {
                            $organized['folders'][$type->id] = [
                                'folder' => $type,
                                'requirements' => []
                            ];
                        }
                        $organized['folders'][$type->id]['requirements'][] = $requirement;
                        $hasFolderAssignment = true;
                    } else if ($type->parent_id) {
                        // This requirement type has a parent (folder)
                        $parentFolder = \App\Models\RequirementType::find($type->parent_id);
                        if ($parentFolder && $parentFolder->is_folder) {
                            if (!isset($organized['folders'][$parentFolder->id])) {
                                $organized['folders'][$parentFolder->id] = [
                                    'folder' => $parentFolder,
                                    'requirements' => []
                                ];
                            }
                            $organized['folders'][$parentFolder->id]['requirements'][] = $requirement;
                            $hasFolderAssignment = true;
                        }
                    }
                }
            }
            
            // If requirement wasn't assigned to any folder, put it in standalone
            if (!$hasFolderAssignment) {
                $organized['standalone'][] = $requirement;
            }
        }

        return $organized;
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
        
        // Store the view preference in session for persistence
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
        
        // Search semesters
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
        
        // Search courses assigned to the user - FIXED: Use correct relationship
        $courses = Course::whereHas('assignments', function($q) use ($userId) {
                $q->where('professor_id', $userId);
            })
            ->where(function($q) use ($query) {
                $q->where('course_code', 'like', "%{$query}%")
                ->orWhere('course_name', 'like', "%{$query}%");
            })
            ->get();
        
        foreach ($courses as $course) {
            $results[] = [
                'type' => 'course',
                'id' => $course->id,
                'name' => $course->course_code,
                'description' => $course->course_name,
                'icon' => 'fa-folder',
                'icon_color' => 'text-green-700',
                'course_id' => $course->id
            ];
        }
        
        // Search requirements - MODIFIED: Show separate results for each course and exclude folders
        $requirements = Requirement::where('name', 'like', "%{$query}%")
            ->with('semester')
            ->get();

        foreach ($requirements as $requirement) {
            // Skip folders in search results
            $requirementTypeIds = $requirement->requirement_type_ids ?? [];
            $isFolder = false;
            
            if (!empty($requirementTypeIds)) {
                $requirementTypes = \App\Models\RequirementType::whereIn('id', $requirementTypeIds)->get();
                foreach ($requirementTypes as $type) {
                    if ($type->is_folder) {
                        $isFolder = true;
                        break;
                    }
                }
            }
            
            if ($isFolder) {
                continue; // Skip folders
            }
            
            // Get all courses where this requirement appears for the user
            $userCourses = $this->getCoursesForRequirement($requirement, $userId);
            
            foreach ($userCourses as $course) {
                $results[] = [
                    'type' => 'requirement',
                    'id' => $requirement->id,
                    'name' => $requirement->name,
                    'description' => 'In ' . $course->course_code . ' • ' . ($requirement->semester->name ?? 'Unknown Semester'),
                    'icon' => 'fa-folder',
                    'icon_color' => 'text-green-700',
                    'semester_id' => $requirement->semester_id,
                    'requirement_id' => $requirement->id,
                    'course_id' => $course->id,
                    'course_code' => $course->course_code
                ];
            }
        }
        
        // Search files
        $files = SubmittedRequirement::where('user_id', $userId)
            ->whereHas('submissionFile', function($q) use ($query) {
                $q->where('file_name', 'like', "%{$query}%");
            })
            ->with(['submissionFile', 'requirement.semester', 'course'])
            ->get();
        
        foreach ($files as $file) {
            // Safely access nested relationships with null coalescing
            $fileName = $file->submissionFile->file_name ?? 'Untitled';
            $fileSize = $file->submissionFile->size ?? 0;
            $requirementName = $file->requirement->name ?? 'Unknown Requirement';
            $semesterId = $file->requirement->semester_id ?? null;
            $requirementId = $file->requirement_id ?? null;
            $courseId = $file->course_id ?? null;
            $courseCode = $file->course->course_code ?? 'Unknown Course';
            
            $results[] = [
                'type' => 'file',
                'id' => $file->id,
                'name' => $fileName,
                'description' => 'In ' . $requirementName . ' • ' . $courseCode . ' • ' . $this->formatFileSize($fileSize),
                'icon' => $this->getFileIcon($fileName),
                'icon_color' => $this->getFileIconColor($fileName),
                'requirement_id' => $requirementId,
                'semester_id' => $semesterId,
                'course_id' => $courseId,
                'course_code' => $courseCode
            ];
        }
        
        return $results;
    }

    /**
     * Get all courses where a requirement appears for a user
     */
    private function getCoursesForRequirement($requirement, $userId)
    {
        $user = Auth::user();
        
        // Get all semesters where this requirement exists
        $semesters = Semester::where('id', $requirement->semester_id)->get();
        
        $userCourses = collect();
        
        foreach ($semesters as $semester) {
            // Get user's assigned courses for this semester
            $assignedCourses = CourseAssignment::where('professor_id', $userId)
                ->where('semester_id', $semester->id)
                ->with('course')
                ->get()
                ->pluck('course')
                ->unique('id')
                ->values();
            
            // Filter courses where the requirement is assigned to the user
            foreach ($assignedCourses as $course) {
                if ($this->isUserAssignedToRequirement($requirement, $user)) {
                    $userCourses->push($course);
                }
            }
        }
        
        return $userCourses->unique('id')->values();
    }

    public function selectSearchResult($result)
    {
        $this->showSearchResults = false;
        $this->searchQuery = '';
        
        try {
            switch ($result['type']) {
                case 'semester':
                    // Navigate to courses level for this semester
                    $this->handleNavigation('courses', $result['id']);
                    break;
                    
                case 'course':
                    // Navigate to requirements level for this course
                    $this->handleNavigation('requirements', $result['id']);
                    break;
                    
                case 'requirement':
                    // For requirements, navigate to the specific course context
                    $semesterId = $result['semester_id'];
                    $courseId = $result['course_id'];
                    $requirementId = $result['id'];
                    
                    if ($semesterId && $courseId) {
                        // Navigate through the hierarchy: semesters -> courses -> requirements -> files
                        $this->handleNavigation('courses', $semesterId);
                        $this->handleNavigation('requirements', $courseId);
                        $this->handleNavigation('files', $requirementId);
                    } else {
                        // Fallback: try to find a context for this requirement
                        $context = $this->findContextForRequirement($requirementId);
                        if ($context) {
                            $this->handleNavigation('courses', $context['semester_id']);
                            $this->handleNavigation('requirements', $context['course_id']);
                            $this->handleNavigation('files', $requirementId);
                        } else {
                            $this->handleNavigation('semesters');
                            session()->flash('error', 'Requirement not found in any of your assigned courses.');
                        }
                    }
                    break;
                    
                case 'file':
                    // For files, we need to navigate to the requirement and select the file
                    $requirementId = $result['requirement_id'];
                    $semesterId = $result['semester_id'];
                    $courseId = $result['course_id'];
                    $fileId = $result['id'];
                    
                    if ($semesterId && $courseId && $requirementId) {
                        // Navigate through the hierarchy and select the file
                        $this->handleNavigation('courses', $semesterId);
                        $this->handleNavigation('requirements', $courseId);
                        $this->handleNavigation('files', $requirementId);
                        
                        // Use a small delay to ensure navigation completes before selecting file
                        $this->dispatch('navigateToFileAfterSearch', fileId: $fileId);
                    } else {
                        // Fallback: try to find context from the file itself
                        $file = SubmittedRequirement::with(['requirement', 'course'])->find($fileId);
                        if ($file && $file->requirement) {
                            $this->handleNavigation('courses', $file->requirement->semester_id);
                            $this->handleNavigation('requirements', $file->course_id);
                            $this->handleNavigation('files', $file->requirement_id);
                            $this->dispatch('navigateToFileAfterSearch', fileId: $fileId);
                        } else {
                            $this->handleNavigation('semesters');
                            session()->flash('error', 'File context not found.');
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
     * Find a suitable context (semester and course) for a requirement
     */
    private function findContextForRequirement($requirementId)
    {
        $userId = Auth::id();
        
        // Check if user has a submission for this requirement
        $submission = SubmittedRequirement::where('requirement_id', $requirementId)
            ->where('user_id', $userId)
            ->first();
            
        if ($submission) {
            return [
                'semester_id' => $submission->requirement->semester_id ?? null,
                'course_id' => $submission->course_id
            ];
        }
        
        // If no submission, check if user is assigned to this requirement via course assignment
        $requirement = Requirement::find($requirementId);
        if ($requirement) {
            $courseAssignment = CourseAssignment::where('professor_id', $userId)
                ->where('semester_id', $requirement->semester_id)
                ->first();
                
            if ($courseAssignment) {
                return [
                    'semester_id' => $requirement->semester_id,
                    'course_id' => $courseAssignment->course_id
                ];
            }
        }
        
        return null;
    }

    public function handleNavigateAfterSearch($level, $id)
    {
        $this->handleNavigation($level, $id);
    }

    public function handleNavigateToFileAfterSearch($fileId)
    {
        // Small delay to ensure navigation is complete
        usleep(300000); // 300ms delay
        
        $this->selectFile($fileId);
    }

    public function ensureNavigationData()
    {
        // Ensure we have the necessary data for current navigation level
        if ($this->currentLevel === 'courses' && $this->currentSemester) {
            $this->loadAssignedCourses();
        }
        
        if (($this->currentLevel === 'requirements' || $this->currentLevel === 'files') && !$this->currentSemester) {
            // Fallback to active semester if current semester is not set
            $this->currentSemester = $this->activeSemester;
            $this->selectedSemesterId = $this->currentSemester->id;
        }
    }
}