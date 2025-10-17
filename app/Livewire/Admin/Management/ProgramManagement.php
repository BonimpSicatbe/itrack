<?php
// app/Livewire/Admin/Management/ProgramManagement.php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\Program;
use App\Models\College;
use App\Models\Course;
use Illuminate\Validation\Rule;

class ProgramManagement extends Component
{
    public $search = '';
    
    public $sortField = 'program_code';
    public $sortDirection = 'asc';
    
    public $showAddProgramModal = false;
    public $newProgram = [
        'program_code' => '',
        'program_name' => '',
        'description' => '',
        'college_id' => ''
    ];

    public $showEditProgramModal = false;
    public $editingProgram = [
        'id' => '',
        'program_code' => '',
        'program_name' => '',
        'description' => '',
        'college_id' => ''
    ];

    public $showDeleteConfirmationModal = false;
    public $programToDelete = null;

    public $showProgramDetailsModal = false;
    public $selectedProgram = null;
    public $programCourses = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'program_code'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function openAddProgramModal()
    {
        $this->showAddProgramModal = true;
        $this->reset('newProgram');
        $this->resetErrorBag();
    }

    public function closeAddProgramModal()
    {
        $this->showAddProgramModal = false;
        $this->reset('newProgram');
        $this->resetErrorBag();
    }

    public function openEditProgramModal($programId)
    {
        $program = Program::with('college')->find($programId);
        
        $this->editingProgram = [
            'id' => $program->id,
            'program_code' => $program->program_code,
            'program_name' => $program->program_name,
            'description' => $program->description,
            'college_id' => $program->college_id,
        ];
        
        $this->showEditProgramModal = true;
        $this->resetErrorBag();
    }

    public function closeEditProgramModal()
    {
        $this->showEditProgramModal = false;
        $this->reset('editingProgram');
        $this->resetErrorBag();
    }

    public function openDeleteConfirmationModal($programId)
    {
        $this->programToDelete = Program::find($programId);
        $this->showDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->showDeleteConfirmationModal = false;
        $this->programToDelete = null;
    }

    public function openProgramDetailsModal($programId)
    {
        $this->selectedProgram = Program::with(['college', 'courses.courseType', 'courses.assignments.professor', 'courses.assignments.semester'])->find($programId);
        
        if ($this->selectedProgram) {
            $this->programCourses = $this->selectedProgram->courses->map(function($course) {
                return [
                    'course_code' => $course->course_code,
                    'course_name' => $course->course_name,
                    'course_type' => $course->courseType ? $course->courseType->name : 'N/A',
                    'description' => $course->description,
                    'current_assignment' => $course->assignments->sortByDesc('assignment_date')->first()
                ];
            });
            
            $this->showProgramDetailsModal = true;
        }
    }

    public function closeProgramDetailsModal()
    {
        $this->showProgramDetailsModal = false;
        $this->selectedProgram = null;
        $this->programCourses = [];
    }

    public function updateProgram()
    {
        try {
            $this->validate([
                'editingProgram.program_code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('programs', 'program_code')->ignore($this->editingProgram['id'])
                ],
                'editingProgram.program_name' => 'required|string|max:255',
                'editingProgram.description' => 'nullable|string',
                'editingProgram.college_id' => 'required|exists:colleges,id',
            ], [
                'editingProgram.program_code.required' => 'Program code is required.',
                'editingProgram.program_code.unique' => 'This program code is already in use.',
                'editingProgram.program_name.required' => 'Program name is required.',
                'editingProgram.college_id.required' => 'College selection is required.',
            ]);

            $program = Program::find($this->editingProgram['id']);
            $programName = $program->program_name;
            
            $program->update([
                'program_code' => $this->editingProgram['program_code'],
                'program_name' => $this->editingProgram['program_name'],
                'description' => $this->editingProgram['description'],
                'college_id' => $this->editingProgram['college_id'],
            ]);

            // Refresh the selected program if it's the same program being edited
            if ($this->selectedProgram && $this->selectedProgram->id == $program->id) {
                $this->selectedProgram = $program->fresh(['college', 'courses.courseType', 'courses.assignments.professor', 'courses.assignments.semester']);
                $this->programCourses = $this->selectedProgram->courses->map(function($course) {
                    return [
                        'course_code' => $course->course_code,
                        'course_name' => $course->course_name,
                        'course_type' => $course->courseType ? $course->courseType->name : 'N/A',
                        'description' => $course->description,
                        'current_assignment' => $course->assignments->sortByDesc('assignment_date')->first()
                    ];
                });
            }

            $this->closeEditProgramModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Program '{$programName}' updated successfully!"
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

    public function addProgram()
    {
        try {
            $this->validate([
                'newProgram.program_code' => 'required|string|max:50|unique:programs,program_code',
                'newProgram.program_name' => 'required|string|max:255',
                'newProgram.description' => 'nullable|string',
                'newProgram.college_id' => 'required|exists:colleges,id',
            ], [
                'newProgram.program_code.required' => 'Program code is required.',
                'newProgram.program_code.unique' => 'This program code is already in use.',
                'newProgram.program_name.required' => 'Program name is required.',
                'newProgram.college_id.required' => 'College selection is required.',
            ]);

            $program = Program::create([
                'program_code' => $this->newProgram['program_code'],
                'program_name' => $this->newProgram['program_name'],
                'description' => $this->newProgram['description'],
                'college_id' => $this->newProgram['college_id'],
            ]);

            $programName = $program->program_name;
            $this->closeAddProgramModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Program '{$programName}' added successfully!"
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

    public function deleteProgram()
    {
        if ($this->programToDelete) {
            $programName = $this->programToDelete->program_name;
            
            // Check if program has courses
            if ($this->programToDelete->courses()->exists()) {
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: "Cannot delete program '{$programName}' because it has associated courses."
                );
                $this->closeDeleteConfirmationModal();
                return;
            }
            
            $this->programToDelete->delete();
            
            $this->closeDeleteConfirmationModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Program '{$programName}' deleted successfully!"
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
        // Search debounce is handled by wire:model.live.debounce
    }

    public function render()
    {
        $programs = Program::with('college')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('program_code', 'like', '%' . $this->search . '%')
                      ->orWhere('program_name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('college', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $colleges = College::orderBy('name')->get();

        return view('livewire.admin.management.program-management', [
            'programs' => $programs,
            'colleges' => $colleges,
        ]);
    }
}