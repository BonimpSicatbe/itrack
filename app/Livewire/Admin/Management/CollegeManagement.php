<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\College;
use Illuminate\Validation\Rule;

class CollegeManagement extends Component
{
    public $search = '';
    
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    public $showAddCollegeModal = false;
    public $newCollege = [
        'name' => '',
        'acronym' => ''
    ];

    // Edit College Modal Properties
    public $showEditCollegeModal = false;
    public $editingCollege = [
        'id' => '',
        'name' => '',
        'acronym' => ''
    ];

    // Delete Confirmation Properties
    public $showDeleteConfirmationModal = false;
    public $collegeToDelete = null;

    public function openAddCollegeModal()
    {
        $this->showAddCollegeModal = true;
        $this->reset('newCollege');
        $this->resetErrorBag();
    }

    public function closeAddCollegeModal()
    {
        $this->showAddCollegeModal = false;
        $this->reset('newCollege');
        $this->resetErrorBag();
    }

    // Edit College Methods
    public function openEditCollegeModal($collegeId)
    {
        $college = College::find($collegeId);
        
        $this->editingCollege = [
            'id' => $college->id,
            'name' => $college->name,
            'acronym' => $college->acronym,
        ];
        
        $this->showEditCollegeModal = true;
        $this->resetErrorBag();
    }

    public function closeEditCollegeModal()
    {
        $this->showEditCollegeModal = false;
        $this->reset('editingCollege');
        $this->resetErrorBag();
    }

    // Delete College Methods
    public function openDeleteConfirmationModal($collegeId)
    {
        $this->collegeToDelete = College::find($collegeId);
        $this->showDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->showDeleteConfirmationModal = false;
        $this->collegeToDelete = null;
    }

    public function deleteCollege()
    {
        if ($this->collegeToDelete) {
            $collegeName = $this->collegeToDelete->name;
            $this->collegeToDelete->delete();
            
            $this->closeDeleteConfirmationModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "College '{$collegeName}' deleted successfully!"
            );
        }
    }

    public function updateCollege()
    {
        try {
            $this->validate([
                'editingCollege.name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('colleges', 'name')->ignore($this->editingCollege['id'])
                ],
                'editingCollege.acronym' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('colleges', 'acronym')->ignore($this->editingCollege['id'])
                ],
            ], [
                'editingCollege.name.required' => 'College name is required.',
                'editingCollege.name.unique' => 'This college name is already in use.',
                'editingCollege.acronym.required' => 'College acronym is required.',
                'editingCollege.acronym.unique' => 'This college acronym is already in use.',
            ]);

            $college = College::find($this->editingCollege['id']);
            $collegeName = $college->name;
            
            $college->update([
                'name' => $this->editingCollege['name'],
                'acronym' => $this->editingCollege['acronym'],
            ]);

            $this->closeEditCollegeModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "College '{$collegeName}' updated successfully!"
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

    public function addCollege()
    {
        try {
            $this->validate([
                'newCollege.name' => 'required|string|max:255|unique:colleges,name',
                'newCollege.acronym' => 'required|string|max:255|unique:colleges,acronym',
            ], [
                'newCollege.name.required' => 'College name is required.',
                'newCollege.name.unique' => 'This college name is already in use.',
                'newCollege.acronym.required' => 'College acronym is required.',
                'newCollege.acronym.unique' => 'This college acronym is already in use.',
            ]);

            $college = College::create([
                'name' => $this->newCollege['name'],
                'acronym' => $this->newCollege['acronym'],
            ]);

            $collegeName = $college->name;
            $this->closeAddCollegeModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "College '{$collegeName}' added successfully!"
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
        $colleges = College::when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('acronym', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return view('livewire.admin.management.college-management', [
            'colleges' => $colleges,
        ]);
    }
}