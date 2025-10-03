<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseAssignment;
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
        'description' => ''
    ];

    // Edit Course Modal Properties (now includes professor assignment)
    public $showEditCourseModal = false;
    public $editingCourse = [
        'id' => '',
        'course_code' => '',
        'course_name' => '',
        'description' => ''
    ];
    public $currentCourseAssignments = [];

    // Assignment Data (now used within edit modal)
    public $assignmentData = [
        'professor_id' => '',
        'year' => '',
        'semester' => ''
    ];

    // Delete Confirmation Properties
    public $showDeleteConfirmationModal = false;
    public $courseToDelete = null;

    // Remove Assignment Properties
    public $showRemoveAssignmentModal = false;
    public $assignmentToRemove = null;

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

    // Edit Course Methods (now includes professor assignment)
    public function openEditCourseModal($courseId)
    {
        $course = Course::with(['assignments.professor'])->find($courseId);
        
        $this->editingCourse = [
            'id' => $course->id,
            'course_code' => $course->course_code,
            'course_name' => $course->course_name,
            'description' => $course->description,
        ];
        
        $this->currentCourseAssignments = $course->assignments;
        
        // Reset assignment data
        $this->assignmentData = [
            'professor_id' => '',
            'year' => date('Y'),
            'semester' => '1st'
        ];
        
        $this->showEditCourseModal = true;
        $this->resetErrorBag();
    }

    public function closeEditCourseModal()
    {
        $this->showEditCourseModal = false;
        $this->reset('editingCourse');
        $this->reset('currentCourseAssignments');
        $this->reset('assignmentData');
        $this->resetErrorBag();
    }

    // Professor Assignment Methods (now within edit modal)
    public function assignProfessor()
    {
        try {
            $this->validate([
                'assignmentData.professor_id' => 'required|exists:users,id',
                'assignmentData.year' => 'required|integer|min:2000|max:2030',
                'assignmentData.semester' => 'required|in:1st,2nd,Summer',
            ], [
                'assignmentData.professor_id.required' => 'Professor selection is required.',
                'assignmentData.year.required' => 'Year is required.',
                'assignmentData.semester.required' => 'Semester is required.',
            ]);

            // Check if this course already has an assignment for the same year and semester
            $existingAssignment = CourseAssignment::where('course_id', $this->editingCourse['id'])
                ->where('year', $this->assignmentData['year'])
                ->where('semester', $this->assignmentData['semester'])
                ->first();

            if ($existingAssignment) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: "This course already has a professor assigned for {$this->assignmentData['semester']} Semester {$this->assignmentData['year']}."
                );
                return;
            }

            CourseAssignment::create([
                'course_id' => $this->editingCourse['id'],
                'professor_id' => $this->assignmentData['professor_id'],
                'year' => $this->assignmentData['year'],
                'semester' => $this->assignmentData['semester'],
                'assignment_date' => now(),
            ]);

            // Refresh the current assignments
            $this->currentCourseAssignments = Course::with(['assignments.professor'])
                ->find($this->editingCourse['id'])
                ->assignments;

            $professorName = User::find($this->assignmentData['professor_id'])->full_name;
            
            // Reset assignment form
            $this->assignmentData = [
                'professor_id' => '',
                'year' => date('Y'),
                'semester' => '1st'
            ];
            
            $this->resetErrorBag();
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Professor '{$professorName}' assigned successfully!"
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

    // Remove Assignment Methods
    public function openRemoveAssignmentModal($assignmentId)
    {
        $this->assignmentToRemove = CourseAssignment::with(['course', 'professor'])->find($assignmentId);
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
            $professorName = $this->assignmentToRemove->professor->full_name;
            
            $this->assignmentToRemove->delete();
            
            // Refresh current assignments if we're in edit mode
            if ($this->showEditCourseModal && $this->editingCourse['id']) {
                $this->currentCourseAssignments = Course::with(['assignments.professor'])
                    ->find($this->editingCourse['id'])
                    ->assignments;
            }
            
            $this->closeRemoveAssignmentModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Professor '{$professorName}' removed from '{$courseName}' successfully!"
            );
        }
    }

    // Delete Course Methods
    public function openDeleteConfirmationModal($courseId)
    {
        $this->courseToDelete = Course::find($courseId);
        $this->showDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->showDeleteConfirmationModal = false;
        $this->courseToDelete = null;
    }

    public function deleteCourse()
    {
        if ($this->courseToDelete) {
            $courseName = $this->courseToDelete->course_name;
            $this->courseToDelete->delete();
            
            $this->closeDeleteConfirmationModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Course '{$courseName}' deleted successfully!"
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
            ], [
                'editingCourse.course_code.required' => 'Course code is required.',
                'editingCourse.course_code.unique' => 'This course code is already in use.',
                'editingCourse.course_name.required' => 'Course name is required.',
            ]);

            $course = Course::find($this->editingCourse['id']);
            $courseName = $course->course_name;
            
            $course->update([
                'course_code' => $this->editingCourse['course_code'],
                'course_name' => $this->editingCourse['course_name'],
                'description' => $this->editingCourse['description'],
            ]);

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
            ], [
                'newCourse.course_code.required' => 'Course code is required.',
                'newCourse.course_code.unique' => 'This course code is already in use.',
                'newCourse.course_name.required' => 'Course name is required.',
            ]);

            $course = Course::create([
                'course_code' => $this->newCourse['course_code'],
                'course_name' => $this->newCourse['course_name'],
                'description' => $this->newCourse['description'],
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
        // No need to reset page since we removed pagination
    }

    public function render()
    {
        $courses = Course::with(['assignments.professor'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('course_code', 'like', '%' . $this->search . '%')
                      ->orWhere('course_name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('assignments.professor', function ($q) {
                          $q->where('firstname', 'like', '%' . $this->search . '%')
                            ->orWhere('lastname', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $professors = User::whereHas('department') // Assuming professors have department assigned
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('livewire.admin.management.course-management', [
            'courses' => $courses,
            'professors' => $professors,
        ]);
    }
}