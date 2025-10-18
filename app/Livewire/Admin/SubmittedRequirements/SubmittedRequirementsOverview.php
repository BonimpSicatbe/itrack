<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use App\Models\Requirement;
use App\Models\Semester;
use App\Models\User;
use App\Models\CourseAssignment;
use App\Models\RequirementSubmissionIndicator;
use Livewire\Component;

class SubmittedRequirementsOverview extends Component
{
    public $search = '';

    // Add this to make search reactive
    protected $listeners = ['refreshOverview' => '$refresh'];

    public function render()
    {
        $activeSemester = Semester::getActiveSemester();
        
        if (!$activeSemester) {
            return view('livewire.admin.submitted-requirements.submitted-requirements-overview', [
                'overviewData' => []
            ]);
        }

        $overviewData = $this->getOverviewData($activeSemester);

        return view('livewire.admin.submitted-requirements.submitted-requirements-overview', [
            'overviewData' => $overviewData
        ]);
    }

    protected function getOverviewData($activeSemester)
    {
        // Get all requirements for the active semester
        $requirements = Requirement::where('semester_id', $activeSemester->id)
            ->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(requirement_type_ids, "$[0]")) AS UNSIGNED) ASC')
            ->orderBy('name')
            ->get();

        // Get all non-admin users with search functionality - ONLY ACTIVE USERS
        $usersQuery = User::where('is_active', true) // Only active users
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->with(['college', 'department'])
            ->orderBy('lastname')
            ->orderBy('firstname');

        // Apply search filter
        if ($this->search) {
            $usersQuery->where(function($q) {
                $q->where('firstname', 'like', '%'.$this->search.'%')
                ->orWhere('middlename', 'like', '%'.$this->search.'%')
                ->orWhere('lastname', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')
                ->orWhereHas('college', function($collegeQuery) {
                    $collegeQuery->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('department', function($deptQuery) {
                    $deptQuery->where('name', 'like', '%'.$this->search.'%');
                });
            });
        }

        $users = $usersQuery->get();

        // Get course assignments for the filtered users
        $courseAssignments = CourseAssignment::whereIn('professor_id', $users->pluck('id'))
            ->where('semester_id', $activeSemester->id)
            ->with('course')
            ->get()
            ->groupBy('professor_id');

        // Get submission indicators for the filtered users
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
            'users' => $users,
            'courseAssignments' => $courseAssignments,
            'submissionIndicators' => $submissionIndicators,
            'userCoursesData' => $userCoursesData
        ];
    }

    // ... rest of your helper methods remain the same ...
    public function getUserCourses($userId, $courseAssignments)
    {
        if (!isset($courseAssignments[$userId])) {
            return collect();
        }
        
        return $courseAssignments[$userId]->pluck('course');
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

    public function getSubmissionDisplay($userId, $requirementId, $courseId, $submissionIndicators)
    {
        $status = $this->getSubmissionStatusForCourse($userId, $requirementId, $courseId, $submissionIndicators);
        
        return $status === 'not_submitted' ? 'No Submission' : 'Submitted';
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
}