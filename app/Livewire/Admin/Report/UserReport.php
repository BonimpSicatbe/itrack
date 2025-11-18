<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Semester;
use App\Models\User;
use App\Models\CourseAssignment;
use App\Models\Requirement;
use App\Models\SubmittedRequirement;

class UserReport extends Component
{
    use WithPagination;
    
    public $search = '';
    public $selectedUser = null;
    public $selectedSemester = '';
    public $semesters = [];
    public $userSearchResults = [];
    public $showUserDropdown = false;
    public $submissionFilter = 'all'; // New property for submission filter

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
    }

    public function updatedSearch()
    {
        if ($this->search) {
            // If there's search text, filter results
            $this->searchUsers();
        } else {
            // If search is empty, show all users
            $this->showAllUsers();
        }
    }

    public function showAllUsers()
    {
        $this->userSearchResults = User::where('is_active', true)
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->limit(50)
            ->get();
        
        $this->showUserDropdown = true;
    }

    public function searchUsers()
    {
        $this->userSearchResults = User::where('is_active', true)
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->where(function($query) {
                $query->where('firstname', 'like', '%'.$this->search.'%')
                    ->orWhere('middlename', 'like', '%'.$this->search.'%')
                    ->orWhere('lastname', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->limit(50)
            ->get();
        
        $this->showUserDropdown = true;
    }

    public function showDropdown()
    {
        // When input is focused, show all users if no search text
        if (empty($this->search)) {
            $this->showAllUsers();
        } else {
            $this->searchUsers();
        }
    }

    public function selectUser($userId)
    {
        // Close dropdown first
        $this->showUserDropdown = false;
        
        // Then select the user
        $this->selectedUser = User::with('college')->find($userId);
        $this->search = $this->selectedUser->full_name;
        $this->userSearchResults = [];
    }

    public function clearUserSelection()
    {
        $this->selectedUser = null;
        $this->search = '';
        $this->userSearchResults = [];
        $this->showUserDropdown = false;
        
        // Force a re-render to ensure the view updates
        $this->dispatch('user-cleared');
    }

    public function updatedSelectedSemester()
    {
        $this->resetPage();
    }

    // New method to handle submission filter change
    public function updatedSubmissionFilter()
    {
        $this->resetPage();
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

    public function getSubmissionSummary()
    {
        if (!$this->selectedUser || !$this->selectedSemester) {
            return [
                'total_requirements' => 0,
                'submitted_count' => 0,
                'approved_count' => 0,
                'rejected_count' => 0,
                'no_submission_count' => 0,
            ];
        }

        $assignedCourses = CourseAssignment::with(['course.program', 'course.courseType'])
            ->where('professor_id', $this->selectedUser->id)
            ->where('semester_id', $this->selectedSemester)
            ->get();

        $requirements = Requirement::where('semester_id', $this->selectedSemester)->get();

        $totalRequirements = 0;
        $submittedCount = 0;
        $approvedCount = 0;
        $rejectedCount = 0;
        $noSubmissionCount = 0;

        foreach ($assignedCourses as $assignment) {
            foreach ($requirements as $requirement) {
                // Only count requirements that are assigned to this course's program
                if ($this->isCourseAssignedToRequirement($assignment->course, $requirement)) {
                    $totalRequirements++;
                    
                    $submissions = SubmittedRequirement::where('requirement_id', $requirement->id)
                        ->where('user_id', $this->selectedUser->id)
                        ->where('course_id', $assignment->course_id)
                        ->get();

                    if ($submissions->count() > 0) {
                        foreach ($submissions as $submission) {
                            $submittedCount++;
                            if (strtolower($submission->status) === 'approved') $approvedCount++;
                            if (strtolower($submission->status) === 'rejected') $rejectedCount++;
                        }
                    } else {
                        $noSubmissionCount++;
                    }
                }
            }
        }

        return [
            'total_requirements' => $totalRequirements,
            'submitted_count' => $submittedCount,
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'no_submission_count' => $noSubmissionCount,
        ];
    }

    public function getDetailedRequirements()
    {
        if (!$this->selectedUser || !$this->selectedSemester) {
            return [
                'courses_by_program' => collect(),
                'requirements' => collect(),
                'grouped_submissions' => [],
            ];
        }

        $assignedCourses = CourseAssignment::with(['course.program', 'course.courseType'])
            ->where('professor_id', $this->selectedUser->id)
            ->where('semester_id', $this->selectedSemester)
            ->get();

        // Update requirements query to order by requirement_type_ids
        $requirements = Requirement::where('semester_id', $this->selectedSemester)
            ->orderByRaw('
                CASE 
                    WHEN JSON_LENGTH(requirement_type_ids) = 0 OR requirement_type_ids IS NULL THEN 1 
                    ELSE 0 
                END
            ') // Put empty arrays last
            ->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(requirement_type_ids, "$[0]")) AS UNSIGNED)') // Order by first type ID
            ->orderBy('name') // Secondary order by name for same type IDs
            ->get();

        // Rest of the method remains the same...
        $groupedSubmissions = [];
        
        // First, get all requirements that are assigned to the user's courses
        $assignedRequirements = $requirements->filter(function($requirement) use ($assignedCourses) {
            foreach ($assignedCourses as $assignment) {
                if ($this->isCourseAssignedToRequirement($assignment->course, $requirement)) {
                    return true;
                }
            }
            return false;
        });

        foreach ($assignedCourses as $assignment) {
            foreach ($assignedRequirements as $requirement) {
                // Only include requirements that are assigned to this course's program
                if ($this->isCourseAssignedToRequirement($assignment->course, $requirement)) {
                    $key = $assignment->course_id . '_' . $requirement->id;
                    $submissions = SubmittedRequirement::with('media')
                        ->where('requirement_id', $requirement->id)
                        ->where('user_id', $this->selectedUser->id)
                        ->where('course_id', $assignment->course_id)
                        ->get();
                    
                    $groupedSubmissions[$key] = $submissions;
                }
            }
        }

        // Apply submission filter to the courses_by_program structure
        $filteredCoursesByProgram = $assignedCourses->groupBy(function($assignment) {
            return $assignment->course->program->id;
        })->map(function($programCourses) use ($assignedRequirements, $groupedSubmissions) {
            return $programCourses->map(function($assignment) use ($assignedRequirements, $groupedSubmissions) {
                // Create a copy of the assignment
                $filteredAssignment = clone $assignment;
                
                // Filter the requirements for this course based on submission status
                $filteredRequirements = $assignedRequirements->filter(function($requirement) use ($assignment, $groupedSubmissions) {
                    if (!$this->isCourseAssignedToRequirement($assignment->course, $requirement)) {
                        return false;
                    }
                    
                    $key = $assignment->course_id . '_' . $requirement->id;
                    $hasSubmission = isset($groupedSubmissions[$key]) && $groupedSubmissions[$key]->count() > 0;
                    
                    // Apply the submission filter
                    if ($this->submissionFilter === 'all') {
                        return true;
                    } elseif ($this->submissionFilter === 'with_submission') {
                        return $hasSubmission;
                    } elseif ($this->submissionFilter === 'no_submission') {
                        return !$hasSubmission;
                    }
                    
                    return true;
                });
                
                // Store the filtered requirements for this course
                $filteredAssignment->filtered_requirements = $filteredRequirements;
                
                return $filteredAssignment;
            })->filter(function($assignment) {
                // Remove courses that have no requirements after filtering
                return $assignment->filtered_requirements->count() > 0;
            });
        })->filter(function($programCourses) {
            // Remove programs that have no courses after filtering
            return $programCourses->count() > 0;
        });

        return [
            'courses_by_program' => $filteredCoursesByProgram,
            'requirements' => $assignedRequirements,
            'grouped_submissions' => $groupedSubmissions,
        ];
    }
    
    public function generateReport()
    {
        // Validate that a semester and user are selected
        if (!$this->selectedSemester) {
            session()->flash('error', 'Please select a semester to generate the report.');
            return;
        }

        if (!$this->selectedUser) {
            session()->flash('error', 'Please select a faculty member to generate the report.');
            return;
        }

        // Generate URL for the report preview with submission filter
        $previewUrl = route('admin.reports.preview-faculty', [
            'user_id' => $this->selectedUser->id,
            'semester_id' => $this->selectedSemester,
            'submission_filter' => $this->submissionFilter // Add this line
        ]);

        // Open in new tab using JavaScript
        $this->dispatch('open-new-tab', url: $previewUrl);
    }

    public function render()
    {
        return view('livewire.admin.report.user-report', [
            'selectedUserData' => $this->selectedUser
        ]);
    }
}