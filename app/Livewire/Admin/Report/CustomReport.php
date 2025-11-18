<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use App\Models\Semester;
use App\Models\User;
use App\Models\College;
use App\Models\CourseAssignment;
use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use Carbon\Carbon;

class CustomReport extends Component
{
    public $startDate;
    public $endDate;
    public $selectedCollege = '';
    public $search = '';
    public $colleges = [];
    
    public $reportData = [];
    public $summaryStats = [];

    public function mount()
    {
        $this->colleges = College::orderBy('name')->get();
        
        // Set default date range (last 5 years)
        $this->endDate = now()->format('Y-m');
        $this->startDate = now()->subYears(4)->format('Y-m');
    }

    public function generateReport()
    {
        $this->validate([
            'startDate' => 'required|date_format:Y-m',
            'endDate' => 'required|date_format:Y-m|after_or_equal:startDate',
        ]);

        // Convert Y-m to full dates for comparison
        $start = Carbon::createFromFormat('Y-m', $this->startDate)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $this->endDate)->endOfMonth();

        // Get faculty who were teaching during this period
        $facultyQuery = User::where('is_active', true)
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->where(function($query) use ($start, $end) {
                // Faculty whose teaching period overlaps with the selected date range
                $query->where(function($q) use ($start, $end) {
                    $q->whereNull('teaching_started_at')
                      ->orWhere('teaching_started_at', '<=', $end);
                })
                ->where(function($q) use ($start, $end) {
                    $q->whereNull('teaching_ended_at')
                      ->orWhere('teaching_ended_at', '>=', $start);
                });
            });

        // Apply college filter
        if ($this->selectedCollege) {
            $facultyQuery->where('college_id', $this->selectedCollege);
        }

        // Apply search filter
        if ($this->search) {
            $facultyQuery->where(function($q) {
                $q->where('firstname', 'like', '%'.$this->search.'%')
                  ->orWhere('middlename', 'like', '%'.$this->search.'%')
                  ->orWhere('lastname', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $faculty = $facultyQuery->with('college')->get();

        // Get semesters within the date range that have already started
        $semesters = Semester::where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->where('start_date', '<=', now()) // ← Add this line to exclude future semesters
            ->orderBy('start_date')
            ->get();

        $this->reportData = [];
        $totalRequirements = 0;
        $totalSubmissions = 0;
        $totalApproved = 0;

        foreach ($faculty as $facultyMember) {
            // Get course assignments within the date range
            $courseAssignments = CourseAssignment::with(['course.program', 'semester'])
                ->where('professor_id', $facultyMember->id)
                ->whereIn('semester_id', $semesters->pluck('id'))
                ->get();

            $facultyCourses = [];
            $facultyTotalSubmissions = 0;
            $facultyTotalApproved = 0;

            foreach ($courseAssignments as $assignment) {
                // Get requirements for this semester
                $requirements = Requirement::where('semester_id', $assignment->semester_id)->get();
                
                $courseRequirements = [];
                $courseSubmissions = 0;
                $courseApproved = 0;

                foreach ($requirements as $requirement) {
                    // Check if this requirement is assigned to the course's program
                    if ($this->isCourseAssignedToRequirement($assignment->course, $requirement)) {
                        $submissions = SubmittedRequirement::with('media')
                            ->where('requirement_id', $requirement->id)
                            ->where('user_id', $facultyMember->id)
                            ->where('course_id', $assignment->course_id)
                            ->get();

                        $submissionCount = $submissions->count();
                        $approvedCount = $submissions->where('status', 'approved')->count();

                        $courseRequirements[] = [
                            'requirement' => $requirement,
                            'submissions' => $submissions,
                            'submission_count' => $submissionCount,
                            'approved_count' => $approvedCount,
                        ];

                        $courseSubmissions += $submissionCount;
                        $courseApproved += $approvedCount;
                        $totalRequirements++;
                    }
                }

                if (count($courseRequirements) > 0) {
                    $facultyCourses[] = [
                        'assignment' => $assignment,
                        'requirements' => $courseRequirements,
                        'total_submissions' => $courseSubmissions,
                        'total_approved' => $courseApproved,
                    ];

                    $facultyTotalSubmissions += $courseSubmissions;
                    $facultyTotalApproved += $courseApproved;
                }
            }

            if (count($facultyCourses) > 0) {
                $this->reportData[] = [
                    'faculty' => $facultyMember,
                    'courses' => $facultyCourses,
                    'total_submissions' => $facultyTotalSubmissions,
                    'total_approved' => $facultyTotalApproved,
                    'submission_rate' => count($facultyCourses) > 0 ? round(($facultyTotalSubmissions / (count($facultyCourses) * count($facultyCourses[0]['requirements']))) * 100, 1) : 0,
                ];

                $totalSubmissions += $facultyTotalSubmissions;
                $totalApproved += $facultyTotalApproved;
            }
        }

        // Calculate summary statistics
        $this->summaryStats = [
            'total_faculty' => count($this->reportData),
            'total_courses' => array_sum(array_map(fn($item) => count($item['courses']), $this->reportData)),
            'total_requirements' => $totalRequirements,
            'total_submissions' => $totalSubmissions,
            'total_approved' => $totalApproved,
            'overall_submission_rate' => $totalRequirements > 0 ? round(($totalSubmissions / $totalRequirements) * 100, 1) : 0,
            'date_range' => [
                'start' => $start,
                'end' => $end,
                'formatted' => $start->format('F Y') . ' to ' . $end->format('F Y'),
            ],
        ];
    }

    public function generatePdf()
    {
        $this->validate([
            'startDate' => 'required|date_format:Y-m',
            'endDate' => 'required|date_format:Y-m|after_or_equal:startDate',
        ]);

        // Generate URL for the PDF with all filters
        $previewUrl = route('admin.reports.preview-custom', [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'college_id' => $this->selectedCollege,
            'search' => $this->search,
        ]);

        // Open in new tab
        $this->dispatch('open-new-tab', url: $previewUrl);
    }

    /**
     * Check if a course is assigned to a requirement
     */
    private function isCourseAssignedToRequirement($course, $requirement)
    {
        if (!$course || !$course->program_id) {
            return false;
        }

        try {
            $assignedTo = $requirement->assigned_to;
            $assignedPrograms = [];
            
            if (is_string($assignedTo)) {
                $decoded = json_decode($assignedTo, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $assignedPrograms = $decoded['programs'] ?? [];
                }
            } elseif (is_array($assignedTo)) {
                $assignedPrograms = $assignedTo['programs'] ?? [];
            }
            
            if (empty($assignedPrograms)) {
                return false;
            }
            
            return in_array($course->program_id, $assignedPrograms);
            
        } catch (\Exception $e) {
            \Log::error('Error checking requirement assignment', [
                'course_id' => $course->id,
                'requirement_id' => $requirement->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function render()
    {
        return view('livewire.admin.report.custom-report');
    }
}