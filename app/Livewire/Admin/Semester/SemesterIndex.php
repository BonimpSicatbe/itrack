<?php

namespace App\Livewire\Admin\Semester;

use App\Models\Semester;
use Livewire\Component;

class SemesterIndex extends Component
{
    public $name;
    public $start_date;
    public $end_date;
    public $is_active = false;
    public $editMode = false;
    public $semesterId;
    
    // Modal control properties
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $isDeleting = false;
    public $isSaving = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
    ];

    public function render()
    {
        $semesters = Semester::latest()->get();
        return view('livewire.admin.semester.semester-index', compact('semesters'));
    }

    public function save()
    {
        $this->validate();

        $this->isSaving = true;

        $data = [
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];

        if ($this->is_active) {
            Semester::query()->update(['is_active' => false]);
            $data['is_active'] = true;
        }

        try {
            if ($this->editMode) {
                $semester = Semester::findOrFail($this->semesterId);
                $semester->update($data);
                $this->dispatch('showNotification', 
                    type: 'success', 
                    content: 'Semester updated successfully',
                    duration: 3000
                );
            } else {
                Semester::create($data);
                $this->dispatch('showNotification', 
                    type: 'success', 
                    content: 'Semester created successfully',
                    duration: 3000
                );
            }

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Error saving semester: ' . $e->getMessage(),
                duration: 5000
            );
        } finally {
            $this->isSaving = false;
        }
    }


    public function edit($id)
    {
        $semester = Semester::findOrFail($id);
        $this->semesterId = $id;
        $this->name = $semester->name;
        $this->start_date = $semester->start_date;
        $this->end_date = $semester->end_date;
        $this->is_active = $semester->is_active;
        $this->editMode = true;
        $this->showEditModal = true;
    }

    public function confirmDelete($id)
    {
        $this->semesterId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteSemester()
    {
        $this->isDeleting = true;
        
        try {
            Semester::findOrFail($this->semesterId)->delete();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'Semester deleted successfully',
                duration: 3000
            );
            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to delete semester: ' . $e->getMessage(),
                duration: 5000
            );
        } finally {
            $this->isDeleting = false;
        }
    }

    public function setActive($id)
    {
        try {
            Semester::query()->update(['is_active' => false]);
            $semester = Semester::findOrFail($id);
            $semester->update(['is_active' => true]);
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'Semester activated successfully',
                duration: 3000
            );
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to activate semester: ' . $e->getMessage(),
                duration: 5000
            );
        }
    }

    public function closeModal()
    {
        $this->reset(['name', 'start_date', 'end_date', 'is_active', 'editMode', 'semesterId', 'showEditModal', 'showDeleteModal']);
    }
}