<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use App\Models\Semester;
use App\Models\User;
use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;

class SubmittedRequirementsIndex extends Component
{
    use WithPagination;

    public $viewMode = 'grid';
    public $category = 'overview';
    public $search = '';
    
    // Navigation properties with URL binding
    #[Url]
    public $selectedRequirementId = null;
    
    #[Url]
    public $selectedUserId = null;
    
    #[Url]
    public $selectedCourseId = null;

    #[Url]
    public $selectedSemesterId = null;
    
    public $breadcrumb = [];
    public $availableSemesters = [];
    public $latestEndedSemester = null;

    protected $queryString = [
        'category' => ['except' => 'overview'],
        'search' => ['except' => ''],
        'selectedRequirementId' => ['except' => null],
        'selectedUserId' => ['except' => null],
        'selectedCourseId' => ['except' => null],
        'selectedSemesterId' => ['except' => null],
    ];

    public function mount()
    {
        $this->initializeSemesters();
        $this->updateBreadcrumb();
    }

    protected function initializeSemesters()
    {
        // Get available semesters (active and past semesters that have ended)
        $this->availableSemesters = Semester::where(function($query) {
                $query->where('is_active', true)
                    ->orWhere('end_date', '<=', now()->format('Y-m-d'));
            })
            ->where('start_date', '<=', now()->format('Y-m-d')) // Exclude future semesters
            ->orderBy('start_date', 'desc')
            ->get();

        // Get active semester
        $activeSemester = Semester::getActiveSemester();
        
        // Get latest ended semester if no active semester
        $this->latestEndedSemester = Semester::where('end_date', '<=', now()->format('Y-m-d'))
            ->orderBy('end_date', 'desc')
            ->first();

        // Set default selected semester
        if ($activeSemester) {
            $this->selectedSemesterId = $activeSemester->id;
        } elseif ($this->latestEndedSemester) {
            $this->selectedSemesterId = $this->latestEndedSemester->id;
        } else {
            $this->selectedSemesterId = $this->availableSemesters->first()?->id;
        }
    }

    public function switchView($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function setCategory($category)
    {
        $this->category = $category;
        $this->resetNavigation();
        $this->resetPage();
    }

    public function clearCategory()
    {
        $this->category = 'overview';
        $this->resetNavigation();
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    public function updatedSelectedSemesterId()
    {
        $this->resetNavigation();
        $this->resetPage();
    }
    
    // Navigation methods
    public function selectRequirement($requirementId)
    {
        $this->selectedRequirementId = $requirementId;
        $this->selectedUserId = null;
        $this->selectedCourseId = null;
        $this->updateBreadcrumb();
        $this->resetPage();
    }
    
    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $this->selectedCourseId = null;
        $this->updateBreadcrumb();
        $this->resetPage();
        
        // Force a component re-render to ensure state is updated
        $this->dispatch('$refresh');
        
        // Debug
        logger("User selected: " . $userId);
        logger("Current state after user selection: ", [
            'requirementId' => $this->selectedRequirementId,
            'userId' => $this->selectedUserId,
            'courseId' => $this->selectedCourseId
        ]);
    }
    
    public function selectCourse($courseId)
    {
        $this->selectedCourseId = $courseId;
        $this->updateBreadcrumb();
        
        // Debug: Log the current state
        logger("Course selected - Current state:", [
            'requirementId' => $this->selectedRequirementId,
            'userId' => $this->selectedUserId,
            'courseId' => $this->selectedCourseId
        ]);

        // Navigate to requirement view page with all context parameters
        // Remove the strict condition - just check if we have the minimum required data
        if ($this->selectedRequirementId && $this->selectedUserId && $this->selectedCourseId) {
            logger("Redirecting to requirement view");
            
            return redirect()->route('admin.submitted-requirements.requirement', [
                'requirement_id' => $this->selectedRequirementId,
                'user_id' => $this->selectedUserId,
                'course_id' => $this->selectedCourseId,
                'source' => 'requirement-category' 
            ]);
        } else {
            logger("Cannot redirect - missing parameters:", [
                'has_requirement' => !empty($this->selectedRequirementId),
                'has_user' => !empty($this->selectedUserId),
                'has_course' => !empty($this->selectedCourseId)
            ]);
        }
        
        // Make sure to reset page even if not redirecting
        $this->resetPage();
    }
    
    public function goBack($crumbType, $index = null)
    {
        if ($index !== null) {
            // Clear all selections after the clicked breadcrumb
            $this->clearSelectionsFromIndex($index);
        } else {
            // Fallback to type-based navigation
            $this->handleTypeBasedNavigation($crumbType);
        }
        
        $this->updateBreadcrumb();
        $this->resetPage();
    }

    protected function clearSelectionsFromIndex($index)
    {
        $breadcrumbTypes = array_column($this->breadcrumb, 'type');
        
        // Clear all selections that come after the clicked index
        for ($i = $index + 1; $i < count($breadcrumbTypes); $i++) {
            $typeToClear = $breadcrumbTypes[$i];
            
            switch ($typeToClear) {
                case 'requirement':
                    $this->selectedRequirementId = null;
                    break;
                case 'user':
                    $this->selectedUserId = null;
                    $this->selectedCourseId = null; // Clearing user also clears course
                    break;
                case 'course':
                    $this->selectedCourseId = null;
                    break;
            }
        }
    }

    protected function handleTypeBasedNavigation($crumbType)
    {
        switch ($crumbType) {
            case 'category':
                $this->resetNavigation();
                break;
            case 'requirement':
                $this->selectedRequirementId = null;
                $this->selectedUserId = null;
                $this->selectedCourseId = null;
                break;
            case 'user':
                $this->selectedUserId = null;
                $this->selectedCourseId = null;
                break;
            case 'course':
                $this->selectedCourseId = null;
                break;
        }
    }
    
    private function resetNavigation()
    {
        $this->selectedRequirementId = null;
        $this->selectedUserId = null;
        $this->selectedCourseId = null;
        $this->breadcrumb = [];
    }
    
    private function updateBreadcrumb()
    {
        $this->breadcrumb = [];
        
        // Always show category as first breadcrumb
        $this->breadcrumb[] = [
            'type' => 'category',
            'id' => $this->category,
            'name' => ucfirst($this->category)
        ];

        // Build navigation breadcrumbs
        if ($this->selectedRequirementId) {
            $requirement = Requirement::find($this->selectedRequirementId);
            $this->breadcrumb[] = [
                'type' => 'requirement',
                'id' => $this->selectedRequirementId,
                'name' => $requirement ? $requirement->name : 'Requirement'
            ];
        }
        
        if ($this->selectedUserId) {
            $user = User::find($this->selectedUserId);
            $this->breadcrumb[] = [
                'type' => 'user',
                'id' => $this->selectedUserId,
                'name' => $user ? $user->full_name : 'User'
            ];
        }
        
        if ($this->selectedCourseId) {
            $course = Course::find($this->selectedCourseId);
            $this->breadcrumb[] = [
                'type' => 'course',
                'id' => $this->selectedCourseId,
                'name' => $course ? $course->course_code : 'Course'
            ];
        }
    }

    /**
     * Get the current semester based on selection
     */
    protected function getCurrentSemester()
    {
        if ($this->selectedSemesterId) {
            return Semester::find($this->selectedSemesterId);
        }
        
        return Semester::getActiveSemester() ?: $this->latestEndedSemester;
    }

    /**
     * LEVEL 1: Get all requirements for selected semester
     */
    protected function getRequirements()
    {
        $currentSemester = $this->getCurrentSemester();
        
        if (!$currentSemester) {
            return collect();
        }

        $query = Requirement::where('semester_id', $currentSemester->id)
            ->orderByRaw('
                CASE 
                    WHEN JSON_LENGTH(requirement_type_ids) = 0 OR requirement_type_ids IS NULL THEN 1 
                    ELSE 0 
                END
            ') // Put empty arrays last
            ->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(requirement_type_ids, "$[0]")) AS UNSIGNED)') // Order by first type ID
            ->orderBy('name'); // Secondary order by name for same type IDs

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->get()->map(function($requirement) {
            // Count submissions using JOIN with requirement_submission_indicators
            $submissionCount = DB::table('submitted_requirements as sr')
                ->join('requirement_submission_indicators as rsi', function($join) use ($requirement) {
                    $join->on('sr.requirement_id', '=', 'rsi.requirement_id')
                        ->on('sr.user_id', '=', 'rsi.user_id')
                        ->on('sr.course_id', '=', 'rsi.course_id')
                        ->where('sr.requirement_id', $requirement->id);
                })
                ->where('sr.requirement_id', $requirement->id)
                ->count();
                
            return [
                'id' => $requirement->id,
                'name' => $requirement->name,
                'submission_count' => $submissionCount,
                'requirement_type_ids' => $requirement->requirement_type_ids, // Include for debugging
            ];
        });
    }

    /**
     * LEVEL 2: Get users who submitted the selected requirement (from submitted_requirements)
     */
    protected function getUsersForRequirement()
    {
        if (!$this->selectedRequirementId) {
            return collect();
        }

        // Get users who have SUBMITTED (marked as done) requirements
        $userIds = DB::table('requirement_submission_indicators as rsi')
            ->where('rsi.requirement_id', $this->selectedRequirementId)
            ->distinct()
            ->pluck('rsi.user_id');

        $query = User::whereIn('id', $userIds)
            ->with(['college'])
            ->orderBy('lastname')
            ->orderBy('firstname');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('firstname', 'like', '%'.$this->search.'%')
                ->orWhere('middlename', 'like', '%'.$this->search.'%')
                ->orWhere('lastname', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get()->map(function($user) {
            // Count ONLY submissions that are marked as done
            $submissionCount = DB::table('submitted_requirements as sr')
                ->join('requirement_submission_indicators as rsi', function($join) use ($user) {
                    $join->on('sr.requirement_id', '=', 'rsi.requirement_id')
                        ->on('sr.user_id', '=', 'rsi.user_id')
                        ->on('sr.course_id', '=', 'rsi.course_id')
                        ->where('rsi.user_id', $user->id);
                })
                ->where('sr.requirement_id', $this->selectedRequirementId)
                ->where('sr.user_id', $user->id)
                ->count();
                
            // Count courses with submitted requirements
            $courseCount = DB::table('submitted_requirements as sr')
                ->join('requirement_submission_indicators as rsi', function($join) use ($user) {
                    $join->on('sr.requirement_id', '=', 'rsi.requirement_id')
                        ->on('sr.user_id', '=', 'rsi.user_id')
                        ->on('sr.course_id', '=', 'rsi.course_id')
                        ->where('rsi.user_id', $user->id);
                })
                ->where('sr.requirement_id', $this->selectedRequirementId)
                ->where('sr.user_id', $user->id)
                ->distinct('sr.course_id')
                ->count('sr.course_id');
                
            return [
                'user' => $user,
                'submission_count' => $submissionCount,
                'course_count' => $courseCount
            ];
        });
    }

    /**
     * LEVEL 3: Get courses where user submitted the selected requirement (from submitted_requirements)
     */
    protected function getCoursesForUserRequirement()
    {
        if (!$this->selectedRequirementId || !$this->selectedUserId) {
            return collect();
        }

        // Get course IDs from SUBMITTED (marked as done) requirements
        $courseIds = DB::table('requirement_submission_indicators as rsi')
            ->where('rsi.requirement_id', $this->selectedRequirementId)
            ->where('rsi.user_id', $this->selectedUserId)
            ->distinct()
            ->pluck('rsi.course_id');

        $query = Course::whereIn('id', $courseIds)
            ->orderBy('course_code');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('course_code', 'like', '%'.$this->search.'%')
                ->orWhere('course_name', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get()->map(function($course) {
            // Get ONLY the submission that is marked as done
            $submission = DB::table('submitted_requirements as sr')
                ->join('requirement_submission_indicators as rsi', function($join) use ($course) {
                    $join->on('sr.requirement_id', '=', 'rsi.requirement_id')
                        ->on('sr.user_id', '=', 'rsi.user_id')
                        ->on('sr.course_id', '=', 'rsi.course_id')
                        ->where('rsi.course_id', $course->id);
                })
                ->where('sr.requirement_id', $this->selectedRequirementId)
                ->where('sr.user_id', $this->selectedUserId)
                ->where('sr.course_id', $course->id)
                ->select('sr.*') // Get the submitted_requirements data
                ->first();
                
            // Convert to SubmittedRequirement model if needed
            $submissionModel = $submission ? SubmittedRequirement::find($submission->id) : null;
                
            return [
                'course' => $course,
                'submission' => $submissionModel,
                'status' => $submissionModel ? $submissionModel->status : 'not_submitted'
            ];
        });
    }

    protected function getCategories()
    {
        return [
            'overview' => 'Overview',
            'requirement' => 'Requirement',
        ];
    }

    public function debugState()
    {
        logger([
            'category' => $this->category,
            'selectedRequirementId' => $this->selectedRequirementId,
            'selectedUserId' => $this->selectedUserId,
            'selectedCourseId' => $this->selectedCourseId,
            'selectedSemesterId' => $this->selectedSemesterId,
            'viewMode' => $this->viewMode,
            'search' => $this->search
        ]);
    }

    public function updated($property)
    {
        if (in_array($property, ['selectedRequirementId', 'selectedUserId', 'selectedCourseId', 'viewMode', 'search', 'selectedSemesterId'])) {
            $this->debugState();
        }
    }

    public function selectRequirementFromBox($requirementId)
    {
        $this->selectRequirement($requirementId);
    }

    public function render()
    {
        $currentSemester = $this->getCurrentSemester();
        
        if (!$currentSemester) {
            return view('livewire.admin.submitted-requirements.submitted-requirements-index', [
                'activeSemester' => null,
                'selectedSemester' => null,
                'categories' => $this->getCategories(),
                'requirements' => collect(),
                'usersForRequirement' => collect(),
                'coursesForUserRequirement' => collect(),
            ]);
        }

        // Only handle requirement category logic here
        if ($this->category === 'requirement') {
            if ($this->selectedRequirementId) {
                if ($this->selectedUserId) {
                    // LEVEL 3: Courses for specific user and requirement
                    $requirements = collect();
                    $usersForRequirement = collect();
                    $coursesForUserRequirement = $this->getCoursesForUserRequirement();
                } else {
                    // LEVEL 2: Users for specific requirement
                    $requirements = collect();
                    $usersForRequirement = $this->getUsersForRequirement();
                    $coursesForUserRequirement = collect();
                }
            } else {
                // LEVEL 1: All requirements
                $requirements = $this->getRequirements();
                $usersForRequirement = collect();
                $coursesForUserRequirement = collect();
            }
        } else {
            // For overview category, we'll use a separate component
            $requirements = collect();
            $usersForRequirement = collect();
            $coursesForUserRequirement = collect();
        }

        return view('livewire.admin.submitted-requirements.submitted-requirements-index', [
            'activeSemester' => $currentSemester,
            'selectedSemester' => $currentSemester,
            'categories' => $this->getCategories(),
            'requirements' => $requirements,
            'usersForRequirement' => $usersForRequirement,
            'coursesForUserRequirement' => $coursesForUserRequirement,
        ]);
    }
}