<?php

namespace App\Livewire\Admin\FileManager;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\User;
use App\Models\Course;
use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use App\Models\Semester;
use App\Models\RequirementSubmissionIndicator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FileManagerIndex extends Component
{
    use WithPagination;

    public $selectedFile = null;
    public $viewMode = 'grid';
    public $category = 'requirement';
    public $search = '';
    
    // Navigation state
    public $selectedRequirementId = null;
    public $selectedUserId = null;
    public $selectedCourseId = null;
    
    public $selectedSemester = null;
    public $showSemesterPanel = false;
    public $breadcrumbs = [];
    public $fileUrl = null;
    public $isImage = false;
    public $isPdf = false;
    public $isOfficeDoc = false;
    public $isPreviewable = false;

    // URL query parameters for breadcrumb navigation
    public $semesterId = null;
    public $requirementId = null;
    public $userId = null;
    public $courseId = null;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => 'requirement'],
        'selectedRequirementId' => ['except' => null],
        'selectedUserId' => ['except' => null],
        'selectedCourseId' => ['except' => null],
        'viewMode' => ['except' => 'grid'],
        'showSemesterPanel' => ['except' => false],
        'semesterId' => ['except' => null],
        'requirementId' => ['except' => null],
        'userId' => ['except' => null],
        'courseId' => ['except' => null],
    ];

    protected $listeners = [
        'semesterActivated' => '$refresh',
        'semesterSelected' => 'loadSemesterFiles',
        'semesterArchived' => 'handleSemesterArchived',
        'clearSelectedSemester' => 'clearSelectedSemester'
    ];

    public function togglePanel()
    {
        if ($this->selectedFile) {
            $this->selectedFile = null;
            $this->updateBreadcrumbs();
        }
        
        $this->showSemesterPanel = !$this->showSemesterPanel;
    }

    public function handleSemesterArchived()
    {
        $this->selectedSemester = null;
        $this->resetPage();
    }

    public function clearSelectedSemester()
    {
        $this->selectedSemester = null;
        $this->resetPage();
    }

    public function loadSemesterFiles($semesterId)
    {
        $this->selectedSemester = Semester::find($semesterId);
        $this->resetPage();
    }

    public function mount()
    {
        $this->viewMode = 'grid';
        $this->category = 'requirement';
        
        // Initialize from URL parameters if present
        $this->initializeFromUrl();
        $this->updateBreadcrumbs();
    }

    /**
     * Initialize component state from URL parameters
     */
    public function initializeFromUrl()
    {
        // Set semester if provided
        if ($this->semesterId) {
            $this->selectedSemester = Semester::find($this->semesterId);
        }

        // Set navigation state based on URL parameters
        if ($this->requirementId) {
            $this->selectedRequirementId = $this->requirementId;
        }
        if ($this->userId) {
            $this->selectedUserId = $this->userId;
        }
        if ($this->courseId) {
            $this->selectedCourseId = $this->courseId;
        }
    }

    /**
     * Update URL parameters based on current state
     */
    private function updateUrlParameters()
    {
        $this->semesterId = $this->selectedSemester ? $this->selectedSemester->id : null;
        $this->requirementId = $this->selectedRequirementId;
        $this->userId = $this->selectedUserId;
        $this->courseId = $this->selectedCourseId;
    }

    // Navigation methods
    public function setCategory($category)
    {
        if ($this->category !== $category) {
            $this->category = $category;
            $this->resetNavigation();
            $this->resetPage();
        }
    }

    public function clearCategoryFilter()
    {
        $this->category = 'requirement';
        $this->resetNavigation();
        $this->resetPage();
    }

    public function resetNavigation()
    {
        $this->selectedRequirementId = null;
        $this->selectedUserId = null;
        $this->selectedCourseId = null;
        $this->selectedFile = null;
        $this->updateBreadcrumbs();
        $this->updateUrlParameters();
    }

    /**
     * Unified navigation method
     */
    public function handleNavigation($level, $id = null)
    {
        try {
            switch ($level) {
                case 'category':
                    $this->resetNavigation();
                    $this->category = 'requirement';
                    break;
                    
                case 'requirement':
                    if ($this->category === 'user') {
                        // In user category: keep user and course, set requirement
                        $this->selectedRequirementId = $id;
                        $this->selectedFile = null;
                    } else {
                        // In requirement category: set requirement, clear user and course to show users list
                        $this->selectedRequirementId = $id;
                        $this->selectedUserId = null;
                        $this->selectedCourseId = null;
                        $this->selectedFile = null;
                    }
                    break;
                    
                case 'user':
                    if ($this->category === 'user') {
                        // In user category: set user, clear course and requirement
                        $this->selectedUserId = $id;
                        $this->selectedCourseId = null;
                        $this->selectedRequirementId = null;
                        $this->selectedFile = null;
                    } else {
                        // In requirement category: set user, keep requirement, clear course
                        $this->selectedUserId = $id;
                        $this->selectedCourseId = null;
                        $this->selectedFile = null;
                    }
                    break;
                    
                case 'course':
                    if ($this->category === 'user') {
                        // In user category: set course, keep user, clear requirement
                        $this->selectedCourseId = $id;
                        $this->selectedRequirementId = null;
                        $this->selectedFile = null;
                    } else {
                        // In requirement category: set course, keep user and requirement
                        $this->selectedCourseId = $id;
                        $this->selectedFile = null;
                    }
                    break;
                    
                case 'file':
                    $this->selectFile($id);
                    break;
            }
            
            $this->updateBreadcrumbs();
            $this->updateUrlParameters();
            $this->resetPage();
            
        } catch (\Exception $e) {
            \Log::error('Navigation error: ' . $e->getMessage());
            $this->resetNavigation();
        }
    }

    // Keep existing specific methods for backward compatibility
    public function selectRequirement($requirementId)
    {
        $this->handleNavigation('requirement', $requirementId);
    }

    public function selectUser($userId)
    {
        $this->handleNavigation('user', $userId);
    }

    public function selectCourse($courseId)
    {
        $this->handleNavigation('course', $courseId);
    }

    public function selectFile($fileId)
    {
        $this->selectedFile = Media::find($fileId);
        
        if ($this->selectedFile) {
            $this->fileUrl = route('file.preview', [
                'submission' => $this->selectedFile->model_id,
                'file' => $this->selectedFile->id
            ]);
            
            $this->isImage = str_starts_with($this->selectedFile->mime_type, 'image/');
            $this->isPdf = $this->selectedFile->mime_type === 'application/pdf';
            $this->isOfficeDoc = in_array(pathinfo($this->selectedFile->file_name, PATHINFO_EXTENSION), ['doc', 'docx', 'xls', 'xlsx']);
            $this->isPreviewable = $this->isImage || $this->isPdf || $this->isOfficeDoc;
        }
        
        $this->showSemesterPanel = false;
        $this->updateBreadcrumbs();
        $this->updateUrlParameters();
    }
    
    public function clearSelection()
    {
        $this->selectedFile = null;
        $this->fileUrl = null;
        $this->isImage = false;
        $this->isPdf = false;
        $this->isOfficeDoc = false;
        $this->isPreviewable = false;
        $this->updateBreadcrumbs();
        $this->updateUrlParameters();
    }

    /**
     * Improved breadcrumb navigation that preserves context properly
     */
    public function goBack($crumbType, $index = null)
    {
        try {
            if ($index !== null) {
                // Clear all selections after the clicked breadcrumb
                $this->clearSelectionsFromIndex($index);
            } else {
                // Fallback to type-based navigation
                $this->handleTypeBasedNavigation($crumbType);
            }
            
            $this->updateBreadcrumbs();
            $this->updateUrlParameters();
            $this->resetPage();
            
        } catch (\Exception $e) {
            \Log::error('Breadcrumb navigation error: ' . $e->getMessage());
            $this->resetNavigation();
        }
    }

    /**
     * Handle type-based navigation with proper category context
     */
    protected function handleTypeBasedNavigation($crumbType)
    {
        switch ($crumbType) {
            case 'category':
                $this->resetNavigation();
                $this->category = 'requirement';
                break;
                
            case 'requirement':
                if ($this->category === 'requirement') {
                    // Requirement category: clear requirement and everything below
                    $this->selectedRequirementId = null;
                    $this->selectedUserId = null;
                    $this->selectedCourseId = null;
                } else {
                    // User category: clear only requirement, keep user and course
                    $this->selectedRequirementId = null;
                }
                $this->selectedFile = null;
                break;
                
            case 'user':
                if ($this->category === 'requirement') {
                    // Requirement category: clear user and course, keep requirement
                    $this->selectedUserId = null;
                    $this->selectedCourseId = null;
                } else {
                    // User category: clear user and everything below
                    $this->selectedUserId = null;
                    $this->selectedCourseId = null;
                    $this->selectedRequirementId = null;
                }
                $this->selectedFile = null;
                break;
                
            case 'course':
                // Always clear course and file, keep higher levels based on category
                $this->selectedCourseId = null;
                $this->selectedFile = null;
                break;
        }
    }

    /**
     * Clear selections from a specific breadcrumb index with category awareness
     */
    protected function clearSelectionsFromIndex($index)
    {
        $breadcrumbTypes = array_column($this->breadcrumbs, 'type');
        
        // Clear all selections that come after the clicked index
        for ($i = $index + 1; $i < count($breadcrumbTypes); $i++) {
            $typeToClear = $breadcrumbTypes[$i];
            
            switch ($typeToClear) {
                case 'requirement':
                    $this->selectedRequirementId = null;
                    // In requirement category, clearing requirement also clears user and course
                    if ($this->category === 'requirement') {
                        $this->selectedUserId = null;
                        $this->selectedCourseId = null;
                    }
                    break;
                    
                case 'user':
                    $this->selectedUserId = null;
                    // Clearing user also clears course in both categories
                    $this->selectedCourseId = null;
                    // In user category, clearing user also clears requirement
                    if ($this->category === 'user') {
                        $this->selectedRequirementId = null;
                    }
                    break;
                    
                case 'course':
                    $this->selectedCourseId = null;
                    // In user category, clearing course also clears requirement
                    if ($this->category === 'user') {
                        $this->selectedRequirementId = null;
                    }
                    break;
                    
                case 'file':
                    $this->selectedFile = null;
                    break;
            }
        }
        
        // Ensure we don't have invalid state combinations
        $this->validateNavigationState();
    }

    /**
     * Validate and correct navigation state to prevent invalid combinations
     */
    protected function validateNavigationState()
    {
        // If we have a course selected but no user, clear the course
        if ($this->selectedCourseId && !$this->selectedUserId) {
            $this->selectedCourseId = null;
        }
        
        // If we have a requirement selected in user category but no course, clear requirement
        if ($this->category === 'user' && $this->selectedRequirementId && !$this->selectedCourseId) {
            $this->selectedRequirementId = null;
        }
        
        // Clear file if we're no longer at the file level
        if ($this->selectedFile && 
            (($this->category === 'requirement' && (!$this->selectedRequirementId || !$this->selectedUserId || !$this->selectedCourseId)) ||
             ($this->category === 'user' && (!$this->selectedUserId || !$this->selectedCourseId || !$this->selectedRequirementId)))) {
            $this->selectedFile = null;
        }
    }
    
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    /**
     * Improved breadcrumb generation
     */
    public function updateBreadcrumbs()
    {
        $this->breadcrumbs = [];

        // Always show category as first breadcrumb
        $this->breadcrumbs[] = [
            'type' => 'category',
            'name' => ucfirst($this->category),
            'id' => $this->category
        ];

        // Build breadcrumbs based on current navigation state
        if ($this->category === 'requirement') {
            $this->buildRequirementCategoryBreadcrumbs();
        } elseif ($this->category === 'user') {
            $this->buildUserCategoryBreadcrumbs();
        }

        // Add file breadcrumb if selected
        if ($this->selectedFile) {
            $this->breadcrumbs[] = [
                'type' => 'file',
                'name' => $this->selectedFile->file_name,
                'id' => $this->selectedFile->id
            ];
        }
    }

    /**
     * Build breadcrumbs for requirement category
     */
    protected function buildRequirementCategoryBreadcrumbs()
    {
        if ($this->selectedRequirementId) {
            $requirement = Requirement::find($this->selectedRequirementId);
            $this->breadcrumbs[] = [
                'type' => 'requirement',
                'name' => $requirement ? $requirement->name : 'Requirement',
                'id' => $this->selectedRequirementId
            ];

            if ($this->selectedUserId) {
                $user = User::find($this->selectedUserId);
                $this->breadcrumbs[] = [
                    'type' => 'user',
                    'name' => $user ? $user->full_name : 'User',
                    'id' => $this->selectedUserId
                ];

                if ($this->selectedCourseId) {
                    $course = Course::find($this->selectedCourseId);
                    $this->breadcrumbs[] = [
                        'type' => 'course',
                        'name' => $course ? $course->course_code : 'Course',
                        'id' => $this->selectedCourseId
                    ];
                }
            }
        }
    }

    /**
     * Build breadcrumbs for user category
     */
    protected function buildUserCategoryBreadcrumbs()
    {
        if ($this->selectedUserId) {
            $user = User::find($this->selectedUserId);
            $this->breadcrumbs[] = [
                'type' => 'user',
                'name' => $user ? $user->full_name : 'User',
                'id' => $this->selectedUserId
            ];

            if ($this->selectedCourseId) {
                $course = Course::find($this->selectedCourseId);
                $this->breadcrumbs[] = [
                    'type' => 'course',
                    'name' => $course ? $course->course_code : 'Course',
                    'id' => $this->selectedCourseId
                ];

                if ($this->selectedRequirementId) {
                    $requirement = Requirement::find($this->selectedRequirementId);
                    $this->breadcrumbs[] = [
                        'type' => 'requirement',
                        'name' => $requirement ? $requirement->name : 'Requirement',
                        'id' => $this->selectedRequirementId
                    ];
                }
            }
        }
    }

    /**
     * SIMPLIFIED: Get requirements that have submission indicators
     */
    public function getRequirements()
    {
        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        // Get requirements that have submission indicators in the current semester
        $requirementIds = RequirementSubmissionIndicator::whereBetween('submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->distinct()
            ->pluck('requirement_id');

        $query = Requirement::whereIn('id', $requirementIds)
            ->withCount(['submissionIndicators as file_count' => function($q) use ($semester) {
                $q->whereBetween('submitted_at', [
                    $semester->start_date,
                    $semester->end_date
                ]);
            }]);

        if ($this->search && !$this->selectedRequirementId && !$this->selectedUserId && !$this->selectedCourseId) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return $query->get()->map(function($requirement) {
            return [
                'id' => $requirement->id,
                'name' => $requirement->name,
                'file_count' => $requirement->file_count
            ];
        });
    }

    /**
     * SIMPLIFIED: Get users who have submission indicators for the selected requirement
     */
    public function getUsersForRequirement()
    {
        if (!$this->selectedRequirementId) return collect();

        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        // Get users who have submission indicators for this requirement in the current semester
        $userIds = RequirementSubmissionIndicator::where('requirement_id', $this->selectedRequirementId)
            ->whereBetween('submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->distinct()
            ->pluck('user_id');

        $query = User::whereIn('id', $userIds)
            ->withCount(['submittedRequirements as file_count' => function($q) use ($semester) {
                $q->where('requirement_id', $this->selectedRequirementId)
                ->whereHas('media')
                ->whereBetween('submitted_at', [
                    $semester->start_date,
                    $semester->end_date
                ]);
            }]);

        return $query->get()->map(function($user) {
            return [
                'user' => $user,
                'file_count' => $user->file_count
            ];
        });
    }

    /**
     * SIMPLIFIED: Get courses for selected user and requirement that have submission indicators
     */
    public function getCoursesForUserRequirement()
    {
        if (!$this->selectedRequirementId || !$this->selectedUserId) return collect();

        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        // Get courses that have submission indicators for this user and requirement
        $courseIds = RequirementSubmissionIndicator::where('requirement_id', $this->selectedRequirementId)
            ->where('user_id', $this->selectedUserId)
            ->whereBetween('submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->distinct()
            ->pluck('course_id');

        $query = Course::whereIn('id', $courseIds)
            ->withCount(['submittedRequirements as file_count' => function($q) {
                $q->where('requirement_id', $this->selectedRequirementId)
                ->where('user_id', $this->selectedUserId)
                ->whereHas('media');
            }]);

        if ($this->search && $this->selectedRequirementId && $this->selectedUserId && !$this->selectedCourseId) {
            $query->where(function($q) {
                $q->where('course_code', 'like', '%'.$this->search.'%')
                ->orWhere('course_name', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get()->map(function($course) {
            return [
                'course' => $course,
                'file_count' => $course->file_count
            ];
        });
    }

    /**
     * SIMPLIFIED: Get users who have submission indicators
     */
    public function getUsers()
    {
        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        // Get users who have submission indicators in the current semester
        $userIds = RequirementSubmissionIndicator::whereBetween('submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->distinct()
            ->pluck('user_id');

        $query = User::whereIn('id', $userIds)
            ->withCount(['submittedRequirements as file_count' => function($q) use ($semester) {
                $q->whereHas('media')
                ->whereBetween('submitted_at', [
                    $semester->start_date,
                    $semester->end_date
                ]);
            }]);

        if ($this->search && !$this->selectedUserId && !$this->selectedCourseId && !$this->selectedRequirementId) {
            $query->where(function($q) {
                $q->where('firstname', 'like', '%'.$this->search.'%')
                ->orWhere('lastname', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get()->map(function($user) {
            return [
                'user' => $user,
                'file_count' => $user->file_count
            ];
        });
    }

    /**
     * SIMPLIFIED: Get courses for selected user that have submission indicators
     */
    public function getCoursesForUser()
    {
        if (!$this->selectedUserId) return collect();

        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        // Get courses that have submission indicators for this user
        $courseIds = RequirementSubmissionIndicator::where('user_id', $this->selectedUserId)
            ->whereBetween('submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->distinct()
            ->pluck('course_id');

        $query = Course::whereIn('id', $courseIds)
            ->withCount(['submittedRequirements as file_count' => function($q) {
                $q->where('user_id', $this->selectedUserId)
                ->whereHas('media');
            }]);

        if ($this->search && $this->selectedUserId && !$this->selectedCourseId && !$this->selectedRequirementId) {
            $query->where(function($q) {
                $q->where('course_code', 'like', '%'.$this->search.'%')
                ->orWhere('course_name', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get()->map(function($course) {
            return [
                'course' => $course,
                'file_count' => $course->file_count
            ];
        });
    }

    /**
     * SIMPLIFIED: Get requirements for selected user and course that have submission indicators
     */
    public function getRequirementsForUserCourse()
    {
        if (!$this->selectedUserId || !$this->selectedCourseId) return collect();

        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        // Get requirements that have submission indicators for this user and course
        $requirementIds = RequirementSubmissionIndicator::where('user_id', $this->selectedUserId)
            ->where('course_id', $this->selectedCourseId)
            ->whereBetween('submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->distinct()
            ->pluck('requirement_id');

        $query = Requirement::whereIn('id', $requirementIds)
            ->withCount(['submittedRequirements as file_count' => function($q) {
                $q->where('user_id', $this->selectedUserId)
                ->where('course_id', $this->selectedCourseId)
                ->whereHas('media');
            }]);

        if ($this->search && $this->selectedUserId && $this->selectedCourseId && !$this->selectedRequirementId) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return $query->get()->map(function($requirement) {
            return [
                'requirement' => $requirement,
                'file_count' => $requirement->file_count
            ];
        });
    }

    /**
     * SIMPLIFIED: Get actual media files with submission indicators
     */
    public function getFiles()
    {
        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->viewMode === 'grid' ? 24 : 10);
        }

        // First get the submission IDs that have indicators
        $submissionIds = RequirementSubmissionIndicator::whereBetween('submitted_at', [
                $semester->start_date,
                $semester->end_date
            ])
            ->when($this->selectedRequirementId, function($q) {
                $q->where('requirement_id', $this->selectedRequirementId);
            })
            ->when($this->selectedUserId, function($q) {
                $q->where('user_id', $this->selectedUserId);
            })
            ->when($this->selectedCourseId, function($q) {
                $q->where('course_id', $this->selectedCourseId);
            })
            ->pluck('id');

        if ($submissionIds->isEmpty()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->viewMode === 'grid' ? 24 : 10);
        }

        // Now get media files for these submissions
        $query = Media::query()
            ->with([
                'model.user', 
                'model.requirement', 
                'model.course.program'
            ])
            ->whereHasMorph('model', [SubmittedRequirement::class], function($q) use ($submissionIds) {
                $q->whereIn('id', function($subQuery) use ($submissionIds) {
                    $subQuery->select('submitted_requirements.id')
                        ->from('submitted_requirements')
                        ->join('requirement_submission_indicators', function($join) {
                            $join->on('submitted_requirements.requirement_id', '=', 'requirement_submission_indicators.requirement_id')
                                ->on('submitted_requirements.user_id', '=', 'requirement_submission_indicators.user_id')
                                ->on('submitted_requirements.course_id', '=', 'requirement_submission_indicators.course_id');
                        })
                        ->whereIn('requirement_submission_indicators.id', $submissionIds);
                });
            })
            ->orderBy('created_at', 'desc');

        // Apply search if at files level
        $isAtFilesLevel = false;
        
        if ($this->category === 'requirement') {
            $isAtFilesLevel = $this->selectedRequirementId && $this->selectedUserId && $this->selectedCourseId;
        } elseif ($this->category === 'user') {
            $isAtFilesLevel = $this->selectedUserId && $this->selectedCourseId && $this->selectedRequirementId;
        }

        if ($this->search && $isAtFilesLevel) {
            $query->where(function($q) {
                $q->where('file_name', 'like', '%'.$this->search.'%')
                ->orWhereHasMorph('model', [SubmittedRequirement::class], function($modelQuery) {
                    $modelQuery->whereHas('user', function($userQuery) {
                        $userQuery->where('firstname', 'like', '%'.$this->search.'%')
                                ->orWhere('lastname', 'like', '%'.$this->search.'%')
                                ->orWhere('email', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('requirement', function($reqQuery) {
                        $reqQuery->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('course', function($courseQuery) {
                        $courseQuery->where('course_code', 'like', '%'.$this->search.'%')
                                ->orWhere('course_name', 'like', '%'.$this->search.'%')
                                ->orWhereHas('program', function($programQuery) {
                                    $programQuery->where('program_name', 'like', '%'.$this->search.'%')
                                                ->orWhere('program_code', 'like', '%'.$this->search.'%');
                                });
                    });
                });
            });
        }

        return $query->paginate($this->viewMode === 'grid' ? 24 : 10);
    }

    public function getSemestersProperty()
    {
        return Semester::orderBy('created_at', 'desc')->get();
    }

    public function getSearchPlaceholder()
    {
        if ($this->category === 'requirement') {
            if (!$this->selectedRequirementId) {
                return 'Search requirements...';
            } elseif ($this->selectedRequirementId && !$this->selectedUserId) {
                return 'Search users...';
            } elseif ($this->selectedRequirementId && $this->selectedUserId && !$this->selectedCourseId) {
                return 'Search courses...';
            } else {
                return 'Search files...';
            }
        } elseif ($this->category === 'user') {
            if (!$this->selectedUserId) {
                return 'Search users...';
            } elseif ($this->selectedUserId && !$this->selectedCourseId) {
                return 'Search courses...';
            } elseif ($this->selectedUserId && $this->selectedCourseId && !$this->selectedRequirementId) {
                return 'Search requirements...';
            } else {
                return 'Search files...';
            }
        }
        
        return 'Search files or users...';
    }

    /**
     * Get the currently active semester for display
     */
    public function getActiveSemesterProperty()
    {
        // If we have a selected semester, use that
        if ($this->selectedSemester) {
            return $this->selectedSemester;
        }
        
        // Otherwise get the actually active semester
        return Semester::where('is_active', true)->first();
    }

    public function render()
    {
        // Check if there's no active semester
        $activeSemester = $this->activeSemester;
        if (!$activeSemester) {
            return view('livewire.admin.file-manager.file-manager-index', [
                'noActiveSemester' => true,
                'activeSemester' => null,
                'semesters' => $this->semesters,
                'files' => collect(),
                'requirements' => collect(),
                'users' => collect(),
                'usersForRequirement' => collect(),
                'coursesForUserRequirement' => collect(),
                'coursesForUser' => collect(),
                'requirementsForUserCourse' => collect(),
            ])->extends('layouts.app');
        }

        $data = [
            'files' => $this->getFiles(),
            'activeSemester' => $activeSemester, 
            'semesters' => $this->semesters,
            'noActiveSemester' => false,
        ];

        // Add data based on current navigation state and category
        if ($this->category === 'requirement') {
            if (!$this->selectedRequirementId) {
                $data['requirements'] = $this->getRequirements();
            } elseif ($this->selectedRequirementId && !$this->selectedUserId) {
                $data['usersForRequirement'] = $this->getUsersForRequirement();
            } elseif ($this->selectedRequirementId && $this->selectedUserId && !$this->selectedCourseId) {
                $data['coursesForUserRequirement'] = $this->getCoursesForUserRequirement();
            }
        } elseif ($this->category === 'user') {
            if (!$this->selectedUserId) {
                $data['users'] = $this->getUsers();
            } elseif ($this->selectedUserId && !$this->selectedCourseId) {
                $data['coursesForUser'] = $this->getCoursesForUser();
            } elseif ($this->selectedUserId && $this->selectedCourseId && !$this->selectedRequirementId) {
                $data['requirementsForUserCourse'] = $this->getRequirementsForUserCourse();
            }
        }

        return view('livewire.admin.file-manager.file-manager-index', $data)->extends('layouts.app');
    }
}