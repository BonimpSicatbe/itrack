<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use App\Models\Semester;
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
        $activeSemester = Semester::getActiveSemester();
        
        if (!$activeSemester) {
            return view('livewire.admin.submitted-requirements.submitted-requirements-index', [
                'activeSemester' => null,
                'categories' => $this->getCategories(),
                'submittedRequirements' => null,
                'groupedItems' => []
            ]);
        }

        if ($this->category === 'file') {
            $submittedRequirements = $this->getSubmittedRequirementsQuery($activeSemester)->paginate(10);
            $groupedItems = [];
        } else {
            $submittedRequirements = null;
            $groupedItems = $this->getGroupedRequirements($activeSemester);
        }

        return view('livewire.admin.submitted-requirements.submitted-requirements-index', [
            'submittedRequirements' => $submittedRequirements,
            'groupedItems' => $groupedItems,
            'categories' => $this->getCategories(),
            'activeSemester' => $activeSemester,
        ]);
    }

    protected function getSubmittedRequirementsQuery($activeSemester)
    {
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

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    protected function getGroupedRequirements($activeSemester)
    {
        $query = Requirement::where('semester_id', $activeSemester->id)
            ->orderBy('name');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $requirements = $query->get();

        $groupedItems = [];

        foreach ($requirements as $requirement) {
            $submissions = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->with(['user.college', 'user.department', 'media'])
                ->get();
                
            $groupedItems[$requirement->id] = [
                'name' => $requirement->name,
                'count' => $submissions->count(),
                'items' => $submissions
            ];
        }

        return $groupedItems;
    }

    protected function getCategories()
    {
        return [
            'file' => 'File',
            'requirement' => 'Requirement',
        ];
    }
}