<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Semester;
use App\Models\College;
use App\Models\Program;
use App\Models\Course;
use App\Models\Requirement;
use App\Models\User;
use App\Models\CourseAssignment;
use App\Models\RequirementSubmissionIndicator;

class ReportIndex extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 5; // Add this line
    
    // Filter options
    public $selectedSemester = '';
    public $selectedProgram = '';
    public $selectedCourse = '';
    
    // Data for filters
    public $semesters = [];
    public $programs = [];
    public $courses = [];

    public function mount()
    {
        $this->loadFilterData();
        
        // Set default active semester
        $activeSemester = Semester::getActiveSemester();
        if ($activeSemester) {
            $this->selectedSemester = $activeSemester->id;
        }
    }

    public function loadFilterData()
    {
        $today = now()->format('Y-m-d');
        
        $this->semesters = Semester::where('start_date', '<=', $today)
            ->orderBy('start_date', 'desc')
            ->get();
            
        $this->programs = Program::orderBy('program_name')->get();
        $this->courses = Course::with('program')->orderBy('course_name')->get();
    }

    // Add these pagination methods
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSelectedSemester()
    {
        // Reset program when semester changes
        $this->selectedProgram = '';
        $this->selectedCourse = '';
        $this->resetPage();
    }

    public function updatedSelectedProgram()
    {
        $this->selectedCourse = '';
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function generateReport()
    {
        // Validate that a semester is selected
        if (!$this->selectedSemester) {
            session()->flash('error', 'Please select a semester to generate the report.');
            return;
        }

        // Redirect to the preview route with filter parameters
        $params = [
            'semester_id' => $this->selectedSemester,
        ];

        if ($this->selectedProgram) {
            $params['program_id'] = $this->selectedProgram;
        }

        if ($this->search) {
            $params['search'] = $this->search;
        }

        // Generate URL for the report preview
        $previewUrl = route('admin.reports.preview-semester', $params);

        // Open in new tab using JavaScript
        $this->dispatch('open-new-tab', url: $previewUrl);
    }

    protected function getOverviewData()
    {
        // Use selected semester or get active semester
        $semester = $this->selectedSemester 
            ? Semester::find($this->selectedSemester)
            : Semester::getActiveSemester();
        
        if (!$semester) {
            // Return empty paginator instead of empty collection
            return [
                'requirements' => collect(),
                'users' => User::where('id', 0)->paginate($this->perPage), // Empty paginator
                'courseAssignments' => collect(),
                'submissionIndicators' => collect(),
                'userCoursesData' => [],
                'semester' => null
            ];
        }

        // Get all requirements for the selected semester
        $requirements = Requirement::where('semester_id', $semester->id)
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

        // Get course assignments for ALL users (for filtering) - WITH PROGRAM FILTERING
        $allUsers = $usersQuery->get();
        
        $courseAssignmentsQuery = CourseAssignment::whereIn('professor_id', $allUsers->pluck('id'))
            ->where('semester_id', $semester->id)
            ->with(['course' => function($query) {
                // Apply program filter to courses if a program is selected
                if ($this->selectedProgram) {
                    $query->where('program_id', $this->selectedProgram);
                }
                $query->with('program'); // Eager load program relationship
            }]);

        $courseAssignments = $courseAssignmentsQuery->get()
            ->groupBy('professor_id');

        // Filter out course assignments where the course doesn't match the program filter
        if ($this->selectedProgram) {
            foreach ($courseAssignments as $professorId => $assignments) {
                $filteredAssignments = $assignments->filter(function($assignment) {
                    return $assignment->course && $assignment->course->program_id == $this->selectedProgram;
                });
                
                if ($filteredAssignments->isEmpty()) {
                    unset($courseAssignments[$professorId]);
                } else {
                    $courseAssignments[$professorId] = $filteredAssignments;
                }
            }
        }

        // Apply program filter to users query
        if ($this->selectedProgram) {
            $usersQuery->whereHas('courseAssignments.course', function($query) {
                $query->where('program_id', $this->selectedProgram);
            });
        }

        // Paginate users - use perPage property instead of hardcoded value
        $users = $usersQuery->paginate($this->perPage); // Changed from 7 to $this->perPage

        // Get submission indicators for the paginated users and filtered courses
        $submissionIndicators = RequirementSubmissionIndicator::whereIn('user_id', $users->pluck('id'))
            ->whereIn('requirement_id', $requirements->pluck('id'))
            ->with(['requirement', 'course'])
            ->get()
            ->groupBy(['user_id', 'requirement_id', 'course_id']);

        // Prepare user courses data - only include courses that match the program filter
        $userCoursesData = [];
        foreach ($users as $user) {
            $courses = $this->getUserCourses($user->id, $courseAssignments);
            
            // Apply program filter to courses
            if ($this->selectedProgram) {
                $courses = $courses->filter(function($course) {
                    return $course->program_id == $this->selectedProgram;
                });
            }
            
            $userCoursesData[$user->id] = $courses;
        }

        return [
            'requirements' => $requirements,
            'users' => $users,
            'courseAssignments' => $courseAssignments,
            'submissionIndicators' => $submissionIndicators,
            'userCoursesData' => $userCoursesData,
            'semester' => $semester
        ];
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
     * More efficient version that pre-processes course assignments
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

    public function render()
    {
        $overviewData = $this->getOverviewData();

        return view('livewire.admin.report.report-index', [
            'overviewData' => $overviewData
        ]);
    }
}