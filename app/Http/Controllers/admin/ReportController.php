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
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
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

    protected function getOverviewData($semester, $programId = null, $search = null)
    {
        // Get all requirements for the selected semester
        $requirements = Requirement::where('semester_id', $semester->id)
            ->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(requirement_type_ids, "$[0]")) AS UNSIGNED) ASC')
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
}