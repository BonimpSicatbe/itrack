<?php

namespace App\Livewire\user\Requirements;

use Livewire\Component;
use App\Models\Requirement;
use App\Models\Semester;
use Livewire\WithPagination;
use App\Models\RequirementSubmissionIndicator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequirementsList extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $completionFilter = 'all';
    public $sortField = 'due';
    public $sortDirection = 'asc';
    public $completionStatuses = [];
    public $viewMode = 'list';
    
    // Track submitted requirements in real-time
    public $submittedRequirements = [];
    
    // New properties for direct navigation from notifications
    public $highlightedRequirement = null;
    public $selectedRequirement = null;
    public $selectedRequirementData = null;

    protected $queryString = [
    'search' => ['except' => ''],
    'completionFilter' => ['except' => 'all'],
    'sortField' => ['except' => 'due'],
    'sortDirection' => ['except' => 'asc'],
    'viewMode' => ['except' => 'list'],
    'requirement' => ['except' => '', 'as' => 'req'], // Add query string support for requirement ID
    ];

    protected $listeners = [
        'requirementUpdated' => '$refresh',
        'showRequirementDetail' => 'showRequirementDetail',
    ];

    public function mount()
    {
        $this->completionStatuses = [
            'all' => 'All Requirements',
            'submitted' => 'Submitted',
            'pending' => 'Pending Submission'
        ];
        
        // Check if a specific requirement should be highlighted from URL parameter
        $this->highlightedRequirement = request()->get('requirement');
        
        // Initialize submitted requirements on mount
        $this->loadSubmittedRequirements();
        
        // If there's a highlighted requirement from URL, auto-select it
        if ($this->highlightedRequirement) {
            $this->selectRequirement($this->highlightedRequirement);
        }
    }

    
    
    // New method to handle requirement selection from notifications
        public function selectRequirement($requirementId)
        {
            $this->selectedRequirement = $requirementId;
            
            // Load the requirement details
            $requirement = Requirement::with(['media', 'userSubmissions'])
                ->where('id', $requirementId)
                ->first();
                
            if ($requirement) {
                $this->selectedRequirementData = $requirement;
                
                // Clear the highlight after selection
                $this->highlightedRequirement = null;
                
                // Update the URL without the requirement parameter
                $this->js('window.history.replaceState({}, "", "/user/requirements")');
                
                // Dispatch event to show requirement detail modal
                $this->dispatch('showRequirementDetail', requirementId: $requirementId);
            }
        }
    
    // Method to handle requirement detail display
    public function showRequirementDetail($requirementId)
    {
        $this->selectRequirement($requirementId);
        
        // Dispatch event to show requirement detail modal
        $this->dispatch('showRequirementDetail', requirementId: $requirementId);
    }

    // Fixed completion filter method
    public function setCompletionFilter($filter)
    {
        // Validate filter value
        $validFilters = ['all', 'submitted', 'pending'];
        if (!in_array($filter, $validFilters)) {
            $filter = 'all';
        }
        
        $this->completionFilter = $filter;
        $this->resetPage();
        
        // Refresh submitted requirements to ensure accuracy
        $this->loadSubmittedRequirements();
        
        Log::info('Completion filter updated', [
            'new_filter' => $this->completionFilter,
            'user_id' => Auth::id()
        ]);
    }

    public function updatedHighlightedRequirement()
    {
        if ($this->highlightedRequirement) {
            // Automatically open the modal for the highlighted requirement
            $this->dispatch('showRequirementDetail', requirementId: $this->highlightedRequirement);
        }
    }
    
    // Handle completion filter changes
    public function updatedCompletionFilter()
    {
        $this->resetPage();
        $this->loadSubmittedRequirements();
    }
    
    // Handle view mode changes
    public function updatedViewMode()
    {
        $this->resetPage();
    }
    
    public function loadSubmittedRequirements()
    {
        $user = Auth::user();
        if ($user) {
            $this->submittedRequirements = RequirementSubmissionIndicator::where('user_id', $user->id)
                ->pluck('requirement_id')
                ->toArray();
        }
    }

    public function loadMore()
    {
        $this->perPage += 10;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        Log::info('Sort button clicked', ['field' => $field, 'current_sort' => $this->sortField]);
        
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
        
        Log::info('Sort updated', ['new_field' => $this->sortField, 'direction' => $this->sortDirection]);
        
        // Reset pagination to first page when sorting
        $this->resetPage();
    }

    public function markAsDone($requirementId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                session()->flash('error', 'You must be logged in to perform this action.');
                return;
            }

            // Use DB transaction for data integrity
            DB::beginTransaction();
            
            // Validate the requirement exists and belongs to the user
            $requirement = $user->requirements()->find($requirementId);
            
            if (!$requirement) {
                DB::rollBack();
                session()->flash('error', 'Requirement not found or you do not have permission to modify it.');
                return;
            }

            // Check if already submitted to prevent duplicates
            $existingSubmission = RequirementSubmissionIndicator::where('requirement_id', $requirementId)
                ->where('user_id', $user->id)
                ->first();

            if ($existingSubmission) {
                DB::rollBack();
                session()->flash('error', 'This requirement has already been marked as done.');
                return;
            }
            
            // Create a submission indicator
            $submission = RequirementSubmissionIndicator::create([
                'requirement_id' => $requirementId,
                'user_id' => $user->id,
                'submitted_at' => now(),
            ]);

            if ($submission) {
                DB::commit();
                
                // Update the local submitted requirements array immediately
                $this->submittedRequirements[] = $requirementId;
                
                session()->flash('message', 'Requirement marked as done successfully!');
                
                Log::info('Requirement marked as done', [
                    'requirement_id' => $requirementId,
                    'user_id' => $user->id,
                    'submission_id' => $submission->id
                ]);
                
                // Refresh the component to update the UI
                $this->dispatch('$refresh');
            } else {
                DB::rollBack();
                session()->flash('error', 'Failed to mark requirement as done. Please try again.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking requirement as done', [
                'requirement_id' => $requirementId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'An error occurred while marking the requirement as done. Please try again.');
        }
    }

    public function markAsUndone($requirementId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                session()->flash('error', 'You must be logged in to perform this action.');
                return;
            }

            // Use DB transaction for data integrity
            DB::beginTransaction();
            
            // Validate the requirement exists and belongs to the user
            $requirement = $user->requirements()->find($requirementId);
            
            if (!$requirement) {
                DB::rollBack();
                session()->flash('error', 'Requirement not found or you do not have permission to modify it.');
                return;
            }
            
            // Delete the submission indicator
            $deleted = RequirementSubmissionIndicator::where('requirement_id', $requirementId)
                ->where('user_id', $user->id)
                ->delete();
            
            if ($deleted > 0) {
                DB::commit();
                
                // Remove from local submitted requirements array immediately
                $this->submittedRequirements = array_values(
                    array_diff($this->submittedRequirements, [$requirementId])
                );
                
                session()->flash('message', 'Requirement marked as undone successfully!');
                
                Log::info('Requirement marked as undone', [
                    'requirement_id' => $requirementId,
                    'user_id' => $user->id,
                    'deleted_count' => $deleted
                ]);
                
                // Refresh the component to update the UI
                $this->dispatch('$refresh');
            } else {
                DB::rollBack();
                session()->flash('error', 'No submission record found to undo.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking requirement as undone', [
                'requirement_id' => $requirementId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'An error occurred while marking the requirement as undone. Please try again.');
        }
    }

    public function isRequirementSubmitted($requirementId)
    {
        // Check both the local array and database to ensure accuracy
        if (in_array($requirementId, $this->submittedRequirements)) {
            return true;
        }
        
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        $exists = RequirementSubmissionIndicator::where('requirement_id', $requirementId)
            ->where('user_id', $user->id)
            ->exists();
            
        // If it exists in DB but not in local array, add it
        if ($exists && !in_array($requirementId, $this->submittedRequirements)) {
            $this->submittedRequirements[] = $requirementId;
        }
        
        return $exists;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        
        if (!$user) {
            return view('livewire.user.requirements.requirements-list', [
                'requirements' => collect()->paginate($this->perPage),
                'completionStatuses' => $this->completionStatuses,
            ]);
        }

        $userId = $user->id;

        // Get active semester
        $activeSemester = Semester::getActiveSemester();
        
        if (!$activeSemester) {
            return view('livewire.user.requirements.requirements-list', [
                'requirements' => collect()->paginate($this->perPage),
                'completionStatuses' => $this->completionStatuses,
            ]);
        }

        // Refresh submitted requirements to ensure accuracy
        $this->loadSubmittedRequirements();

        $query = $user->requirements()
            ->where('semester_id', $activeSemester->id); // Only requirements from active semester

        // Apply search filter
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Fixed completion filter logic
        if ($this->completionFilter === 'submitted') {
            // Only show submitted requirements
            $query->whereHas('submissionIndicators', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        } elseif ($this->completionFilter === 'pending') {
            // Only show pending (not submitted) requirements
            $query->whereDoesntHave('submissionIndicators', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }
        // If completionFilter is 'all', no additional filtering is applied

        // Apply sorting with proper validation
        $allowedSortFields = ['name', 'due', 'priority', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSortFields) ? $this->sortField : 'due';
        $sortDirection = in_array($this->sortDirection, ['asc', 'desc']) ? $this->sortDirection : 'asc';

        if ($sortField === 'priority') {
            // Custom sorting for priority field
            $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low') " . ($sortDirection === 'asc' ? 'ASC' : 'DESC'));
        } else {
            // Default sorting for other fields
            $query->orderBy($sortField, $sortDirection);
        }

        // Use pagination instead of get()
        $requirements = $query->paginate($this->perPage);

        Log::info('Render requirements', [
            'completion_filter' => $this->completionFilter,
            'submitted_count' => count($this->submittedRequirements),
            'total_requirements' => $requirements->total(),
            'search' => $this->search,
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'highlighted_requirement' => $this->highlightedRequirement
        ]);

        return view('livewire.user.requirements.requirements-list', [
            'requirements' => $requirements,
            'completionStatuses' => $this->completionStatuses,
        ]);
    }
}