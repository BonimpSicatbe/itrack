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

    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_REVISION_NEEDED = 'revision_needed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPROVED = 'approved';

    public function getStatusText($status)
    {
        return match($status) {
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_REVISION_NEEDED => 'Revision Needed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_APPROVED => 'Approved',
            default => 'Unknown',
        };
    }

    public function getStatusBadge($status)
    {
        return match($status) {
            self::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800',
            self::STATUS_REVISION_NEEDED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

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
        
        if ($this->category === 'file') {
            $query = SubmittedRequirement::query()
                ->with([
                    'requirement', 
                    'user.college', 
                    'user.department', 
                    'media'
                ])
                ->when($activeSemester, function ($query) use ($activeSemester) {
                    $query->whereHas('requirement', function($q) use ($activeSemester) {
                        $q->where('semester_id', $activeSemester->id);
                    });
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

            return view('livewire.admin.submitted-requirements.submitted-requirements-index', [
                'submittedRequirements' => $query->paginate(10),
                'groupedItems' => null,
                'categories' => [
                    'file' => 'File',
                    'requirement' => 'Requirement',
                ],
                'activeSemester' => $activeSemester, // Pass to view if needed
            ]);
        }

        // For other categories, group the results
        $groupedItems = [];
        $items = SubmittedRequirement::query()
            ->with([
                'requirement', 
                'user.college', 
                'user.department', 
                'media'
            ])
            ->when($activeSemester, function ($query) use ($activeSemester) {
                $query->whereHas('requirement', function($q) use ($activeSemester) {
                    $q->where('semester_id', $activeSemester->id);
                });
            })
            ->orderBy('submitted_at', 'asc')
            ->get();

        foreach ($items as $item) {
            $groupKey = null;
            $groupName = null;

            switch ($this->category) {
                case 'requirement':
                    $groupKey = $item->requirement_id;
                    $groupName = $item->requirement->name;
                    break;
            }

            if ($groupKey) {
                if (!isset($groupedItems[$groupKey])) {
                    $groupedItems[$groupKey] = [
                        'name' => $groupName,
                        'count' => 0,
                        'items' => []
                    ];
                }
                $groupedItems[$groupKey]['items'][] = $item;
                $groupedItems[$groupKey]['count']++;
            }
        }

        return view('livewire.admin.submitted-requirements.submitted-requirements-index', [
            'submittedRequirements' => null,
            'groupedItems' => $groupedItems,
            'categories' => [
                'file' => 'File',
                'requirement' => 'Requirement',
            ],
            'activeSemester' => $activeSemester, // Pass to view if needed
        ]);
    }
}