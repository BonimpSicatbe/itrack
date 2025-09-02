<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use App\Models\Semester;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class SubmittedRequirementsIndex extends Component
{
    use WithPagination;

    public $viewMode = 'list';
    public $category = 'file';
    public $search = '';
    public $statusFilter = '';

    protected $queryString = [
        'category' => ['except' => 'file'],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function switchView($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function setCategory($category)
    {
        $this->category = $category;
        $this->resetPage();
    }

    public function clearCategory()
    {
        $this->category = 'file';
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    public function render()
    {
        // Get the active semester
        $activeSemester = Semester::getActiveSemester();
        
        // Initialize empty collections
        $submittedRequirements = null;
        $groupedItems = [];
        
        // Only query submitted requirements if there's an active semester
        if ($activeSemester) {
            if ($this->category === 'file') {
                $query = SubmittedRequirement::query()
                    ->with([
                        'requirement', 
                        'user.college', 
                        'user.department', 
                        'media'
                    ])
                    ->whereHas('requirement', function($q) use ($activeSemester) {
                        $q->where('semester_id', $activeSemester->id);
                    })
                    ->orderBy('submitted_at', 'asc');

                // Apply search filter
                if ($this->search) {
                    $query->where(function($q) {
                        $q->whereHas('requirement', function($q) {
                            $q->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('user', function($q) {
                            $q->where('firstname', 'like', '%'.$this->search.'%')
                            ->orWhere('middlename', 'like', '%'.$this->search.'%')
                            ->orWhere('lastname', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('media', function($q) {
                            $q->where('file_name', 'like', '%'.$this->search.'%');
                        });
                    });
                }

                // Apply status filter
                if ($this->statusFilter) {
                    $query->where('status', $this->statusFilter);
                }

                $submittedRequirements = $query->paginate(10);
            } else {
                // For other categories, get all requirements and group the results
                $requirements = Requirement::where('semester_id', $activeSemester->id)
                    ->orderBy('name')
                    ->get();

                foreach ($requirements as $requirement) {
                    $groupKey = $requirement->id;
                    $groupName = $requirement->name;
                    
                    // Get submissions for this requirement
                    $submissions = SubmittedRequirement::where('requirement_id', $requirement->id)
                        ->with(['user.college', 'user.department', 'media'])
                        ->get();
                        
                    $groupedItems[$groupKey] = [
                        'name' => $groupName,
                        'count' => $submissions->count(),
                        'items' => $submissions
                    ];
                }
            }
        }

        return view('livewire.admin.submitted-requirements.submitted-requirements-index', [
            'submittedRequirements' => $submittedRequirements,
            'groupedItems' => $groupedItems,
            'categories' => [
                'file' => 'File',
                'requirement' => 'Requirement',
            ],
            'activeSemester' => $activeSemester,
        ]);
    }

    /* ========== STATUS HELPER METHODS (Using the Model) ========== */

    public function getStatusText($status)
    {
        return SubmittedRequirement::statuses()[$status] ?? 'Unknown';
    }
}