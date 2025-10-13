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
    public $category = null; 
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

    
    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'selectedRequirementId' => ['except' => null],
        'selectedUserId' => ['except' => null],
        'selectedCourseId' => ['except' => null],
        'viewMode' => ['except' => 'grid'],
        'showSemesterPanel' => ['except' => false],
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
        $this->updateBreadcrumbs();
    }

    // Navigation methods
    public function setCategory($category)
    {
        $this->category = $category;
        $this->resetNavigation();
        $this->resetPage();
    }

    public function clearCategoryFilter()
    {
        $this->category = null;
        $this->resetNavigation();
        $this->resetPage();
    }

    public function resetNavigation()
    {
        $this->selectedRequirementId = null;
        $this->selectedUserId = null;
        $this->selectedCourseId = null;
        $this->selectedFile = null;
        $this->category = null; // Also reset category when doing full reset
        $this->updateBreadcrumbs();
    }

    public function selectRequirement($requirementId)
    {
        $this->selectedRequirementId = $requirementId;
        $this->updateBreadcrumbs();
        $this->resetPage();
    }

    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $this->updateBreadcrumbs();
        $this->resetPage();
    }

    public function selectCourse($courseId)
    {
        $this->selectedCourseId = $courseId;
        $this->updateBreadcrumbs();
        $this->resetPage();
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
    }

    public function goBack($crumbType)
    {
        switch ($crumbType) {
            case 'category':
                $this->resetNavigation();
                $this->category = null;
                break;
            case 'requirement':
                $this->selectedRequirementId = null;
                break;
            case 'user':
                $this->selectedUserId = null;
                break;
            case 'course':
                $this->selectedCourseId = null;
                break;
        }
        $this->updateBreadcrumbs();
        $this->resetPage();
    }
    
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function updateBreadcrumbs()
    {
        $this->breadcrumbs = [];

        // Start with category as the root instead of "File Manager"
        if ($this->category) {
            $this->breadcrumbs[] = [
                'type' => 'category',
                'name' => ucfirst($this->category),
                'id' => $this->category
            ];
        } else {
            // Default to requirement category if none selected
            $this->breadcrumbs[] = [
                'type' => 'category',
                'name' => 'Requirement',
                'id' => 'requirement'
            ];
        }

        // Requirements category breadcrumbs
        if ($this->category === 'requirement') {
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

        // Users category breadcrumbs
        if ($this->category === 'user') {
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

        // Add file breadcrumb if selected
        if ($this->selectedFile) {
            $this->breadcrumbs[] = [
                'type' => 'file',
                'name' => $this->selectedFile->file_name,
                'id' => $this->selectedFile->id
            ];
        }
    }

    public function getRequirements()
    {
        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        $query = Requirement::whereHas('submissionIndicators', function($q) use ($semester) {
            $q->whereBetween('requirement_submission_indicators.submitted_at', [
                $semester->start_date,
                $semester->end_date
            ]);
        })
        ->withCount(['submissionIndicators as file_count' => function($q) use ($semester) {
            $q->whereBetween('requirement_submission_indicators.submitted_at', [
                $semester->start_date,
                $semester->end_date
            ]);
        }]);

        if ($this->search) {
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

    public function getUsersForRequirement()
    {
        if (!$this->selectedRequirementId) return collect();

        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        $query = User::whereHas('submissionIndicators', function($q) use ($semester) {
                $q->where('requirement_id', $this->selectedRequirementId)
                ->whereBetween('requirement_submission_indicators.submitted_at', [
                    $semester->start_date,
                    $semester->end_date
                ]);
            })
            ->withCount(['submissionIndicators as file_count' => function($q) use ($semester) {
                $q->where('requirement_id', $this->selectedRequirementId)
                ->whereBetween('requirement_submission_indicators.submitted_at', [
                    $semester->start_date,
                    $semester->end_date
                ]);
            }])
            ->withCount(['courses as course_count' => function($q) use ($semester) {
                $q->whereHas('submissionIndicators', function($q2) use ($semester) {
                    $q2->where('requirement_id', $this->selectedRequirementId)
                    ->where('user_id', DB::raw('users.id'))
                    ->whereBetween('requirement_submission_indicators.submitted_at', [
                        $semester->start_date,
                        $semester->end_date
                    ]);
                })
                ->distinct();
            }]);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('firstname', 'like', '%'.$this->search.'%')
                ->orWhere('lastname', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get()->map(function($user) {
            return [
                'user' => $user,
                'file_count' => $user->file_count,
                'course_count' => $user->course_count
            ];
        });
    }

    public function getCoursesForUserRequirement()
    {
        if (!$this->selectedRequirementId || !$this->selectedUserId) return collect();

        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        $query = Course::whereHas('submissionIndicators', function($q) {
                $q->where('requirement_id', $this->selectedRequirementId)
                ->where('user_id', $this->selectedUserId);
            })
            ->withCount(['submissionIndicators as file_count' => function($q) {
                $q->where('requirement_id', $this->selectedRequirementId)
                ->where('user_id', $this->selectedUserId);
            }]);

        if ($this->search) {
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

    public function getUsers()
    {
        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        $query = User::whereHas('submissionIndicators', function($q) use ($semester) {
                $q->whereBetween('requirement_submission_indicators.submitted_at', [
                    $semester->start_date,
                    $semester->end_date
                ]);
            })
            ->withCount(['submissionIndicators as file_count' => function($q) use ($semester) {
                $q->whereBetween('requirement_submission_indicators.submitted_at', [
                    $semester->start_date,
                    $semester->end_date
                ]);
            }])
            ->withCount(['courses as course_count' => function($q) use ($semester) {
                $q->whereHas('submissionIndicators', function($q2) use ($semester) {
                    $q2->whereBetween('requirement_submission_indicators.submitted_at', [
                        $semester->start_date,
                        $semester->end_date
                    ]);
                })
                ->distinct();
            }]);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('firstname', 'like', '%'.$this->search.'%')
                ->orWhere('lastname', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get()->map(function($user) {
            return [
                'user' => $user,
                'file_count' => $user->file_count,
                'course_count' => $user->course_count
            ];
        });
    }

    public function getCoursesForUser()
    {
        if (!$this->selectedUserId) return collect();

        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        $query = Course::whereHas('submissionIndicators', function($q) {
                $q->where('user_id', $this->selectedUserId);
            })
            ->withCount(['submissionIndicators as file_count' => function($q) {
                $q->where('user_id', $this->selectedUserId);
            }]);

        if ($this->search) {
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


    public function getRequirementsForUserCourse()
    {
        if (!$this->selectedUserId || !$this->selectedCourseId) return collect();

        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) return collect();

        $query = Requirement::whereHas('submissionIndicators', function($q) {
                $q->where('user_id', $this->selectedUserId)
                ->where('course_id', $this->selectedCourseId);
            })
            ->withCount(['submissionIndicators as file_count' => function($q) {
                $q->where('user_id', $this->selectedUserId)
                ->where('course_id', $this->selectedCourseId);
            }]);

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return $query->get()->map(function($requirement) {
            return [
                'requirement' => $requirement,
                'file_count' => $requirement->file_count
            ];
        });
    }

    public function getFiles()
    {
        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$semester) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->viewMode === 'grid' ? 24 : 10);
        }

        // Get the submission indicator IDs that match our criteria
        $indicatorQuery = RequirementSubmissionIndicator::query()
            ->whereBetween('submitted_at', [
                $semester->start_date,
                $semester->end_date
            ]);

        // Apply filters based on current navigation state and category
        if ($this->category === 'requirement') {
            if ($this->selectedRequirementId) {
                $indicatorQuery->where('requirement_id', $this->selectedRequirementId);
            }
            if ($this->selectedUserId) {
                $indicatorQuery->where('user_id', $this->selectedUserId);
            }
            if ($this->selectedCourseId) {
                $indicatorQuery->where('course_id', $this->selectedCourseId);
            }
        } elseif ($this->category === 'user') {
            if ($this->selectedUserId) {
                $indicatorQuery->where('user_id', $this->selectedUserId);
            }
            if ($this->selectedCourseId) {
                $indicatorQuery->where('course_id', $this->selectedCourseId);
            }
            if ($this->selectedRequirementId) {
                $indicatorQuery->where('requirement_id', $this->selectedRequirementId);
            }
        }

        $matchingIndicators = $indicatorQuery->get();

        if ($matchingIndicators->isEmpty()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->viewMode === 'grid' ? 24 : 10);
        }

        // Build query for media files that belong to SubmittedRequirements matching the indicators
        $query = Media::query()
            ->with(['model.user', 'model.requirement', 'model.course'])
            ->whereHasMorph('model', [SubmittedRequirement::class], function($q) use ($matchingIndicators) {
                $q->where(function($subQuery) use ($matchingIndicators) {
                    foreach ($matchingIndicators as $indicator) {
                        $subQuery->orWhere(function($q2) use ($indicator) {
                            $q2->where('requirement_id', $indicator->requirement_id)
                            ->where('user_id', $indicator->user_id)
                            ->where('course_id', $indicator->course_id);
                        });
                    }
                });
            })
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($this->search) {
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
                                ->orWhere('course_name', 'like', '%'.$this->search.'%');
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

    public function render()
    {
        $data = [
            'files' => $this->getFiles(),
            'activeSemester' => $this->selectedSemester ?? Semester::getActiveSemester(),
            'semesters' => $this->semesters,
        ];

        // Add data based on current navigation state
        if ($this->category === 'requirement' || !$this->category) {
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