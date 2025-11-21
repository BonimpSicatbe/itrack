<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseAssignment;
use App\Models\Semester;
use App\Models\Program;
use App\Models\CourseType;
use Illuminate\Validation\Rule;

class CourseManagement extends Component
{
    public $search = '';
    
    public $sortField = 'course_code';
    public $sortDirection = 'asc';
    
    public $showAddCourseModal = false;
    public $newCourse = [
        'course_code' => '',
        'course_name' => '',
        'description' => '',
        'program_id' => '',
        'course_type_id' => ''
    ];

    public $showEditCourseModal = false;
    public $editingCourse = [
        'id' => '',
        'course_code' => '',
        'course_name' => '',
        'description' => '',
        'program_id' => '',
        'course_type_id' => ''
    ];
    public $currentCourseAssignments = [];

    public $assignmentData = [
        'professor_id' => '',
        'semester_id' => ''
    ];

    // Bulk copy properties
    public $showBulkCopyModal = false;
    public $bulkCopyData = [
        'source_semester_id' => '',
        'target_semester_id' => '',
        'replace_existing' => false
    ];
    public $bulkCopyPreview = [];
    public $showBulkCopyPreview = false;

    public $showDeleteConfirmationModal = false;
    public $courseToDelete = null;

    public $showRemoveAssignmentModal = false;
    public $assignmentToRemove = null;

    public $showCourseDetailsModal = false;
    public $selectedCourse = null;

    // For combobox
    public $facultySearch = '';
    public $showFacultyDropdown = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'course_code'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function openAddCourseModal()
    {
        $this->showAddCourseModal = true;
        $this->reset('newCourse');
        $this->resetErrorBag();
    }

    public function closeAddCourseModal()
    {
        $this->showAddCourseModal = false;
        $this->reset('newCourse');
        $this->resetErrorBag();
    }

    public function openEditCourseModal($courseId)
    {
        $course = Course::with(['assignments.professor', 'assignments.semester'])->find($courseId);
        
        $this->editingCourse = [
            'id' => $course->id,
            'course_code' => $course->course_code,
            'course_name' => $course->course_name,
            'description' => $course->description,
            'program_id' => $course->program_id,
            'course_type_id' => $course->course_type_id,
        ];
        
        $this->currentCourseAssignments = $course->assignments->fresh(); 
        
        $this->assignmentData = [
            'professor_id' => '',
            'semester_id' => ''
        ];

        $this->facultySearch = '';
        $this->showFacultyDropdown = false;
        
        $this->showEditCourseModal = true;
        $this->resetErrorBag();
    }

    public function closeEditCourseModal()
    {
        $this->showEditCourseModal = false;
        $this->reset('editingCourse');
        $this->reset('currentCourseAssignments');
        $this->reset('assignmentData');
        $this->reset(['facultySearch', 'showFacultyDropdown']);
        $this->resetErrorBag();
    }

    public function openCourseDetailsModal($courseId)
    {
        $this->selectedCourse = Course::with([
            'program.college', 
            'courseType', 
            'assignments.professor', 
            'assignments.semester'
        ])->find($courseId);
        
        $this->showCourseDetailsModal = true;
    }

    public function closeCourseDetailsModal()
    {
        $this->showCourseDetailsModal = false;
        $this->selectedCourse = null;
    }

    // Bulk copy methods
    public function openBulkCopyModal()
    {
        $this->showBulkCopyModal = true;
        $this->bulkCopyData = [
            'source_semester_id' => '',
            'target_semester_id' => '',
            'replace_existing' => false
        ];
        $this->bulkCopyPreview = [];
        $this->showBulkCopyPreview = false;
        $this->resetErrorBag();
    }

    public function closeBulkCopyModal()
    {
        $this->showBulkCopyModal = false;
        $this->reset('bulkCopyData');
        $this->reset('bulkCopyPreview');
        $this->showBulkCopyPreview = false;
        $this->resetErrorBag();
    }

    public function updatedBulkCopyData()
    {
        $this->generateBulkCopyPreview();
    }

    public function generateBulkCopyPreview()
    {
        $this->bulkCopyPreview = [];
        $this->showBulkCopyPreview = false;

        if (empty($this->bulkCopyData['source_semester_id']) || empty($this->bulkCopyData['target_semester_id'])) {
            return;
        }

        // Get all course assignments from source semester
        $sourceAssignments = CourseAssignment::with(['course', 'professor', 'semester'])
            ->where('semester_id', $this->bulkCopyData['source_semester_id'])
            ->get();

        // Filter out assignments with deactivated professors and group by course
        $validAssignments = $sourceAssignments->filter(function($assignment) {
            return $assignment->professor && $assignment->professor->is_active;
        });

        // Group by course and count assignments
        $groupedByCourse = $validAssignments->groupBy('course_id');

        foreach ($groupedByCourse as $courseId => $assignments) {
            $course = $assignments->first()->course;
            $assignmentCount = $assignments->count();
            
            // Check for conflicts in target semester
            $existingAssignmentsCount = CourseAssignment::where('course_id', $courseId)
                ->where('semester_id', $this->bulkCopyData['target_semester_id'])
                ->count();

            $willCopy = $existingAssignmentsCount === 0 || $this->bulkCopyData['replace_existing'];

            $this->bulkCopyPreview[] = [
                'course_id' => $courseId,
                'course_code' => $course->course_code,
                'course_name' => $course->course_name,
                'assignment_count' => $assignmentCount,
                'existing_assignments' => $existingAssignmentsCount,
                'is_conflict' => $existingAssignmentsCount > 0,
                'will_copy' => $willCopy
            ];
        }

        $this->showBulkCopyPreview = count($this->bulkCopyPreview) > 0;
    }

    public function bulkCopyAssignments()
    {
        try {
            $this->validate([
                'bulkCopyData.source_semester_id' => 'required|exists:semesters,id',
                'bulkCopyData.target_semester_id' => 'required|exists:semesters,id',
            ], [
                'bulkCopyData.source_semester_id.required' => 'Please select a source semester.',
                'bulkCopyData.target_semester_id.required' => 'Please select a target semester.',
            ]);

            // Generate preview
            $this->generateBulkCopyPreview();

            if (empty($this->bulkCopyPreview)) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: 'No valid assignments found to copy from the selected semester.'
                );
                return;
            }

            $totalCopied = 0;
            $totalSkipped = 0;
            $coursesProcessed = 0;

            foreach ($this->bulkCopyPreview as $previewItem) {
                if (!$previewItem['will_copy']) {
                    $totalSkipped += $previewItem['assignment_count'];
                    continue;
                }

                // Remove existing assignments if replace is enabled
                if ($this->bulkCopyData['replace_existing'] && $previewItem['existing_assignments'] > 0) {
                    CourseAssignment::where('course_id', $previewItem['course_id'])
                        ->where('semester_id', $this->bulkCopyData['target_semester_id'])
                        ->delete();
                }

                // Get valid assignments for this course from source semester
                $sourceAssignments = CourseAssignment::with('professor')
                    ->where('course_id', $previewItem['course_id'])
                    ->where('semester_id', $this->bulkCopyData['source_semester_id'])
                    ->get()
                    ->filter(function($assignment) {
                        return $assignment->professor && $assignment->professor->is_active;
                    });

                foreach ($sourceAssignments as $assignment) {
                    // Check if assignment already exists (for merge mode)
                    $existingAssignment = CourseAssignment::where('course_id', $previewItem['course_id'])
                        ->where('semester_id', $this->bulkCopyData['target_semester_id'])
                        ->where('professor_id', $assignment->professor_id)
                        ->first();

                    if (!$existingAssignment) {
                        CourseAssignment::create([
                            'course_id' => $previewItem['course_id'],
                            'professor_id' => $assignment->professor_id,
                            'semester_id' => $this->bulkCopyData['target_semester_id'],
                            'assignment_date' => now(),
                        ]);
                        $totalCopied++;
                    } else {
                        $totalSkipped++;
                    }
                }

                $coursesProcessed++;
            }

            // Show success message
            $sourceSemester = Semester::find($this->bulkCopyData['source_semester_id'])->name;
            $targetSemester = Semester::find($this->bulkCopyData['target_semester_id'])->name;
            
            $message = "Successfully copied {$totalCopied} assignment(s) from {$sourceSemester} to {$targetSemester} across {$coursesProcessed} courses.";
            
            if ($totalSkipped > 0) {
                $message .= " {$totalSkipped} assignment(s) were skipped (already assigned or deactivated professors).";
            }

            $this->dispatch('showNotification', 
                type: 'success', 
                content: $message
            );

            $this->closeBulkCopyModal();

        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to copy assignments: ' . $e->getMessage()
            );
        }
    }

    // Faculty dropdown methods
    public function selectFacultyFromDropdown($professorId)
    {
        $professor = User::find($professorId);
        if ($professor) {
            $this->assignmentData['professor_id'] = $professorId;
            $this->facultySearch = $professor->firstname . ' ' . $professor->lastname;
            $this->showFacultyDropdown = false;
        }
    }

    public function updatedFacultySearch()
    {
        $this->showFacultyDropdown = true;
        
        // If search is cleared, clear the selection
        if (empty($this->facultySearch)) {
            $this->assignmentData['professor_id'] = '';
        }
    }

    public function onFacultyInputFocus()
    {
        $this->showFacultyDropdown = true;
        // If we have a selected professor but search is empty, populate search
        if ($this->assignmentData['professor_id'] && empty($this->facultySearch)) {
            $professor = User::find($this->assignmentData['professor_id']);
            if ($professor) {
                $this->facultySearch = $professor->firstname . ' ' . $professor->lastname;
            }
        }
    }

    public function clearFacultySelection()
    {
        $this->assignmentData['professor_id'] = '';
        $this->facultySearch = '';
        $this->showFacultyDropdown = false;
    }

    public function toggleFacultyDropdown()
    {
        $this->showFacultyDropdown = !$this->showFacultyDropdown;
    }

    public function getFilteredProfessorsProperty()
    {
        // Get all currently assigned professor IDs for this course
        $assignedProfessorIds = $this->currentCourseAssignments
            ->pluck('professor_id')
            ->toArray();

        $query = User::whereHas('roles', function($query) {
                $query->where('name', 'user');
            })
            ->where('is_active', true)
            ->whereNotIn('id', $assignedProfessorIds);

        if (!empty($this->facultySearch)) {
            $query->where(function($q) {
                $q->where('firstname', 'like', '%' . $this->facultySearch . '%')
                  ->orWhere('lastname', 'like', '%' . $this->facultySearch . '%')
                  ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ['%' . $this->facultySearch . '%']);
            });
        }

        return $query->orderBy('firstname')
            ->orderBy('lastname')
            ->limit(10)
            ->get()
            ->map(function($professor) {
                return [
                    'id' => $professor->id,
                    'name' => $professor->firstname . ' ' . $professor->lastname
                ];
            });
    }

    // Close dropdown when clicking outside
    public function closeFacultyDropdown()
    {
        $this->showFacultyDropdown = false;
    }

    public function openRemoveAssignmentModal($assignmentId)
    {
        $this->assignmentToRemove = CourseAssignment::with(['course', 'professor', 'semester'])->find($assignmentId);
        $this->showRemoveAssignmentModal = true;
    }

    public function closeRemoveAssignmentModal()
    {
        $this->showRemoveAssignmentModal = false;
        $this->assignmentToRemove = null;
    }

    public function removeAssignment()
    {
        if ($this->assignmentToRemove) {
            $courseName = $this->assignmentToRemove->course->course_name;
            $professorName = $this->assignmentToRemove->professor->firstname . ' ' . $this->assignmentToRemove->professor->lastname;
            $semesterInfo = $this->assignmentToRemove->semester->name ?? 'the selected semester';
            
            $this->assignmentToRemove->delete();
            
            if ($this->showEditCourseModal && $this->editingCourse['id']) {
                $this->currentCourseAssignments = Course::with(['assignments.professor', 'assignments.semester'])
                    ->find($this->editingCourse['id'])
                    ->assignments;
            }

            // Refresh the selected course if it's the same course
            if ($this->selectedCourse && $this->selectedCourse->id == $this->assignmentToRemove->course_id) {
                $this->selectedCourse = Course::with([
                    'program.college', 
                    'courseType', 
                    'assignments.professor', 
                    'assignments.semester'
                ])->find($this->assignmentToRemove->course_id);
            }
            
            $this->closeRemoveAssignmentModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Faculty '{$professorName}' removed from '{$courseName}' for {$semesterInfo} successfully!"
            );
        }
    }

    public function updateCourse()
    {
        try {
            // First, update the course information
            $this->validate([
                'editingCourse.course_code' => 'required|string|max:50', // Remove unique constraint
                'editingCourse.course_name' => 'required|string|max:255',
                'editingCourse.description' => 'nullable|string',
                'editingCourse.program_id' => 'required|exists:programs,id',
                'editingCourse.course_type_id' => 'nullable|exists:course_types,id',
            ], [
                'editingCourse.course_code.required' => 'Course code is required.',
                'editingCourse.course_name.required' => 'Course name is required.',
                'editingCourse.program_id.required' => 'Program is required.',
            ]);

            $course = Course::find($this->editingCourse['id']);
            $courseName = $course->course_name;
            
            $course->update([
                'course_code' => $this->editingCourse['course_code'],
                'course_name' => $this->editingCourse['course_name'],
                'description' => $this->editingCourse['description'],
                'program_id' => $this->editingCourse['program_id'],
                'course_type_id' => $this->editingCourse['course_type_id'],
            ]);

            // Then, if faculty is selected, assign the faculty
            if (!empty($this->assignmentData['professor_id']) && !empty($this->assignmentData['semester_id'])) {
                // Check if the selected professor is active
                $professor = User::find($this->assignmentData['professor_id']);
                if (!$professor || !$professor->is_active) {
                    $this->dispatch('showNotification', 
                        type: 'error', 
                        content: 'Cannot assign courses to inactive faculty members. Please select an active faculty member.'
                    );
                    return;
                }

                // Check if this specific professor is already assigned to this course in this semester
                $existingProfessorAssignment = CourseAssignment::where('course_id', $this->editingCourse['id'])
                    ->where('semester_id', $this->assignmentData['semester_id'])
                    ->where('professor_id', $this->assignmentData['professor_id'])
                    ->first();

                if ($existingProfessorAssignment) {
                    $professorName = User::find($this->assignmentData['professor_id'])->firstname . ' ' . User::find($this->assignmentData['professor_id'])->lastname;
                    $semesterName = Semester::find($this->assignmentData['semester_id'])->name ?? 'Selected Semester';
                    
                    $this->dispatch('showNotification', 
                        type: 'warning', 
                        content: "Professor {$professorName} is already assigned to this course for {$semesterName}."
                    );
                } else {
                    // Create the assignment
                    CourseAssignment::create([
                        'course_id' => $this->editingCourse['id'],
                        'professor_id' => $this->assignmentData['professor_id'],
                        'semester_id' => $this->assignmentData['semester_id'],
                        'assignment_date' => now(),
                    ]);

                    $professorName = User::find($this->assignmentData['professor_id'])->firstname . ' ' . User::find($this->assignmentData['professor_id'])->lastname;
                    $semesterName = Semester::find($this->assignmentData['semester_id'])->name ?? 'the semester';
                    
                    $this->dispatch('showNotification', 
                        type: 'success', 
                        content: "Course '{$courseName}' updated successfully and faculty '{$professorName}' assigned for {$semesterName}!"
                    );
                }
            } else {
                $this->dispatch('showNotification', 
                    type: 'success', 
                    content: "Course '{$courseName}' updated successfully!"
                );
            }

            // Refresh the current assignments
            $this->currentCourseAssignments = Course::with(['assignments.professor', 'assignments.semester'])
                ->find($this->editingCourse['id'])
                ->assignments;

            // Refresh the selected course if it's the same course being edited
            if ($this->selectedCourse && $this->selectedCourse->id == $course->id) {
                $this->selectedCourse = $course->fresh([
                    'program.college', 
                    'courseType', 
                    'assignments.professor', 
                    'assignments.semester'
                ]);
            }

            // Clear the assignment data
            $this->assignmentData = [
                'professor_id' => '',
                'semester_id' => ''
            ];
            $this->clearFacultySelection();
            
            $this->closeEditCourseModal();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            foreach ($errors as $error) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: $error
                );
            }
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'An unexpected error occurred: ' . $e->getMessage()
            );
        }
    }

    public function addCourse()
    {
        try {
            $this->validate([
                'newCourse.course_code' => 'required|string|max:50|unique:courses,course_code',
                'newCourse.course_name' => 'required|string|max:255',
                'newCourse.description' => 'nullable|string',
                'newCourse.program_id' => 'required|exists:programs,id',
                'newCourse.course_type_id' => 'nullable|exists:course_types,id',
            ], [
                'newCourse.course_code.required' => 'Course code is required.',
                'newCourse.course_code.unique' => 'This course code is already in use.',
                'newCourse.course_name.required' => 'Course name is required.',
                'newCourse.program_id.required' => 'Program is required.',
            ]);

            $course = Course::create([
                'course_code' => $this->newCourse['course_code'],
                'course_name' => $this->newCourse['course_name'],
                'description' => $this->newCourse['description'],
                'program_id' => $this->newCourse['program_id'],
                'course_type_id' => $this->newCourse['course_type_id'],
            ]);

            $courseName = $course->course_name;
            $this->closeAddCourseModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Course '{$courseName}' added successfully!"
            );
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            foreach ($errors as $error) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: $error
                );
            }
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'An unexpected error occurred: ' . $e->getMessage()
            );
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        
    }

    public function render()
    {
        $courses = Course::with(['assignments.professor', 'assignments.semester', 'program.college', 'courseType']) // Add college to eager load
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('course_code', 'like', '%' . $this->search . '%')
                      ->orWhere('course_name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('assignments.professor', function ($q) {
                          $q->where('firstname', 'like', '%' . $this->search . '%')
                            ->orWhere('lastname', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('program', function ($q) {
                          $q->where('program_name', 'like', '%' . $this->search . '%')
                            ->orWhere('program_code', 'like', '%' . $this->search . '%')
                            ->orWhereHas('college', function ($q) {
                                $q->where('name', 'like', '%' . $this->search . '%')
                                  ->orWhere('acronym', 'like', '%' . $this->search . '%');
                            });
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $professors = User::whereHas('roles', function($query) {
                $query->where('name', 'user');
            })
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        $semesters = Semester::orderBy('start_date', 'desc')->get();
        $programs = Program::with('college')->orderBy('program_name')->get(); // Load college with programs
        $courseTypes = CourseType::orderBy('name')->get();

        return view('livewire.admin.management.course-management', [
            'courses' => $courses,
            'professors' => $professors,
            'semesters' => $semesters,
            'programs' => $programs,
            'courseTypes' => $courseTypes,
        ]);
    }
}