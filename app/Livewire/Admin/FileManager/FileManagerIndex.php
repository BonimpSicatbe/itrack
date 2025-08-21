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
    public $showSemesterPanel = true;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'groupBy' => ['except' => ''],
        'selectedGroup' => ['except' => ''],
        'viewMode' => ['except' => 'grid']
    ];

    protected $listeners = [
        'semesterActivated' => '$refresh',
        'semesterSelected' => 'loadSemesterFiles',
        'semesterArchived' => 'handleSemesterArchived',
        'clearSelectedSemester' => 'clearSelectedSemester'
    ];

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
    }

    public function selectFile($fileId)
    {
        $this->selectedFile = Media::find($fileId);
    }
    
    public function clearSelection()
    {
        $this->selectedFile = null;
    }
    
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function setGroup($group)
    {
        $this->groupBy = $group;
        $this->selectedGroup = null;
        $this->resetPage();
    }

    public function selectGroup($groupId)
    {
        $this->selectedGroup = $groupId;
        $this->resetPage();
    }

    public function clearGroupFilter()
    {
        $this->groupBy = null;
        $this->selectedGroup = null;
        $this->resetPage();
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
                if ($semester) {
                    $q->whereBetween('created_at', [
                        $semester->start_date,
                        $semester->end_date
                    ]);
                } else {
                    $q->whereNull('id'); // Force no results if no semester
                }
            })
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($this->search) {
            $query->where('file_name', 'like', '%'.$this->search.'%');
        }

        // Apply group filter if selected
        if ($this->groupBy && $this->selectedGroup) {
            switch ($this->groupBy) {
                case 'user':
                    $query->whereHas('model', function($q) {
                        $q->where('user_id', $this->selectedGroup);
                    });
                    break;
                case 'college':
                    $query->whereHas('model.user', function($q) {
                        $q->where('college_id', $this->selectedGroup);
                    });
                    break;
                case 'department':
                    $query->whereHas('model.user', function($q) {
                        $q->where('department_id', $this->selectedGroup);
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
        ]);
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