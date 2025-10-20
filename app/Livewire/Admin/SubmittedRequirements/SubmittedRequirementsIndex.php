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

    public $viewMode = 'list';
    public $category = 'overview';
    public $search = '';
    
    // Navigation properties with URL binding
    #[Url]
    public $selectedRequirementId = null;
    
    #[Url]
    public $selectedUserId = null;
    
    #[Url]
    public $selectedCourseId = null;
    
    public $breadcrumb = [];

    protected $queryString = [
        'category' => ['except' => 'overview'],
        'search' => ['except' => ''],
        'selectedRequirementId' => ['except' => null],
        'selectedUserId' => ['except' => null],
        'selectedCourseId' => ['except' => null],
    ];

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
        
        // Debug
        logger("User selected: " . $userId);
        logger("Current state: ", [
            'requirementId' => $this->selectedRequirementId,
            'userId' => $this->selectedUserId,
            'courseId' => $this->selectedCourseId
        ]);
    }
    
    public function selectCourse($courseId)
    {
        $this->selectedCourseId = $courseId;
        $this->updateBreadcrumb();
        
        // Navigate to requirement view page with all context parameters
        if ($this->selectedRequirementId && $this->selectedUserId && $this->selectedCourseId) {
            return redirect()->route('admin.submitted-requirements.requirement', [
                'requirement_id' => $this->selectedRequirementId,
                'user_id' => $this->selectedUserId,
                'course_id' => $this->selectedCourseId
            ]);
        }
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

    public function mount()
    {
        $this->updateBreadcrumb();
    }

    public function render()
    {
        $activeSemester = Semester::getActiveSemester();
        
        if (!$activeSemester) {
            return view('livewire.admin.submitted-requirements.submitted-requirements-index', [
                'activeSemester' => null,
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
                $requirements = $this->getRequirements($activeSemester);
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
            'activeSemester' => $activeSemester,
            'categories' => $this->getCategories(),
            'requirements' => $requirements,
            'usersForRequirement' => $usersForRequirement,
            'coursesForUserRequirement' => $coursesForUserRequirement,
        ]);
    }

    /**
     * LEVEL 1: Get all requirements for active semester
     */
    protected function getRequirements($activeSemester)
    {
        $query = Requirement::where('semester_id', $activeSemester->id)
            ->orderBy('id');

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
            'viewMode' => $this->viewMode,
            'search' => $this->search
        ]);
    }

    public function updated($property)
    {
        if (in_array($property, ['selectedRequirementId', 'selectedUserId', 'selectedCourseId', 'viewMode', 'search'])) {
            $this->debugState();
        }
    }
}