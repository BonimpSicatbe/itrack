<?php

namespace App\Livewire\user\PendingTask;

use Livewire\Component;
use App\Models\Requirement;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\SubmittedRequirement;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RequirementsList extends Component
{
    use WithPagination, WithFileUploads;

    public $perPage = 10;
    public $search = '';
    public $statusFilter = '';
    public $sortField = 'due';
    public $sortDirection = 'asc';
    public $selectedRequirement = null;
    
    public $file;
    public $uploading = false;
    public $submissionNotes = '';

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

    public function rules()
    {
        return [
            'file' => 'required|file|max:10240', // 10MB max
            'submissionNotes' => 'nullable|string|max:500',
        ];
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

    public function selectRequirement($requirementId)
    {
        $this->selectedRequirement = Requirement::with([
            'guides',
            'userSubmissions' => function($query) {
                $query->with(['submissionFile', 'reviewer'])
                    ->latest();
            }
        ])->find($requirementId);
        
        if ($this->selectedRequirement) {
            $this->selectedRequirement->due = \Carbon\Carbon::parse($this->selectedRequirement->due);
        }
        
        $this->reset(['file', 'submissionNotes']);
    }

    public function closeDetail()
    {
        $this->selectedRequirement = null;
        $this->reset(['file', 'submissionNotes']);
    }

    public function submitRequirement()
    {
        $this->validate([
            'file' => 'required|file|max:10240',
            'submissionNotes' => 'nullable|string|max:500',
        ]);

        $this->uploading = true;
        
        try {
            DB::transaction(function () {
                $submission = SubmittedRequirement::create([
                    'requirement_id' => $this->selectedRequirement->id,
                    'user_id' => auth()->id(),
                    'status' => SubmittedRequirement::STATUS_UNDER_REVIEW,
                    'admin_notes' => $this->submissionNotes,
                ]);

                $submission->addMedia($this->file->getRealPath())
                    ->usingName($this->file->getClientOriginalName())
                    ->usingFileName($this->file->getClientOriginalName())
                    ->toMediaCollection('submission_files');

                // Update requirement status if this is the first submission
                if ($this->selectedRequirement->status === SubmittedRequirement::STATUS_PENDING) {
                    $this->selectedRequirement->update(['status' => SubmittedRequirement::STATUS_UNDER_REVIEW]);
                }
            });

            $this->dispatch('notify', 
                type: 'success', 
                message: 'Requirement submitted successfully! Status: Under Review'
            );
            
            $this->reset(['file', 'submissionNotes']);
            $this->selectedRequirement->refresh();
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                message: 'Submission failed: '.$e->getMessage()
            );
        } finally {
            $this->uploading = false;
        }
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
            ->withCount(['userSubmissions as pending_count' => function($q) {
                $q->where('status', SubmittedRequirement::STATUS_PENDING);
            }])
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