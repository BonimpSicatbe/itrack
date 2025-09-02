<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\Semester;

class SemesterManagement extends Component
{
    public $search = '';
    
    public $sortField = 'start_date';
    public $sortDirection = 'desc';
    
    public $name = '';
    public $start_date = '';
    public $end_date = '';
    public $isActive = false;
    public $editingSemester = null;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteConfirmationModal = false;
    public $semesterToDelete = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'isActive' => 'boolean'
    ];

    protected $messages = [
        'name.required' => 'Semester name is required.',
        'start_date.required' => 'Start date is required.',
        'end_date.required' => 'End date is required.',
        'end_date.after' => 'End date must be after start date.',
    ];

    public function mount()
    {
        // Initialization if needed
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

    // Add these methods for modal handling
    public function openCreateModal()
    {
        $this->reset(['name', 'start_date', 'end_date', 'isActive']);
        $this->showCreateModal = true;
        $this->resetErrorBag();
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function openEditModal($id)
    {
        $this->editingSemester = Semester::find($id);
        $this->name = $this->editingSemester->name;
        $this->start_date = $this->editingSemester->start_date->format('Y-m-d');
        $this->end_date = $this->editingSemester->end_date->format('Y-m-d');
        $this->isActive = $this->editingSemester->is_active;
        $this->showEditModal = true;
        $this->resetErrorBag();
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingSemester = null;
    }

    public function createSemester()
    {
        $this->validate();

        // Deactivate any currently active semester if this one is being set as active
        if ($this->isActive) {
            Semester::where('is_active', true)->update(['is_active' => false]);
        }

        Semester::create([
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->isActive,
        ]);

        $this->closeCreateModal();
        $this->dispatch('showNotification', 'success', 'Semester created successfully!');
    }

    public function editSemester($id)
    {
        $this->editingSemester = Semester::find($id);
        $this->name = $this->editingSemester->name;
        $this->start_date = $this->editingSemester->start_date->format('Y-m-d');
        $this->end_date = $this->editingSemester->end_date->format('Y-m-d');
        $this->isActive = $this->editingSemester->is_active;
        $this->showEditModal = true;
        $this->resetErrorBag();
    }

    public function updateSemester()
    {
        $this->validate();

        // Deactivate any currently active semester if this one is being set as active
        if ($this->isActive) {
            Semester::where('is_active', true)
                ->where('id', '!=', $this->editingSemester->id)
                ->update(['is_active' => false]);
        }

        $this->editingSemester->update([
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->isActive,
        ]);

        $this->closeEditModal();
        $this->dispatch('showNotification', 'success', 'Semester updated successfully!');
    }

    public function deleteSemester()
    {
        if ($this->semesterToDelete) {
            $semesterName = $this->semesterToDelete->name;
            $this->semesterToDelete->delete();
            
            $this->closeDeleteConfirmationModal();
            $this->dispatch('showNotification', 'success', "Semester '{$semesterName}' deleted successfully!");
        }
    }

    public function openDeleteConfirmationModal($id)
    {
        $this->semesterToDelete = Semester::find($id);
        
        // Prevent deletion of active semester
        if ($this->semesterToDelete->is_active) {
            $this->dispatch('showNotification', 'error', 'Cannot delete active semester!');
            return;
        }
        
        $this->showDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->showDeleteConfirmationModal = false;
        $this->semesterToDelete = null;
    }

    public function deleteSemesterDirect($id)
    {
        $semester = Semester::find($id);
        
        // Prevent deletion of active semester
        if ($semester->is_active) {
            $this->dispatch('showNotification', 'error', 'Cannot delete active semester!');
            return;
        }

        $semesterName = $semester->name;
        $semester->delete();
        $this->dispatch('showNotification', 'success', "Semester '{$semesterName}' deleted successfully!");
    }

    public function setActive($id)
    {
        // Deactivate all other semesters
        Semester::where('is_active', true)->update(['is_active' => false]);
        
        // Activate the selected semester
        $semester = Semester::find($id);
        $semester->update(['is_active' => true]);
        
        $this->dispatch('showNotification', 'success', 'Semester activated successfully!');
    }

    public function setInactive($id)
    {
        // Deactivate the selected semester
        $semester = Semester::find($id);
        $semester->update(['is_active' => false]);
        
        $this->dispatch('showNotification', 'success', 'Semester archived successfully!');
    }

    public function render()
    {
        $semesters = Semester::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return view('livewire.admin.management.semester-management', [
            'semesters' => $semesters,
        ]);
    }
}