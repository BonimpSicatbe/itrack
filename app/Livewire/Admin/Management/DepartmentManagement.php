<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\Department;
use App\Models\College;
use Illuminate\Validation\Rule;

class DepartmentManagement extends Component
{
    public $search = '';
    
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    public $showAddDepartmentModal = false;
    public $newDepartment = [
        'name' => '',
        'college_id' => ''
    ];

    // Edit Department Modal Properties
    public $showEditDepartmentModal = false;
    public $editingDepartment = [
        'id' => '',
        'name' => '',
        'college_id' => ''
    ];

    // Delete Confirmation Properties
    public $showDeleteConfirmationModal = false;
    public $departmentToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function openAddDepartmentModal()
    {
        $this->showAddDepartmentModal = true;
        $this->reset('newDepartment');
        $this->resetErrorBag();
    }

    public function closeAddDepartmentModal()
    {
        $this->showAddDepartmentModal = false;
        $this->reset('newDepartment');
        $this->resetErrorBag();
    }

    // Edit Department Methods
    public function openEditDepartmentModal($departmentId)
    {
        $department = Department::with('college')->find($departmentId);
        
        $this->editingDepartment = [
            'id' => $department->id,
            'name' => $department->name,
            'college_id' => $department->college_id,
        ];
        
        $this->showEditDepartmentModal = true;
        $this->resetErrorBag();
    }

    public function closeEditDepartmentModal()
    {
        $this->showEditDepartmentModal = false;
        $this->reset('editingDepartment');
        $this->resetErrorBag();
    }

    // Delete Department Methods
    public function openDeleteConfirmationModal($departmentId)
    {
        $this->departmentToDelete = Department::find($departmentId);
        $this->showDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->showDeleteConfirmationModal = false;
        $this->departmentToDelete = null;
    }

    public function deleteDepartment()
    {
        if ($this->departmentToDelete) {
            $departmentName = $this->departmentToDelete->name;
            $this->departmentToDelete->delete();
            
            $this->closeDeleteConfirmationModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Department '{$departmentName}' deleted successfully!"
            );
        }
    }

    public function updateDepartment()
    {
        try {
            $this->validate([
                'editingDepartment.name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('departments', 'name')->ignore($this->editingDepartment['id'])
                ],
                'editingDepartment.college_id' => 'required|exists:colleges,id',
            ], [
                'editingDepartment.name.required' => 'Department name is required.',
                'editingDepartment.name.unique' => 'This department name is already in use.',
                'editingDepartment.college_id.required' => 'College selection is required.',
                'editingDepartment.college_id.exists' => 'Selected college does not exist.',
            ]);

            $department = Department::find($this->editingDepartment['id']);
            $departmentName = $department->name;
            
            $department->update([
                'name' => $this->editingDepartment['name'],
                'college_id' => $this->editingDepartment['college_id'],
            ]);

            $this->closeEditDepartmentModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Department '{$departmentName}' updated successfully!"
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

    public function addDepartment()
    {
        try {
            $this->validate([
                'newDepartment.name' => 'required|string|max:255|unique:departments,name',
                'newDepartment.college_id' => 'required|exists:colleges,id',
            ], [
                'newDepartment.name.required' => 'Department name is required.',
                'newDepartment.name.unique' => 'This department name is already in use.',
                'newDepartment.college_id.required' => 'College selection is required.',
                'newDepartment.college_id.exists' => 'Selected college does not exist.',
            ]);

            $department = Department::create([
                'name' => $this->newDepartment['name'],
                'college_id' => $this->newDepartment['college_id'],
            ]);

            $departmentName = $department->name;
            $this->closeAddDepartmentModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Department '{$departmentName}' added successfully!"
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
        $departments = Department::with('college')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('departments.name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('college', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('acronym', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $colleges = College::orderBy('name')->get();

        return view('livewire.admin.management.department-management', [
            'departments' => $departments,
            'colleges' => $colleges,
        ]);
    }
}