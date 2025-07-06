<?php

namespace App\Livewire\user\PendingTask;

use Livewire\Component;
use App\Models\Requirement;
use Livewire\WithPagination;
use App\Models\SubmittedRequirement;

class RequirementsList extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $statusFilter = '';
    public $sortField = 'due';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'due'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount()
    {
        $this->statuses = SubmittedRequirement::statuses();
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function render()
    {
        $requirements = Requirement::query()
            ->where('target_id', auth()->id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->whereHas('userSubmissions', function($q) {
                    $q->where('status', $this->statusFilter);
                });
            })
            ->withCount(['userSubmissions as under_review_count' => function($q) {
                $q->where('status', SubmittedRequirement::STATUS_UNDER_REVIEW);
            }])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.user.pending-task.requirements-list', [
            'requirements' => $requirements,
            'statuses' => SubmittedRequirement::statuses(),
        ]);
    }
}