<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseAssignment;
use App\Models\Semester;
use App\Models\Program;
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
        'program_id' => ''
    ];

    public $showEditCourseModal = false;
    public $editingCourse = [
        'id' => '',
        'course_code' => '',
        'course_name' => '',
        'program_id' => ''
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
            'program_id' => $course->program_id,
        ];
        
        $this->currentCourseAssignments = $course->assignments;
        
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
        if (empty($this->facultySearch)) {
            return User::whereHas('roles', function($query) {
                    $query->where('name', 'user');
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

        return User::whereHas('roles', function($query) {
                $query->where('name', 'user');
            })
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

            $existingAssignment = CourseAssignment::where('course_id', $this->editingCourse['id'])
                ->where('semester_id', $this->assignmentData['semester_id'])
                ->first();

            if ($existingAssignment) {
                $semesterName = Semester::find($this->assignmentData['semester_id'])->name ?? 'Selected Semester';
                
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: "This course already has a faculty assigned for {$semesterName}."
                );
                return;
            }

            CourseAssignment::create([
                'course_id' => $this->editingCourse['id'],
                'professor_id' => $this->assignmentData['professor_id'],
                'semester_id' => $this->assignmentData['semester_id'],
                'assignment_date' => now(),
            ]);

            $this->currentCourseAssignments = Course::with(['assignments.professor', 'assignments.semester'])
                ->find($this->editingCourse['id'])
                ->assignments;

            $professorName = User::find($this->assignmentData['professor_id'])->firstname . ' ' . User::find($this->assignmentData['professor_id'])->lastname;
            
            $this->assignmentData = [
                'professor_id' => '',
                'semester_id' => ''
            ];

            $this->clearFacultySelection();
            
            $this->resetErrorBag();
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Faculty '{$professorName}' assigned successfully!"
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
                'editingCourse.program_id' => 'required|exists:programs,id',
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
                'program_id' => $this->editingCourse['program_id'],
            ]);

            // If assignment data is provided, assign the faculty
            if (!empty($this->assignmentData['professor_id']) && !empty($this->assignmentData['semester_id'])) {
                $this->assignProfessor();
            }

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
                'newCourse.program_id' => 'required|exists:programs,id',
            ], [
                'newCourse.course_code.required' => 'Course code is required.',
                'newCourse.course_code.unique' => 'This course code is already in use.',
                'newCourse.course_name.required' => 'Course name is required.',
                'newCourse.program_id.required' => 'Program is required.',
            ]);

            $course = Course::create([
                'course_code' => $this->newCourse['course_code'],
                'course_name' => $this->newCourse['course_name'],
                'program_id' => $this->newCourse['program_id'],
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
        $courses = Course::with(['assignments.professor', 'assignments.semester', 'program'])
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
                            ->orWhere('program_code', 'like', '%' . $this->search . '%');
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
        $programs = Program::orderBy('program_name')->get();

        return view('livewire.admin.management.course-management', [
            'courses' => $courses,
            'professors' => $professors,
            'semesters' => $semesters,
            'programs' => $programs,
        ]);
    }
}