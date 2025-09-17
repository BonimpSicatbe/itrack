<?php

namespace App\Livewire\User\FileManager;

use Livewire\Component;
use App\Models\SubmittedRequirement;
use App\Models\Semester;
use App\Models\Requirement;
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
    public $currentLevel = 'semesters'; // semesters, requirements, files
    public $currentSemester = null;
    public $currentRequirement = null;
    
    // File selection properties
    public $selectedFile = null;
    
    // Semester properties
    public $activeSemester = null;
    public $selectedSemesterId = null;
    public $allSemesters = [];
    public $semesterMessage = null;
    public $daysRemaining;
    public $semesterProgress;

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
            $this->initializeBreadcrumb();
        }
    }


    public function initializeBreadcrumb()
    {
        $this->breadcrumb = [
            ['name' => 'File Manager', 'level' => 'semesters', 'id' => null]
        ];
        
        if ($this->currentLevel === 'requirements' && $this->currentSemester) {
            $this->breadcrumb[] = [
                'name' => $this->currentSemester->name, 
                'level' => 'requirements', 
                'id' => $this->currentSemester->id
            ];
        }
        
        if ($this->currentLevel === 'files' && $this->currentRequirement) {
            $this->breadcrumb[] = [
                'name' => $this->currentSemester->name, 
                'level' => 'requirements', 
                'id' => $this->currentSemester->id
            ];
            $this->breadcrumb[] = [
                'name' => $this->currentRequirement->name, 
                'level' => 'files', 
                'id' => $this->currentRequirement->id
            ];
        }
    }

    public function loadAllSemesters()
    {
        $this->allSemesters = Semester::orderBy('start_date', 'desc')->get();
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
                $this->currentRequirement = null;
                $this->selectedSemesterId = null;
                $this->deselectFile();
                $this->breadcrumb = [['name' => 'File Manager', 'level' => 'semesters', 'id' => null]];
            } elseif ($level === 'requirements' && $id) {
                // Load semester with requirements and their submission counts
                $this->currentSemester = Semester::with(['requirements' => function($query) {
                    $query->withCount(['submittedRequirements' => function($query) {
                        $query->where('user_id', Auth::id());
                    }]);
                }])->find($id);
                
                if (!$this->currentSemester) {
                    $this->currentLevel = 'semesters';
                    return;
                }
                
                $this->selectedSemesterId = $id;
                $this->currentRequirement = null;
                $this->deselectFile();
                
                // Update breadcrumb
                $this->breadcrumb = [
                    ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                    ['name' => $this->currentSemester->name, 'level' => 'requirements', 'id' => $id]
                ];
            } elseif ($level === 'files' && $id) {
                // Load requirement with user's submissions and related data
                $this->currentRequirement = Requirement::with([
                    'submittedRequirements' => function($query) {
                        $query->where('user_id', Auth::id())
                            ->with(['submissionFile', 'user', 'requirement.semester']);
                    }
                ])->find($id);
                
                if (!$this->currentRequirement) {
                    // Fall back to requirements level if requirement doesn't exist
                    if ($this->currentSemester) {
                        $this->currentLevel = 'requirements';
                    } else {
                        $this->currentLevel = 'semesters';
                    }
                    return;
                }
                
                // Make sure we have the current semester set
                if (!$this->currentSemester && $this->currentRequirement->semester) {
                    $this->currentSemester = $this->currentRequirement->semester;
                    $this->selectedSemesterId = $this->currentSemester->id;
                }
                
                // Update breadcrumb
                $this->breadcrumb = [
                    ['name' => 'File Manager', 'level' => 'semesters', 'id' => null],
                    ['name' => $this->currentSemester->name, 'level' => 'requirements', 'id' => $this->currentSemester->id],
                    ['name' => $this->currentRequirement->name, 'level' => 'files', 'id' => $id]
                ];
            }
            
            $this->dispatch('levelChanged', $level, $id);
        } catch (\Exception $e) {
            \Log::error('Navigation error: ' . $e->getMessage());
            $this->currentLevel = 'semesters';
            $this->currentSemester = null;
            $this->currentRequirement = null;
            $this->breadcrumb = [['name' => 'File Manager', 'level' => 'semesters', 'id' => null]];
        } finally {
            $this->isNavigating = false;
        }
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

    public function render()
    {
        $this->ensureNavigationData();

        $allSemesters = $this->allSemesters;
        $currentSemester = $this->currentSemester;
        $currentRequirement = $this->currentRequirement;

        if ($this->currentLevel === 'files' && $currentRequirement) {
            $submittedRequirementsQuery = SubmittedRequirement::query()
                ->where('requirement_id', $currentRequirement->id)
                ->where('user_id', Auth::id())
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
            'currentRequirement' => $currentRequirement,
            'archiveRoute' => route('user.archive'),
        ]);
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
        
        // Search requirements
        $requirements = Requirement::where('name', 'like', "%{$query}%")
            ->with('semester')
            ->get();

        foreach ($requirements as $requirement) {
            $results[] = [
                'type' => 'requirement',
                'id' => $requirement->id,
                'name' => $requirement->name,
                'description' => 'In ' . ($requirement->semester->name ?? 'Unknown Semester'),
                'icon' => 'fa-folder',
                'icon_color' => 'text-green-700',
                'semester_id' => $requirement->semester_id, 
                'requirement_id' => $requirement->id
            ];
        }
        
        // Search files
        $files = SubmittedRequirement::where('user_id', $userId)
            ->whereHas('submissionFile', function($q) use ($query) {
                $q->where('file_name', 'like', "%{$query}%");
            })
            ->with(['submissionFile', 'requirement.semester'])
            ->get();
        
        foreach ($files as $file) {
            // Safely access nested relationships with null coalescing
            $fileName = $file->submissionFile->file_name ?? 'Untitled';
            $fileSize = $file->submissionFile->size ?? 0;
            $requirementName = $file->requirement->name ?? 'Unknown Requirement';
            $semesterId = $file->requirement->semester_id ?? null;
            $requirementId = $file->requirement_id ?? null;
            
            $results[] = [
                'type' => 'file',
                'id' => $file->id,
                'name' => $fileName,
                'description' => 'In ' . $requirementName . ' • ' . $this->formatFileSize($fileSize),
                'icon' => $this->getFileIcon($fileName),
                'icon_color' => $this->getFileIconColor($fileName),
                'requirement_id' => $requirementId, // Make sure this is included
                'semester_id' => $semesterId
            ];
        }
        
        return $results;
    }

    public function selectSearchResult($type, $id, $semesterId = null, $requirementId = null)
    {
        if ($this->isNavigating) return;
        
        $this->showSearchResults = false;
        $this->searchQuery = '';
        
        if ($type === 'semester') {
            $this->handleNavigation('requirements', $id);
        } elseif ($type === 'requirement' && $semesterId) {
            // First navigate to the requirements level of the correct semester
            $this->handleNavigation('requirements', $semesterId);
            
            // Then navigate to the files level for this requirement
            $this->dispatch('navigateAfterSearch', requirementId: $id);
        } elseif ($type === 'file' && $requirementId && $semesterId) {
            // First navigate to the requirements level of the correct semester
            $this->handleNavigation('requirements', $semesterId);
            
            // Then navigate to files level and select the file
            $this->dispatch('navigateAfterSearch', requirementId: $requirementId, fileId: $id);
        }
    }

    public function navigateToFilesAfterSearch($requirementId)
    {
        // Small delay to ensure the requirements level is loaded first
        usleep(300000); // 300ms delay
        $this->handleNavigation('files', $requirementId);
    }

    public function navigateToFileAfterSearch($fileId)
    {
        // Small delay to ensure the navigation is complete
        usleep(300000); // 300ms delay
        
        // Select the file
        $this->selectFile($fileId);
        
        // Scroll to the selected file
        $this->dispatch('scrollToFile', $fileId);
    }

    public function closeSearchResults()
    {
        $this->showSearchResults = false;
    }

    public function ensureNavigationData()
    {
        // If we're at the files level but currentRequirement is null, try to load it
        if ($this->currentLevel === 'files' && !$this->currentRequirement && $this->currentSemester) {
            // Try to get the first requirement of the current semester
            $firstRequirement = $this->currentSemester->requirements->first();
            if ($firstRequirement) {
                $this->currentRequirement = $firstRequirement;
            } else {
                // Fall back to requirements level if no requirements exist
                $this->handleNavigation('requirements', $this->currentSemester->id);
            }
        }
        
        // If we're at the requirements level but currentSemester is null, try to load it
        if ($this->currentLevel === 'requirements' && !$this->currentSemester && $this->selectedSemesterId) {
            $this->currentSemester = Semester::find($this->selectedSemesterId);
            if (!$this->currentSemester) {
                // Fall back to semesters level if semester doesn't exist
                $this->handleNavigation('semesters');
            }
        }
    }

    public function handleNavigateAfterSearch($requirementId, $fileId = null)
    {
        // Small delay to ensure the requirements level is loaded first
        usleep(300000); // 300ms delay
        
        // Navigate to the files level
        $this->handleNavigation('files', $requirementId);
        
        // If a specific file was requested, select it
        if ($fileId) {
            $this->dispatch('navigateToFileAfterSearch', fileId: $fileId);
        }
    }

    public function handleNavigateToFileAfterSearch($fileId)
    {
        // Small delay to ensure navigation is complete
        usleep(300000); // 300ms delay
        
        // Select the file
        $this->selectFile($fileId);
        
        // Scroll to the selected file
        $this->dispatch('scrollToFile', $fileId);
    }

    public function clearFilters()
    {
        $this->reset(['searchQuery', 'statusFilter']);
    }

    public function confirmDelete($submissionId)
    {
        $this->fileToDelete = $submissionId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->fileToDelete = null;
    }

    public function deleteSubmission()
    {
        if (!$this->fileToDelete) {
            $this->showDeleteModal = false;
            return;
        }

        // Find the submission and ensure it belongs to the authenticated user
        $submission = SubmittedRequirement::where('id', $this->fileToDelete)
            ->where('user_id', Auth::id())
            ->with(['submissionFile', 'requirement.semester']) // Eager load semester
            ->first();

        if (!$submission) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'File not found or access denied.']);
            $this->showDeleteModal = false;
            return;
        }

        // Check if the associated semester is active.
        if ($submission->requirement->semester && !$submission->requirement->semester->is_active) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot delete files from an archived semester.']);
            $this->showDeleteModal = false;
            return;
        }

        // Check if a file is associated and has a valid path before attempting deletion
        if ($submission->submissionFile && $submission->submissionFile->path) {
            try {
                // Delete the physical file from storage
                Storage::disk('public')->delete($submission->submissionFile->path);

                // Delete the file record from the database
                $submission->submissionFile->delete();
            } catch (\Exception $e) {
                \Log::error('Error deleting file from storage: ' . $e->getMessage());
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to delete the file from storage.']);
            }
        } else {
            \Log::warning('Attempted to delete a submission without a valid file path. Submission ID: ' . $this->fileToDelete);
        }

        // Delete the submitted requirement record
        $submission->delete();

        // Close the modal and reset state
        $this->showDeleteModal = false;
        $this->fileToDelete = null;
        $this->selectedFile = null;
        $this->dispatch('refreshFiles');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'File deleted successfully.']);
    }
}