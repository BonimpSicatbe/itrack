<?php

namespace App\Livewire\user\Requirements;

use Livewire\Component;
use App\Models\Requirement;
use Livewire\WithPagination;
use App\Models\SubmittedRequirement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RequirementsList extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $statusFilter = '';
    public $sortField = 'due';
    public $sortDirection = 'asc';
    public $statuses = [];

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
        $user = Auth::user();

        $requirements = $user->requirements()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->whereHas('userSubmissions', function ($q) {
                    $q->where('status', $this->statusFilter);
                });
            })
            ->withCount(['userSubmissions as under_review_count' => function ($q) {
                $q->where('status', SubmittedRequirement::STATUS_UNDER_REVIEW);
            }])
            ->orderBy($this->sortField, $this->sortDirection)->get();
        // ->paginate($this->perPage);

        // $requirements = Requirement::query()
        //     ->where(function($query) use ($user) {
        //         $query->where(function($q) use ($user) {
        //             $q->where('target', 'college')
        //               ->where('target_id', $user->college_id);
        //         })
        //         ->orWhere(function($q) use ($user) {
        //             $q->where('target', 'department')
        //               ->where('target_id', $user->department_id);
        //         });
        //     })
        //     ->when($this->search, function ($query) {
        //         $query->where(function ($q) {
        //             $q->where('name', 'like', '%'.$this->search.'%')
        //             ->orWhere('description', 'like', '%'.$this->search.'%');
        //         });
        //     })
        //     ->when($this->statusFilter, function ($query) {
        //         $query->whereHas('userSubmissions', function($q) {
        //             $q->where('status', $this->statusFilter);
        //         });
        //     })
        //     ->withCount(['userSubmissions as under_review_count' => function($q) {
        //         $q->where('status', SubmittedRequirement::STATUS_UNDER_REVIEW);
        //     }])
        //     ->orderBy($this->sortField, $this->sortDirection)
        //     ->paginate($this->perPage);

        return view('livewire.user.requirements.requirements-list', [
            'requirements' => $requirements,
            'statuses' => SubmittedRequirement::statuses(),
        ]);
    }
}
