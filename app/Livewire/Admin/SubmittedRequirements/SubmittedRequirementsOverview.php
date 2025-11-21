<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use App\Models\Requirement;
use App\Models\Semester;
use App\Models\User;
use App\Models\CourseAssignment;
use App\Models\RequirementSubmissionIndicator;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class SubmittedRequirementsOverview extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 5;
    public $selectedSemesterId = null;

    #[Url] 
    public $page = 1;

    protected $listeners = ['refreshOverview' => '$refresh'];

    public function mount($selectedSemesterId = null)
    {
        $this->selectedSemesterId = $selectedSemesterId;
    }

    /**
     * Get the current semester based on selection
     */
    protected function getCurrentSemester()
    {
        if ($this->selectedSemesterId) {
            return Semester::find($this->selectedSemesterId);
        }
        
        return Semester::getActiveSemester();
    }

    public function render()
    {
        $currentSemester = $this->getCurrentSemester();
        
        if (!$currentSemester) {
            return view('livewire.admin.submitted-requirements.submitted-requirements-overview', [
                'overviewData' => []
            ]);
        }

        $overviewData = $this->getOverviewData($currentSemester);

        return view('livewire.admin.submitted-requirements.submitted-requirements-overview', [
            'overviewData' => $overviewData
        ]);
    }

    protected function getOverviewData($currentSemester)
    {
        // Get all requirements for the current semester
        $requirements = Requirement::where('semester_id', $currentSemester->id)
            ->orderByRaw('
                CASE 
                    WHEN JSON_LENGTH(requirement_type_ids) = 0 THEN 999999
                    ELSE CAST(JSON_UNQUOTE(JSON_EXTRACT(requirement_type_ids, "$[0]")) AS UNSIGNED)
                END ASC
            ')
            ->orderBy('name')
            ->get(); 

        // Get all non-admin users with search functionality - ONLY ACTIVE USERS
        $usersQuery = User::where('is_active', true) // Only active users
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->with(['college'])
            ->orderBy('lastname')
            ->orderBy('firstname');

        // Apply search filter - UPDATED TO INCLUDE RANK, PROGRAM, AND COURSE
        if ($this->search) {
            $usersQuery->where(function($q) {
                $q->where('firstname', 'like', '%'.$this->search.'%')
                ->orWhere('middlename', 'like', '%'.$this->search.'%')
                ->orWhere('lastname', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')
                ->orWhere('position', 'like', '%'.$this->search.'%') // Rank/Position
                ->orWhereHas('college', function($collegeQuery) {
                    $collegeQuery->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('courseAssignments.course.program', function($programQuery) {
                    $programQuery->where('program_name', 'like', '%'.$this->search.'%')
                                ->orWhere('program_code', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('courseAssignments.course', function($courseQuery) {
                    $courseQuery->where('course_name', 'like', '%'.$this->search.'%')
                               ->orWhere('course_code', 'like', '%'.$this->search.'%');
                });
            });
        }

        // Use pagination instead of get()
        $users = $usersQuery->paginate($this->perPage);

        // Get course assignments for the paginated users
        $courseAssignments = CourseAssignment::whereIn('professor_id', $users->pluck('id'))
            ->where('semester_id', $currentSemester->id)
            ->with(['course' => function($query) {
                $query->with('program'); // Eager load program relationship
            }])
            ->get()
            ->groupBy('professor_id');

        // Get submission indicators for the paginated users
        $submissionIndicators = RequirementSubmissionIndicator::whereIn('user_id', $users->pluck('id'))
            ->whereIn('requirement_id', $requirements->pluck('id'))
            ->with(['requirement', 'course'])
            ->get()
            ->groupBy(['user_id', 'requirement_id', 'course_id']);

        // Prepare user courses data
        $userCoursesData = [];
        foreach ($users as $user) {
            $courses = $this->getUserCourses($user->id, $courseAssignments);
            $userCoursesData[$user->id] = $courses;
        }

        return [
            'requirements' => $requirements,
            'users' => $users, // This is now a paginator instance
            'courseAssignments' => $courseAssignments,
            'submissionIndicators' => $submissionIndicators,
            'userCoursesData' => $userCoursesData,
            'semester' => $currentSemester
        ];
    }

    // Add method to update per page
    public function updatedPerPage()
    {
        $this->resetPage(); // Reset to first page when changing items per page
    }

    // Add method to handle search with pagination reset
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getUserCourses($userId, $courseAssignments)
    {
        if (!isset($courseAssignments[$userId])) {
            return collect();
        }
        
        return $courseAssignments[$userId]->pluck('course')->filter();
    }

    /**
     * Check if a specific course is assigned to a requirement based on the course's program
     */
    public function isCourseAssignedToRequirement($course, $requirement)
    {
        if (!$course || !$course->program_id) {
            return false;
        }

        try {
            // Handle the assigned_to field - it could be a JSON string or already decoded array
            $assignedTo = $requirement->assigned_to;
            
            $assignedPrograms = [];
            
            if (is_string($assignedTo)) {
                // Try to decode JSON string
                $decoded = json_decode($assignedTo, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $assignedPrograms = $decoded['programs'] ?? [];
                }
            } elseif (is_array($assignedTo)) {
                // It's already an array
                $assignedPrograms = $assignedTo['programs'] ?? [];
            }
            
            if (empty($assignedPrograms)) {
                return false;
            }
            
            // Check if this specific course's program is in the assigned programs
            return in_array($course->program_id, $assignedPrograms);
            
        } catch (\Exception $e) {
            // Log error or handle silently
            \Log::error('Error checking requirement assignment for course', [
                'course_id' => $course->id,
                'requirement_id' => $requirement->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function hasUserSubmittedForCourse($userId, $requirementId, $courseId, $submissionIndicators)
    {
        return isset($submissionIndicators[$userId][$requirementId][$courseId]) && 
               $submissionIndicators[$userId][$requirementId][$courseId]->isNotEmpty();
    }

    public function getSubmissionStatusForCourse($userId, $requirementId, $courseId, $submissionIndicators)
    {
        if (!$this->hasUserSubmittedForCourse($userId, $requirementId, $courseId, $submissionIndicators)) {
            return 'not_submitted';
        }

        return 'submitted';
    }

    /**
     * More efficient version that pre-processes course assignments and checks requirement assignment
     */
    public function getSubmissionDisplay($userId, $requirementId, $courseId, $submissionIndicators, $requirement, $userCoursesData)
    {
        // Get all user courses and find the current one
        $userCourses = $userCoursesData[$userId] ?? collect();
        
        // Find the specific course we're checking
        $currentCourse = null;
        foreach ($userCourses as $course) {
            if ($course->id == $courseId) {
                $currentCourse = $course;
                break;
            }
        }
        
        // Check if this specific course is assigned to the requirement
        if (!$currentCourse || !$this->isCourseAssignedToRequirement($currentCourse, $requirement)) {
            return 'N/A';
        }

        // If assigned, check submission status
        $status = $this->getSubmissionStatusForCourse($userId, $requirementId, $courseId, $submissionIndicators);
        
        return $status === 'not_submitted' ? 'No Submission' : 'Submitted';
    }

    /**
     * Updated badge classes to include "Not Assigned" state
     */
    public function getStatusBadgeClass($status)
    {
        return match(strtolower($status)) {
            'submitted' => 'bg-green-100 text-green-800',
            'no submission' => 'bg-amber-100 text-amber-600',
            'n/a' => 'bg-gray-200 text-gray-400',
            default => 'bg-gray-100 text-gray-500'
        };
    }

    public function getUserRowspan($userId, $userCoursesData)
    {
        $courses = $userCoursesData[$userId] ?? collect();
        return max(1, $courses->count());
    }

    public function getSubmissionUrl($requirementId, $userId, $courseId)
    {
        return route('admin.submitted-requirements.requirement', [
            'requirement_id' => $requirementId,
            'user_id' => $userId,
            'course_id' => $courseId,
            'source' => 'overview',
            'page' => $this->page 
        ]);
    }
}