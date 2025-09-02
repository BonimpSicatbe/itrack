<?php

namespace App\Livewire\Admin\FileManager;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\SubmittedRequirement;
use App\Models\Semester;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FileManagerIndex extends Component
{
    use WithPagination;

    public $selectedFile = null;
    public $viewMode = 'grid';
    public $groupBy = null;
    public $search = '';
    public $selectedGroup = null;
    public $selectedSemester = null;
    public $showSemesterPanel = false; // Hidden by default
    public $currentFolder = null;
    public $folderType = null;
    public $breadcrumbs = [];
    public $fileUrl = null;
    public $isImage = false;
    public $isPdf = false;
    public $isOfficeDoc = false;
    public $isPreviewable = false;

    
    protected $queryString = [
        'search' => ['except' => ''],
        'groupBy' => ['except' => ''],
        'selectedGroup' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
        'showSemesterPanel' => ['except' => false], // Hidden by default
        'currentFolder' => ['except' => null],
        'folderType' => ['except' => null],
    ];

    protected $listeners = [
        'semesterActivated' => '$refresh',
        'semesterSelected' => 'loadSemesterFiles',
        'semesterArchived' => 'handleSemesterArchived',
        'clearSelectedSemester' => 'clearSelectedSemester'
    ];

    // New method to handle panel toggling
    public function togglePanel()
    {
        // If file details are open, close them first
        if ($this->selectedFile) {
            $this->selectedFile = null;
            $this->updateBreadcrumbs();
        }
        
        // Then toggle the semester panel
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
        $this->updateBreadcrumbs();
    }

    public function selectFile($fileId)
    {
        $this->selectedFile = Media::find($fileId);
        
        if ($this->selectedFile) {
            // Set file URL and determine file type
            $this->fileUrl = route('file.preview', [
                'submission' => $this->selectedFile->model_id,
                'file' => $this->selectedFile->id
            ]);
            
            // Determine file type for proper display
            $this->isImage = str_starts_with($this->selectedFile->mime_type, 'image/');
            $this->isPdf = $this->selectedFile->mime_type === 'application/pdf';
            $this->isOfficeDoc = in_array(pathinfo($this->selectedFile->file_name, PATHINFO_EXTENSION), ['doc', 'docx', 'xls', 'xlsx']);
            $this->isPreviewable = $this->isImage || $this->isPdf || $this->isOfficeDoc;
        }
        
        // Automatically hide semester panel when file is selected
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
    
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function setGroup($group)
    {
        $this->groupBy = $group;
        $this->selectedGroup = null;
        $this->currentFolder = null;
        $this->folderType = null;
        $this->updateBreadcrumbs();
        $this->resetPage();
    }

    public function navigateToFolder($type, $id)
    {
        $this->currentFolder = $id;
        $this->folderType = $type;
        $this->selectedGroup = $id; // For backward compatibility
        $this->groupBy = $type; // For backward compatibility
        $this->updateBreadcrumbs();
        $this->resetPage();
    }

    public function selectGroup($groupId)
    {
        $this->navigateToFolder($this->groupBy, $groupId);
    }

    public function clearGroupFilter()
    {
        $this->groupBy = null;
        $this->selectedGroup = null;
        $this->currentFolder = null;
        $this->folderType = null;
        $this->updateBreadcrumbs();
        $this->resetPage();
    }

    public function updateBreadcrumbs()
    {
        $this->breadcrumbs = [
            ['type' => 'root', 'name' => 'File Manager', 'id' => null]
        ];
        
        if ($this->currentFolder && $this->folderType) {
            $folderName = $this->getSelectedGroupName();
            
            if ($folderName) {
                $this->breadcrumbs[] = [
                    'type' => $this->folderType,
                    'name' => $folderName,
                    'id' => $this->currentFolder
                ];
            }
        }
        
        if ($this->selectedFile) {
            $this->breadcrumbs[] = [
                'type' => 'file',
                'name' => $this->selectedFile->file_name,
                'id' => $this->selectedFile->id
            ];
        }
    }

    public function getGroupedFiles()
    {
        $semester = $this->selectedSemester ?? Semester::getActiveSemester();
        
        // If no semester is selected and no active semester exists, return empty paginator
        if (!$semester) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 24);
        }

        $query = Media::query()
            ->with(['model.user', 'model.user.college', 'model.user.department'])
            ->whereHasMorph('model', [SubmittedRequirement::class], function($q) use ($semester) {
                $q->whereBetween('created_at', [
                    $semester->start_date,
                    $semester->end_date
                ]);
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
                                ->orWhere('email', 'like', '%'.$this->search.'%')
                                ->orWhereHas('college', function($collegeQuery) {
                                    $collegeQuery->where('name', 'like', '%'.$this->search.'%');
                                })
                                ->orWhereHas('department', function($deptQuery) {
                                    $deptQuery->where('name', 'like', '%'.$this->search.'%');
                                });
                    });
                });
            });
        }

        // Apply group filter if selected
        if ($this->groupBy && $this->selectedGroup) {
            switch ($this->groupBy) {
                case 'user':
                    $query->whereHasMorph('model', [SubmittedRequirement::class], function($q) {
                        $q->where('user_id', $this->selectedGroup);
                    });
                    break;
                case 'college':
                    $query->whereHasMorph('model', [SubmittedRequirement::class], function($q) {
                        $q->whereHas('user', function($userQuery) {
                            $userQuery->where('college_id', $this->selectedGroup);
                        });
                    });
                    break;
                case 'department':
                    $query->whereHasMorph('model', [SubmittedRequirement::class], function($q) {
                        $q->whereHas('user', function($userQuery) {
                            $userQuery->where('department_id', $this->selectedGroup);
                        });
                    });
                    break;
            }
        }

        return $query->paginate($this->viewMode === 'grid' ? 24 : 10);
    }

    public function getGroups()
    {
        if (!$this->groupBy) return collect();

        $activeSemester = $this->selectedSemester ?? Semester::getActiveSemester();
        if (!$activeSemester) return collect();

        switch ($this->groupBy) {
            case 'user':
                return User::withCount(['submittedRequirements as files_count' => function($query) use ($activeSemester) {
                    $query->select(\DB::raw('count(distinct media.id)'))
                        ->join('media', function($join) {
                            $join->on('media.model_id', '=', 'submitted_requirements.id')
                                ->where('media.model_type', SubmittedRequirement::class);
                        })
                        ->whereBetween('submitted_requirements.created_at', [
                            $activeSemester->start_date,
                            $activeSemester->end_date
                        ]);
                }])
                ->has('submittedRequirements')
                ->orderBy('lastname')
                ->get();
                
            case 'college':
                return College::withCount(['users as files_count' => function($query) use ($activeSemester) {
                    $query->select(\DB::raw('count(distinct media.id)'))
                        ->join('submitted_requirements', 'submitted_requirements.user_id', '=', 'users.id')
                        ->join('media', function($join) {
                            $join->on('media.model_id', '=', 'submitted_requirements.id')
                                ->where('media.model_type', SubmittedRequirement::class);
                        })
                        ->whereBetween('submitted_requirements.created_at', [
                            $activeSemester->start_date,
                            $activeSemester->end_date
                        ]);
                }])
                ->has('users.submittedRequirements')
                ->orderBy('name')
                ->get();
                
            case 'department':
                return Department::withCount(['users as files_count' => function($query) use ($activeSemester) {
                    $query->select(\DB::raw('count(distinct media.id)'))
                        ->join('submitted_requirements', 'submitted_requirements.user_id', '=', 'users.id')
                        ->join('media', function($join) {
                            $join->on('media.model_id', '=', 'submitted_requirements.id')
                                ->where('media.model_type', SubmittedRequirement::class);
                        })
                        ->whereBetween('submitted_requirements.created_at', [
                            $activeSemester->start_date,
                            $activeSemester->end_date
                        ]);
                }])
                ->has('users.submittedRequirements')
                ->orderBy('name')
                ->get();
                
            default:
                return collect();
        }
    }

    public function shouldDisplayFiles()
    {
        return !$this->groupBy || ($this->groupBy && $this->selectedGroup);
    }

    public function render()
    {
        $files = $this->getGroupedFiles();
        $groups = $this->getGroups();
        $activeSemester = $this->selectedSemester ?? Semester::getActiveSemester();
        
        return view('livewire.admin.file-manager.file-manager-index', [
            'files' => $files,
            'groups' => $groups,
            'selectedGroupName' => $this->getSelectedGroupName(),
            'activeSemester' => $activeSemester,
            'shouldDisplayFiles' => $this->shouldDisplayFiles(),
        ])->extends('layouts.app'); // Add this line if needed
    }
    
    protected function getSelectedGroupName()
    {
        if (!$this->groupBy || !$this->selectedGroup) return null;
        
        switch ($this->groupBy) {
            case 'user':
                return User::find($this->selectedGroup)?->full_name;
            case 'college':
                return College::find($this->selectedGroup)?->name;
            case 'department':
                return Department::find($this->selectedGroup)?->name;
            default:
                return null;
        }
    }
}