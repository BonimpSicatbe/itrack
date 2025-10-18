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

    public function updatedFacultySearch()
    {
        $this->showFacultyDropdown = true;
        
        // If user clears the search, clear the selection
        if (empty($this->facultySearch)) {
            $this->assignmentData['professor_id'] = '';
        }
    }

    public function selectFaculty($professorId, $professorName)
    {
        $this->assignmentData['professor_id'] = $professorId;
        $this->facultySearch = $professorName; // This replaces the search text
        $this->showFacultyDropdown = false;
        
        // Force a re-render of the input field
        $this->dispatch('faculty-selected', facultyName: $professorName);
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

        if (empty($this->facultySearch)) {
            return User::whereHas('roles', function($query) {
                    $query->where('name', 'user');
                })
                ->where('is_active', true) // 🔥 ADDED: Only active users
                ->whereNotIn('id', $assignedProfessorIds) // 🔥 EXCLUDE ALREADY ASSIGNED PROFESSORS
                ->orderBy('firstname')
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

        return User::whereHas('roles', function($query) {
                $query->where('name', 'user');
            })
            ->where('is_active', true) // 🔥 ADDED: Only active users
            ->whereNotIn('id', $assignedProfessorIds) // 🔥 EXCLUDE ALREADY ASSIGNED PROFESSORS
            ->where(function($query) {
                $query->where('firstname', 'like', '%' . $this->facultySearch . '%')
                    ->orWhere('lastname', 'like', '%' . $this->facultySearch . '%')
                    ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ['%' . $this->facultySearch . '%']);
            })
            ->orderBy('firstname')
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

    public function assignProfessor()
    {
        try {
            $this->validate([
                'assignmentData.professor_id' => 'required|exists:users,id',
                'assignmentData.semester_id' => 'required|exists:semesters,id',
            ], [
                'assignmentData.professor_id.required' => 'Faculty selection is required.',
                'assignmentData.semester_id.required' => 'Semester selection is required.',
                'assignmentData.semester_id.exists' => 'Invalid semester selected.',
            ]);

            // 🔥 ADDED: Check if the selected professor is active
            $professor = User::find($this->assignmentData['professor_id']);
            if (!$professor || !$professor->is_active) {
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'Cannot assign courses to inactive faculty members. Please select an active faculty member.'
                );
                return false;
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
                return false; // Return false to indicate assignment failed
            }

            CourseAssignment::create([
                'course_id' => $this->editingCourse['id'],
                'professor_id' => $this->assignmentData['professor_id'],
                'semester_id' => $this->assignmentData['semester_id'],
                'assignment_date' => now(),
            ]);

            // 🔥 REFRESH the current assignments to include the newly assigned professor
            $this->currentCourseAssignments = Course::with(['assignments.professor', 'assignments.semester'])
                ->find($this->editingCourse['id'])
                ->assignments;

            // Refresh the selected course if it's the same course
            if ($this->selectedCourse && $this->selectedCourse->id == $this->editingCourse['id']) {
                $this->selectedCourse = Course::with([
                    'program.college', 
                    'courseType', 
                    'assignments.professor', 
                    'assignments.semester'
                ])->find($this->editingCourse['id']);
            }

            $professorName = User::find($this->assignmentData['professor_id'])->firstname . ' ' . User::find($this->assignmentData['professor_id'])->lastname;
            $semesterName = Semester::find($this->assignmentData['semester_id'])->name ?? 'the semester';
            
            $this->assignmentData = [
                'professor_id' => '',
                'semester_id' => ''
            ];

            $this->clearFacultySelection();
            
            $this->resetErrorBag();
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Faculty '{$professorName}' assigned to this course for {$semesterName} successfully!"
            );
            
            return true; // Return true to indicate assignment succeeded
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            foreach ($errors as $error) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: $error
                );
            }
            return false;
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'An unexpected error occurred: ' . $e->getMessage()
            );
            return false;
        }
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
            $this->validate([
                'editingCourse.course_code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('courses', 'course_code')->ignore($this->editingCourse['id'])
                ],
                'editingCourse.course_name' => 'required|string|max:255',
                'editingCourse.description' => 'nullable|string',
                'editingCourse.program_id' => 'required|exists:programs,id',
                'editingCourse.course_type_id' => 'nullable|exists:course_types,id',
            ], [
                'editingCourse.course_code.required' => 'Course code is required.',
                'editingCourse.course_code.unique' => 'This course code is already in use.',
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

            // Refresh the selected course if it's the same course being edited
            if ($this->selectedCourse && $this->selectedCourse->id == $course->id) {
                $this->selectedCourse = $course->fresh([
                    'program.college', 
                    'courseType', 
                    'assignments.professor', 
                    'assignments.semester'
                ]);
            }

            // 🔥 REMOVED: The automatic assignment call from here
            // Faculty assignment is now handled by the separate "Assign Faculty" button

            $this->closeEditCourseModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Course '{$courseName}' updated successfully!"
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