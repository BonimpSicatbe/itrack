<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use App\Models\Semester;
use App\Models\User;
use App\Models\CourseAssignment;
use App\Models\Requirement;
use App\Models\RequirementSubmissionIndicator;

class UserReport extends Component
{
    public $search = '';
    public $selectedSemester = '';
    public $selectedUser = null;
    public $userReportData = [];
    
    public $semesters = [];

    public function mount()
    {
        $this->loadSemesters();
        
        // Set default active semester
        $activeSemester = Semester::getActiveSemester();
        if ($activeSemester) {
            $this->selectedSemester = $activeSemester->id;
        }
    }

    public function loadSemesters()
    {
        $today = now()->format('Y-m-d');
        
        $this->semesters = Semester::where('start_date', '<=', $today)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function searchUser()
    {
        $this->validate([
            'search' => 'required|min:2',
            'selectedSemester' => 'required|exists:semesters,id'
        ]);

        // Search for user by name or email
        $user = User::where('is_active', true)
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->where(function($query) {
                $query->where('firstname', 'like', '%'.$this->search.'%')
                    ->orWhere('middlename', 'like', '%'.$this->search.'%')
                    ->orWhere('lastname', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->first();

        if ($user) {
            $this->selectedUser = $user;
            $this->loadUserReportData();
        } else {
            $this->selectedUser = null;
            $this->userReportData = [];
            session()->flash('error', 'No user found with the provided search criteria.');
        }
    }

    public function loadUserReportData()
    {
        if (!$this->selectedUser || !$this->selectedSemester) {
            return;
        }

        $semester = Semester::find($this->selectedSemester);
        $user = $this->selectedUser;

        // Get all course assignments for the user in the selected semester
        $courseAssignments = CourseAssignment::where('professor_id', $user->id)
            ->where('semester_id', $this->selectedSemester)
            ->with(['course.program'])
            ->get();

        // Get all requirements for the semester
        $requirements = Requirement::where('semester_id', $this->selectedSemester)
            ->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(requirement_type_ids, "$[0]")) AS UNSIGNED) ASC')
            ->orderBy('name')
            ->get();

        // Get submission indicators for this user
        $submissionIndicators = RequirementSubmissionIndicator::where('user_id', $user->id)
            ->whereIn('requirement_id', $requirements->pluck('id'))
            ->with(['requirement', 'course'])
            ->get()
            ->groupBy(['course_id', 'requirement_id']);

        // Organize data by program and course
        $programData = [];
        
        foreach ($courseAssignments as $assignment) {
            $course = $assignment->course;
            $program = $course->program;
            
            if (!isset($programData[$program->id])) {
                $programData[$program->id] = [
                    'program' => $program,
                    'courses' => []
                ];
            }

            $courseRequirements = [];
            foreach ($requirements as $requirement) {
                $submission = $submissionIndicators[$course->id][$requirement->id] ?? null;
                $submissionFile = $submission->first();
                
                $courseRequirements[] = [
                    'requirement' => $requirement,
                    'submission' => $submissionFile,
                    'status' => $submissionFile ? 
                        ($submissionFile->status === 'approved' ? 'APPROVED' : 'UNDER REVIEW') : 
                        'NO SUBMISSION',
                    'submitted_at' => $submissionFile ? $submissionFile->created_at : null,
                    'files' => $submissionFile ? $this->getSubmissionFiles($submissionFile) : []
                ];
            }

            $programData[$program->id]['courses'][] = [
                'course' => $course,
                'requirements' => $courseRequirements
            ];
        }

        $this->userReportData = [
            'user' => $user,
            'semester' => $semester,
            'programs' => $programData,
            'total_requirements' => $requirements->count(),
            'submitted_count' => $this->countSubmissions($submissionIndicators),
            'approved_count' => $this->countApprovedSubmissions($submissionIndicators),
            'no_submission_count' => $requirements->count() * $courseAssignments->count() - $this->countSubmissions($submissionIndicators)
        ];
    }

    private function getSubmissionFiles($submission)
    {
        if (!$submission->file_path) {
            return [];
        }

        // Handle multiple files (JSON array) or single file (string)
        $files = [];
        
        if (is_string($submission->file_path)) {
            $decoded = json_decode($submission->file_path, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $files = $decoded;
            } else {
                $files = [$submission->file_path];
            }
        } elseif (is_array($submission->file_path)) {
            $files = $submission->file_path;
        }

        return array_map(function($file) {
            return basename($file);
        }, $files);
    }

    private function countSubmissions($submissionIndicators)
    {
        $count = 0;
        foreach ($submissionIndicators as $courseIndicators) {
            foreach ($courseIndicators as $requirementIndicators) {
                $count += $requirementIndicators->count();
            }
        }
        return $count;
    }

    private function countApprovedSubmissions($submissionIndicators)
    {
        $count = 0;
        foreach ($submissionIndicators as $courseIndicators) {
            foreach ($courseIndicators as $requirementIndicators) {
                foreach ($requirementIndicators as $indicator) {
                    if ($indicator->status === 'approved') {
                        $count++;
                    }
                }
            }
        }
        return $count;
    }

    public function generateUserReport()
    {
        if (!$this->selectedUser) {
            session()->flash('error', 'Please search and select a user first.');
            return;
        }

        // Generate PDF report for the specific user
        $params = [
            'semester_id' => $this->selectedSemester,
            'user_id' => $this->selectedUser->id
        ];

        $previewUrl = route('admin.reports.preview-user', $params);
        $this->dispatch('open-new-tab', url: $previewUrl);
    }

    public function render()
    {
        return view('livewire.admin.report.user-report');
    }
}
