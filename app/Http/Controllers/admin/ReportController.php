<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\College;
use App\Models\Program;
use App\Models\Course;
use App\Models\Requirement;
use App\Models\User;
use App\Models\CourseAssignment;
use App\Models\RequirementSubmissionIndicator;
use App\Models\SubmittedRequirement;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tabs = [
            'overview' => ['label' => 'Overview', 'icon' => 'chart-simple'],
            'users' => ['label' => 'Faculty', 'icon' => 'users'],
            'requirements' => ['label' => 'Requirements', 'icon' => 'clipboard-list'],
            'yearly' => ['label' => 'Custom Year', 'icon' => 'calendar'],
        ];

        $activeTab = $request->query('tab', 'overview');

        return view('admin.pages.report.report_index', [
            'tabs' => $tabs,
            'activeTab' => $activeTab
        ]);
    }

    public function previewSemesterReport(Request $request)
    {
        // Get filter parameters from request
        $semesterId = $request->input('semester_id');
        $programId = $request->input('program_id');
        $search = $request->input('search');
        
        // Use selected semester or get active semester
        $semester = $semesterId 
            ? Semester::find($semesterId)
            : Semester::getActiveSemester();
        
        if (!$semester) {
            abort(404, 'No semester found');
        }

        // Get overview data for PDF generation
        $overviewData = $this->getOverviewData($semester, $programId, $search);
        
        if ($overviewData['users']->isEmpty()) {
            abort(404, 'No data found for the selected filters');
        }

        // Generate PDF report for preview
        $pdf = Pdf::loadView('reports.semester-report-pdf', [
            'overviewData' => $overviewData,
            'search' => $search
        ])->setPaper('a4', 'landscape');

        // Preview in browser instead of downloading
        return $pdf->stream('faculty-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function previewFacultyReport(Request $request)
    {
        // Get parameters from request
        $userId = $request->input('user_id');
        $semesterId = $request->input('semester_id');
        $submissionFilter = $request->input('submission_filter', 'all'); // Add this line
        
        // Validate required parameters
        if (!$userId || !$semesterId) {
            abort(400, 'User ID and Semester ID are required');
        }

        // Get user and semester
        $user = User::with('college')->find($userId);
        $semester = Semester::find($semesterId);

        if (!$user || !$semester) {
            abort(404, 'User or Semester not found');
        }

        // Get course assignments for the user and semester
        $assignedCourses = CourseAssignment::with(['course.program', 'course.courseType'])
            ->where('professor_id', $user->id)
            ->where('semester_id', $semester->id)
            ->get();

        // Get requirements for the semester
        $requirements = Requirement::where('semester_id', $semester->id)->get();

        // Calculate summary data
        $summaryData = $this->calculateFacultySummaryData($user, $semester, $assignedCourses, $requirements);

        // Get detailed requirements data WITH SUBMISSION FILTER
        $detailedRequirements = $this->getFacultyDetailedRequirements($user, $semester, $assignedCourses, $requirements, $submissionFilter);

        // Generate PDF report for preview
        $pdf = Pdf::loadView('reports.faculty-report-pdf', [
            'user' => $user,
            'semester' => $semester,
            'summaryData' => $summaryData,
            'detailedRequirements' => $detailedRequirements,
            'submissionFilter' => $submissionFilter // Add this line
        ])->setPaper('a4', 'portrait');

        // Preview in browser instead of downloading
        return $pdf->stream('faculty-report-' . $user->lastname . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Calculate summary statistics for faculty report
     */
    protected function calculateFacultySummaryData($user, $semester, $assignedCourses, $requirements)
    {
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
                        ->where('user_id', $user->id)
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

    /**
     * Get detailed requirements data for faculty report
     */
    protected function getFacultyDetailedRequirements($user, $semester, $assignedCourses, $requirements, $submissionFilter = 'all')
    {
        // Group submissions by course and requirement
        $groupedSubmissions = [];
        
        // Get all requirements that are assigned to the user's courses
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
                        ->where('user_id', $user->id)
                        ->where('course_id', $assignment->course_id)
                        ->get();
                    
                    $groupedSubmissions[$key] = $submissions;
                }
            }
        }

        // Group courses by program with filtered requirements - APPLY SUBMISSION FILTER
        $coursesByProgram = $assignedCourses->groupBy(function($assignment) {
            return $assignment->course->program->id;
        })->map(function($programCourses) use ($assignedRequirements, $groupedSubmissions, $submissionFilter) {
            return $programCourses->map(function($assignment) use ($assignedRequirements, $groupedSubmissions, $submissionFilter) {
                // Create a copy of the assignment
                $filteredAssignment = clone $assignment;
                
                // Filter the requirements for this course based on submission status
                $filteredRequirements = $assignedRequirements->filter(function($requirement) use ($assignment, $groupedSubmissions, $submissionFilter) {
                    if (!$this->isCourseAssignedToRequirement($assignment->course, $requirement)) {
                        return false;
                    }
                    
                    $key = $assignment->course_id . '_' . $requirement->id;
                    $hasSubmission = isset($groupedSubmissions[$key]) && $groupedSubmissions[$key]->count() > 0;
                    
                    // Apply the submission filter
                    if ($submissionFilter === 'all') {
                        return true;
                    } elseif ($submissionFilter === 'with_submission') {
                        return $hasSubmission;
                    } elseif ($submissionFilter === 'no_submission') {
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
            'courses_by_program' => $coursesByProgram,
            'requirements' => $assignedRequirements,
            'grouped_submissions' => $groupedSubmissions,
            'submission_filter' => $submissionFilter // Include filter in response
        ];
    }

    protected function getOverviewData($semester, $programId = null, $search = null)
    {
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

        // Apply search filter
        if ($search) {
            $usersQuery->where(function($q) use ($search) {
                $q->where('firstname', 'like', '%'.$search.'%')
                ->orWhere('middlename', 'like', '%'.$search.'%')
                ->orWhere('lastname', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('position', 'like', '%'.$search.'%') // Rank/Position
                ->orWhereHas('college', function($collegeQuery) use ($search) {
                    $collegeQuery->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('courseAssignments.course.program', function($programQuery) use ($search) {
                    $programQuery->where('program_name', 'like', '%'.$search.'%')
                                ->orWhere('program_code', 'like', '%'.$search.'%');
                })
                ->orWhereHas('courseAssignments.course', function($courseQuery) use ($search) {
                    $courseQuery->where('course_name', 'like', '%'.$search.'%')
                               ->orWhere('course_code', 'like', '%'.$search.'%');
                });
            });
        }

        $users = $usersQuery->get();

        // Get course assignments for the filtered users - WITH PROGRAM FILTERING
        $courseAssignmentsQuery = CourseAssignment::whereIn('professor_id', $users->pluck('id'))
            ->where('semester_id', $semester->id)
            ->with(['course' => function($query) use ($programId) {
                // Apply program filter to courses if a program is selected
                if ($programId) {
                    $query->where('program_id', $programId);
                }
                $query->with('program'); // Eager load program relationship
            }]);

        $courseAssignments = $courseAssignmentsQuery->get()
            ->groupBy('professor_id');

        // Filter out course assignments where the course doesn't match the program filter
        if ($programId) {
            foreach ($courseAssignments as $professorId => $assignments) {
                $filteredAssignments = $assignments->filter(function($assignment) use ($programId) {
                    return $assignment->course && $assignment->course->program_id == $programId;
                });
                
                if ($filteredAssignments->isEmpty()) {
                    unset($courseAssignments[$professorId]);
                } else {
                    $courseAssignments[$professorId] = $filteredAssignments;
                }
            }
        }

        // Get submission indicators for the filtered users and filtered courses
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
            if ($programId) {
                $courses = $courses->filter(function($course) use ($programId) {
                    return $course->program_id == $programId;
                });
            }
            
            $userCoursesData[$user->id] = $courses;
        }

        // Filter out users who have no courses after program filtering
        if ($programId) {
            $users = $users->filter(function($user) use ($userCoursesData) {
                return $userCoursesData[$user->id]->isNotEmpty();
            });
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

    public function hasUserSubmittedForCourse($userId, $requirementId, $courseId, $submissionIndicators)
    {
        return isset($submissionIndicators[$userId][$requirementId][$courseId]) && 
               $submissionIndicators[$userId][$requirementId][$courseId]->isNotEmpty();
    }

    public function getSubmissionDisplay($userId, $requirementId, $courseId, $submissionIndicators)
    {
        $hasSubmitted = $this->hasUserSubmittedForCourse($userId, $requirementId, $courseId, $submissionIndicators);
        return $hasSubmitted ? 'Submitted' : 'No Submission';
    }

    public function getStatusBadgeClass($status)
    {
        return match($status) {
            'submitted' => 'bg-green-100 text-green-800',
            'not_submitted' => 'bg-gray-100 text-gray-500',
            default => 'bg-gray-100 text-gray-500'
        };
    }

    public function getUserRowspan($userId, $userCoursesData)
    {
        $courses = $userCoursesData[$userId] ?? collect();
        return max(1, $courses->count());
    }

    // Helper method to calculate summary statistics for PDF
    public function calculateSummaryStatistics($overviewData)
    {
        $totalCourses = 0;
        $totalPossibleSubmissions = 0;
        $totalActualSubmissions = 0;
        
        foreach($overviewData['userCoursesData'] as $courses) {
            $totalCourses += $courses->count();
        }
        
        $totalPossibleSubmissions = $totalCourses * $overviewData['requirements']->count();
        
        foreach($overviewData['users'] as $user) {
            foreach($overviewData['userCoursesData'][$user->id] as $course) {
                foreach($overviewData['requirements'] as $requirement) {
                    if ($this->hasUserSubmittedForCourse($user->id, $requirement->id, $course->id, $overviewData['submissionIndicators'])) {
                        $totalActualSubmissions++;
                    }
                }
            }
        }
        
        $submissionRate = $totalPossibleSubmissions > 0 ? round(($totalActualSubmissions / $totalPossibleSubmissions) * 100, 1) : 0;
        
        return [
            'totalCourses' => $totalCourses,
            'totalPossibleSubmissions' => $totalPossibleSubmissions,
            'totalActualSubmissions' => $totalActualSubmissions,
            'submissionRate' => $submissionRate
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

    /**
     * Download faculty report (alternative to preview)
     */
    public function downloadFacultyReport(Request $request)
    {
        // Get parameters from request
        $userId = $request->input('user_id');
        $semesterId = $request->input('semester_id');
        $submissionFilter = $request->input('submission_filter', 'all'); // Add this line
        
        // Validate required parameters
        if (!$userId || !$semesterId) {
            abort(400, 'User ID and Semester ID are required');
        }

        // Get user and semester
        $user = User::with('college')->find($userId);
        $semester = Semester::find($semesterId);

        if (!$user || !$semester) {
            abort(404, 'User or Semester not found');
        }

        // Get course assignments for the user and semester
        $assignedCourses = CourseAssignment::with(['course.program', 'course.courseType'])
            ->where('professor_id', $user->id)
            ->where('semester_id', $semester->id)
            ->get();

        // Get requirements for the semester
        $requirements = Requirement::where('semester_id', $semester->id)->get();

        // Calculate summary data
        $summaryData = $this->calculateFacultySummaryData($user, $semester, $assignedCourses, $requirements);

        // Get detailed requirements data WITH SUBMISSION FILTER
        $detailedRequirements = $this->getFacultyDetailedRequirements($user, $semester, $assignedCourses, $requirements, $submissionFilter);

        // Generate PDF report for download
        $pdf = Pdf::loadView('reports.faculty-report-pdf', [
            'user' => $user,
            'semester' => $semester,
            'summaryData' => $summaryData,
            'detailedRequirements' => $detailedRequirements,
            'submissionFilter' => $submissionFilter // Add this line
        ])->setPaper('a4', 'portrait');

        // Download the PDF
        return $pdf->download('faculty-report-' . $user->lastname . '-' . $semester->name . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function previewRequirementReport(Request $request)
    {
        // Get parameters from request
        $requirementId = $request->input('requirement_id');
        $semesterId = $request->input('semester_id');
        $submissionFilter = $request->input('submission_filter', 'all'); // Add this line
        
        // Validate required parameters
        if (!$requirementId || !$semesterId) {
            abort(400, 'Requirement ID and Semester ID are required');
        }

        // Get requirement and semester
        $requirement = Requirement::find($requirementId);
        $semester = Semester::find($semesterId);

        if (!$requirement || !$semester) {
            abort(404, 'Requirement or Semester not found');
        }

        // Get all instructors with course assignments for this semester
        $instructors = User::where('is_active', true)
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->whereHas('courseAssignments', function($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->with(['college', 'courseAssignments' => function($query) use ($semesterId) {
                $query->where('semester_id', $semesterId)
                    ->with('course.program');
            }])
            ->get();

        // Get submitted requirements for this requirement and semester
        $submittedUsers = SubmittedRequirement::with(['user.college', 'course'])
            ->where('requirement_id', $requirement->id)
            ->whereIn('user_id', $instructors->pluck('id'))
            ->whereIn('course_id', function($query) use ($semesterId) {
                $query->select('course_id')
                    ->from('course_assignments')
                    ->where('semester_id', $semesterId);
            })
            ->get();

        // Prepare instructors with courses data
        $instructorsWithCourses = [];

        foreach ($instructors as $instructor) {
            $courseSubmissions = [];

            foreach ($instructor->courseAssignments as $assignment) {
                // Only include courses where this requirement is assigned to the course's program
                if ($this->isCourseAssignedToRequirement($assignment->course, $requirement)) {
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

        // Apply submission filter to the data - ADD THIS SECTION
        $filteredInstructorsWithCourses = [];
        
        foreach ($instructorsWithCourses as $instructorData) {
            $filteredCourseSubmissions = [];
            
            foreach ($instructorData['courseSubmissions'] as $courseData) {
                $shouldInclude = false;
                
                switch ($submissionFilter) {
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

        // Generate PDF report for preview
        $pdf = Pdf::loadView('reports.requirement-report-pdf', [
            'requirement' => $requirement,
            'semester' => $semester,
            'submittedUsers' => $submittedUsers,
            'notSubmittedUsers' => $instructors->filter(function($instructor) use ($submittedUsers) {
                return !$submittedUsers->contains('user_id', $instructor->id);
            }),
            'instructorsWithCourses' => $filteredInstructorsWithCourses, // Use filtered data
            'submissionFilter' => $submissionFilter, // Pass filter to view
        ])->setPaper('a4', 'portrait');

        // Clean the filename for preview as well
        $cleanRequirementName = preg_replace('/[\/\\\\]/', '-', $requirement->name);
        
        // Preview in browser instead of downloading
        return $pdf->stream('requirement-report-' . $cleanRequirementName . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadRequirementReport(Request $request)
    {
        // Get parameters from request
        $requirementId = $request->input('requirement_id');
        $semesterId = $request->input('semester_id');
        
        // Validate required parameters
        if (!$requirementId || !$semesterId) {
            abort(400, 'Requirement ID and Semester ID are required');
        }

        // Get requirement and semester
        $requirement = Requirement::find($requirementId);
        $semester = Semester::find($semesterId);

        if (!$requirement || !$semester) {
            abort(404, 'Requirement or Semester not found');
        }

        // Get all instructors with course assignments for this semester
        $instructors = User::where('is_active', true)
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->whereHas('courseAssignments', function($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->with(['college', 'courseAssignments' => function($query) use ($semesterId) {
                $query->where('semester_id', $semesterId)
                    ->with('course.program');
            }])
            ->get();

        // Get submitted requirements for this requirement and semester
        $submittedUsers = SubmittedRequirement::with(['user.college', 'course'])
            ->where('requirement_id', $requirement->id)
            ->whereIn('user_id', $instructors->pluck('id'))
            ->whereIn('course_id', function($query) use ($semesterId) {
                $query->select('course_id')
                    ->from('course_assignments')
                    ->where('semester_id', $semesterId);
            })
            ->get();

        // Prepare instructors with courses data
        $instructorsWithCourses = [];

        foreach ($instructors as $instructor) {
            $courseSubmissions = [];

            foreach ($instructor->courseAssignments as $assignment) {
                // Only include courses where this requirement is assigned to the course's program
                if ($this->isCourseAssignedToRequirement($assignment->course, $requirement)) {
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

        // Generate PDF report for download
        $pdf = Pdf::loadView('reports.requirement-report-pdf', [
            'requirement' => $requirement,
            'semester' => $semester,
            'submittedUsers' => $submittedUsers,
            'notSubmittedUsers' => $instructors->filter(function($instructor) use ($submittedUsers) {
                return !$submittedUsers->contains('user_id', $instructor->id);
            }),
            'instructorsWithCourses' => $instructorsWithCourses,
        ])->setPaper('a4', 'portrait');

        // Download the PDF
        return $pdf->download('requirement-report-' . $requirement->name . '-' . $semester->name . '-' . now()->format('Y-m-d') . '.pdf');
    }
}