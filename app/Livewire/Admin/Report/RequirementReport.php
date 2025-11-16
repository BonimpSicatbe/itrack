<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Semester;
use App\Models\Requirement;
use App\Models\User;
use App\Models\CourseAssignment;
use App\Models\SubmittedRequirement;

class RequirementReport extends Component
{
    use WithPagination;
    
    public $search = '';
    public $selectedRequirement = null;
    public $selectedSemester = '';
    public $semesters = [];
    public $requirementSearchResults = [];
    public $showRequirementDropdown = false;
    public $submissionFilter = 'all';

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
            $this->searchRequirements();
        } else {
            // If search is empty, show all requirements for selected semester
            $this->showAllRequirements();
        }
    }

    public function updatedSelectedSemester()
    {
        $this->resetPage();
        // Clear requirement selection when semester changes
        $this->clearRequirementSelection();
        // Refresh dropdown if it's open
        if ($this->showRequirementDropdown) {
            if (empty($this->search)) {
                $this->showAllRequirements();
            } else {
                $this->searchRequirements();
            }
        }
    }

    public function showAllRequirements()
    {
        if (!$this->selectedSemester) {
            $this->requirementSearchResults = collect();
            return;
        }

        $this->requirementSearchResults = Requirement::with('semester')
            ->where('semester_id', $this->selectedSemester)
            ->orderBy('name')
            ->limit(50)
            ->get();
        
        $this->showRequirementDropdown = true;
    }

    public function searchRequirements()
    {
        if (!$this->selectedSemester) {
            $this->requirementSearchResults = collect();
            return;
        }

        $this->requirementSearchResults = Requirement::with('semester')
            ->where('semester_id', $this->selectedSemester)
            ->where('name', 'like', '%'.$this->search.'%')
            ->orderBy('name')
            ->limit(50)
            ->get();
        
        $this->showRequirementDropdown = true;
    }

    public function showDropdown()
    {
        // When input is focused, show all requirements for selected semester if no search text
        if (empty($this->search)) {
            $this->showAllRequirements();
        } else {
            $this->searchRequirements();
        }
    }

    public function selectRequirement($requirementId)
    {
        // Close dropdown first
        $this->showRequirementDropdown = false;
        
        // Then select the requirement
        $this->selectedRequirement = Requirement::with('semester')->find($requirementId);
        $this->search = $this->selectedRequirement->name;
        $this->requirementSearchResults = [];
    }

    public function clearRequirementSelection()
    {
        $this->selectedRequirement = null;
        $this->search = '';
        $this->requirementSearchResults = [];
        $this->showRequirementDropdown = false;
        
        // Force a re-render to ensure the view updates
        $this->dispatch('requirement-cleared');
    }

    public function updatedSubmissionFilter()
    {
        $this->resetPage();
    }

    public function getSubmissionSummary()
    {
        if (!$this->selectedRequirement || !$this->selectedSemester) {
            return [
                'total_instructors' => 0,
                'submitted_count' => 0,
                'no_submission_count' => 0,
                'completion_rate' => 0,
            ];
        }

        // Get all instructors with course assignments for this semester
        $instructors = User::where('is_active', true)
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->whereHas('courseAssignments', function($query) {
                $query->where('semester_id', $this->selectedSemester);
            })
            ->with(['courseAssignments' => function($query) {
                $query->where('semester_id', $this->selectedSemester)
                      ->with('course.program');
            }])
            ->get();

        $totalInstructors = 0;
        $totalCourseAssignments = 0;
        $submittedCount = 0;

        foreach ($instructors as $instructor) {
            foreach ($instructor->courseAssignments as $assignment) {
                // Check if this requirement is assigned to the course's program
                if ($this->isCourseAssignedToRequirement($assignment->course, $this->selectedRequirement)) {
                    $totalCourseAssignments++;
                    
                    $submission = SubmittedRequirement::where('requirement_id', $this->selectedRequirement->id)
                        ->where('user_id', $instructor->id)
                        ->where('course_id', $assignment->course_id)
                        ->first();

                    if ($submission) {
                        $submittedCount++;
                    }
                }
            }
            
            if ($instructor->courseAssignments->count() > 0) {
                $totalInstructors++;
            }
        }

        $noSubmissionCount = $totalCourseAssignments - $submittedCount;
        $completionRate = $totalCourseAssignments > 0 ? round(($submittedCount / $totalCourseAssignments) * 100, 1) : 0;

        return [
            'total_instructors' => $totalInstructors,
            'total_course_assignments' => $totalCourseAssignments,
            'submitted_count' => $submittedCount,
            'no_submission_count' => $noSubmissionCount,
            'completion_rate' => $completionRate,
        ];
    }

    public function getDetailedSubmissions()
    {
        if (!$this->selectedRequirement || !$this->selectedSemester) {
            return [
                'submitted_users' => collect(),
                'not_submitted_users' => collect(),
                'instructors_with_courses' => [],
            ];
        }

        // Get all instructors with course assignments for this semester
        $instructors = User::where('is_active', true)
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->whereHas('courseAssignments', function($query) {
                $query->where('semester_id', $this->selectedSemester);
            })
            ->with(['college', 'courseAssignments' => function($query) {
                $query->where('semester_id', $this->selectedSemester)
                    ->with('course.program');
            }])
            ->orderBy('lastname') // Add this line to sort by lastname
            ->get();

        // Get submitted requirements for this requirement and semester
        $submittedUsers = SubmittedRequirement::with(['user.college', 'course'])
            ->where('requirement_id', $this->selectedRequirement->id)
            ->whereIn('user_id', $instructors->pluck('id'))
            ->whereIn('course_id', function($query) {
                $query->select('course_id')
                    ->from('course_assignments')
                    ->where('semester_id', $this->selectedSemester);
            })
            ->get();

        // Prepare instructors with courses data
        $instructorsWithCourses = [];

        foreach ($instructors as $instructor) {
            $courseSubmissions = [];

            foreach ($instructor->courseAssignments as $assignment) {
                // Only include courses where this requirement is assigned to the course's program
                if ($this->isCourseAssignedToRequirement($assignment->course, $this->selectedRequirement)) {
                    $submission = $submittedUsers->where('user_id', $instructor->id)
                                                ->where('course_id', $assignment->course_id)
                                                ->first();

                    $courseSubmissions[] = [
                        'course' => $assignment->course,
                        'submission' => $submission
                    ];
                }
            }

            if (count($courseSubmissions) > 0) {
                $instructorsWithCourses[] = [
                    'instructor' => $instructor,
                    'courseSubmissions' => $courseSubmissions
                ];
            }
        }

        // Apply submission filter
        $filteredInstructorsWithCourses = [];
    
        foreach ($instructorsWithCourses as $instructorData) {
            $filteredCourseSubmissions = [];
            
            foreach ($instructorData['courseSubmissions'] as $courseData) {
                $shouldInclude = false;
                
                switch ($this->submissionFilter) {
                    case 'with_submission':
                        $shouldInclude = $courseData['submission'] !== null;
                        break;
                    case 'no_submission':
                        $shouldInclude = $courseData['submission'] === null;
                        break;
                    default: // 'all'
                        $shouldInclude = true;
                        break;
                }
                
                if ($shouldInclude) {
                    $filteredCourseSubmissions[] = $courseData;
                }
            }
            
            // Only include instructors who have at least one course after filtering
            if (count($filteredCourseSubmissions) > 0) {
                $filteredInstructorsWithCourses[] = [
                    'instructor' => $instructorData['instructor'],
                    'courseSubmissions' => $filteredCourseSubmissions
                ];
            }
        }

        return [
            'submitted_users' => $submittedUsers,
            'not_submitted_users' => $instructors->filter(function($instructor) use ($submittedUsers) {
                return !$submittedUsers->contains('user_id', $instructor->id);
            }),
            'instructors_with_courses' => $filteredInstructorsWithCourses,
        ];
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

    public function generateReport()
    {
        // Validate that a semester and requirement are selected
        if (!$this->selectedSemester) {
            session()->flash('error', 'Please select a semester to generate the report.');
            return;
        }

        if (!$this->selectedRequirement) {
            session()->flash('error', 'Please select a requirement to generate the report.');
            return;
        }

        // Generate URL for the report preview with submission filter
        $previewUrl = route('admin.reports.preview-requirement', [
            'requirement_id' => $this->selectedRequirement->id,
            'semester_id' => $this->selectedSemester,
            'submission_filter' => $this->submissionFilter, // Add this line
        ]);

        // Open in new tab using JavaScript
        $this->dispatch('open-new-tab', url: $previewUrl);
    }

    public function render()
    {
        return view('livewire.admin.report.requirement-report', [
            'selectedRequirementData' => $this->selectedRequirement
        ]);
    }
}