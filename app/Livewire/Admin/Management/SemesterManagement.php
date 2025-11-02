<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\Semester;
use Illuminate\Support\Facades\Log;

class SemesterManagement extends Component
{
    public $errorMessage = null;

    public $search = '';

    public $sortField = 'start_date';
    public $sortDirection = 'desc';

    public $name = '';
    public $semester = '';
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
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
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
        $validated = $this->validate([
            'semester' => 'required|in:first,second,midyear',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Log::info($validated);

        try {
            $startYear = date('Y', strtotime($this->start_date));
            $endYear = date('Y', strtotime($this->end_date));
            $currentYear = date('Y');

            // Prevent creating semesters in previous years
            if ($endYear < $currentYear) {
                $this->errorMessage = "You cannot create a semester in previous years. The end year must be {$currentYear} or later.";
                return;
            }

            // Create year range - handle same year or spanning years
            $yearRange = ($startYear === $endYear) ? $startYear : $startYear . '-' . $endYear;

            // Check for duplicate semester TYPE in the same year range
            $existingSemester = Semester::where(function ($query) use ($startYear, $endYear) {
                $query->whereRaw("YEAR(start_date) = ?", [$startYear])
                    ->whereRaw("YEAR(end_date) = ?", [$endYear]);
            })
                ->where(function ($query) {
                    if ($this->semester === 'first') {
                        $query->where('name', 'like', '%First Semester%');
                    } elseif ($this->semester === 'second') {
                        $query->where('name', 'like', '%Second Semester%');
                    } else {
                        $query->where('name', 'like', '%Midyear%');
                    }
                })
                ->first();

            if ($existingSemester) {
                $semesterType = ucfirst($this->semester) . ($this->semester == 'midyear' ? '' : ' Semester');
                $this->errorMessage = "A {$semesterType} with the year range ({$yearRange}) already exists.";
                return;
            }

            // Removed date overlap validation to allow multiple semesters in the same year range
            // Only the semester TYPE needs to be unique for a given year range

            // Construct name
            $semesterName = ucfirst($this->semester) . ($this->semester == 'midyear' ? '' : ' Semester') . ' | ' . $yearRange;

            // Deactivate previous active semesters
            $setPrevSemester = Semester::where('is_active', true)->update(['is_active' => false]);
            Log::info('Previous active semesters deactivated', [
                'updated_rows' => $setPrevSemester,
            ]);

            // Create new semester
            $semester = Semester::create([
                'name' => $semesterName,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'is_active' => true,
            ]);

            Log::info('Semester created', [
                'id' => $semester->id,
                'name' => $semesterName,
                'user_id' => auth()->id() ?? null,
            ]);

            $this->reset(['semester', 'start_date', 'end_date', 'errorMessage']); // clears inputs
            $this->dispatch('closeModal', modalId: 'create_semester_modal');
            $this->dispatch('showNotification', 'success', 'Semester created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create semester', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id() ?? null,
            ]);
            $this->dispatch('showNotification', 'error', 'Failed to create semester. Please try again.');
        }
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
