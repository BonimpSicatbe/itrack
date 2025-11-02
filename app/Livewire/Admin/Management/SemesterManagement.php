<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\Semester;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SemesterManagement extends Component
{
    public $errorMessage = null;

    public $search = '';

    public $sortField = 'start_date';
    public $sortDirection = 'desc';

    public $name = '';
    public $semester = '';
    public $academic_year = '';
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
        'academic_year' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'isActive' => 'boolean'
    ];

    protected $messages = [
        'name.required' => 'Semester name is required.',
        'academic_year.required' => 'Academic year is required.',
        'start_date.required' => 'Start date is required.',
        'end_date.required' => 'End date is required.',
        'end_date.after' => 'End date must be after start date.',
    ];

    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
        
        // Auto-update semester statuses based on current date
        $this->updateSemesterStatuses();
    }

    /**
     * Get available academic year options based on existing semesters
     */
    public function getAcademicYearOptions()
    {
        $currentYear = date('Y');
        $allOptions = [];
        
        // Generate options for current year -2 to current year +3
        for ($i = -1; $i <= 3; $i++) {
            $startYear = $currentYear + $i;
            $endYear = $startYear + 1;
            $allOptions[] = "{$startYear}-{$endYear}";
        }
        
        // Get existing semesters grouped by academic year
        $existingSemesters = Semester::all()->groupBy(function($semester) {
            return $this->extractAcademicYearFromName($semester->name);
        });
        
        // Filter out academic years that already have both First and Second semesters
        $availableOptions = [];
        foreach ($allOptions as $academicYear) {
            if (!isset($existingSemesters[$academicYear])) {
                // No semesters for this academic year, so it's available
                $availableOptions[] = $academicYear;
                continue;
            }
            
            $semestersInYear = $existingSemesters[$academicYear];
            $hasFirstSemester = false;
            $hasSecondSemester = false;
            
            foreach ($semestersInYear as $semester) {
                if (str_contains($semester->name, 'First Semester')) {
                    $hasFirstSemester = true;
                } elseif (str_contains($semester->name, 'Second Semester')) {
                    $hasSecondSemester = true;
                }
            }
            
            // If both First and Second semesters exist, exclude this academic year
            if (!($hasFirstSemester && $hasSecondSemester)) {
                $availableOptions[] = $academicYear;
            }
        }
        
        return $availableOptions;
    }

    /**
     * Update semester statuses based on current date
     * - Sets active semester if current date falls within a semester's date range
     * - Deactivates past semesters
     */
    private function updateSemesterStatuses()
    {
        $today = now()->format('Y-m-d');
        
        // Deactivate all semesters first
        Semester::where('is_active', true)->update(['is_active' => false]);
        
        // Find and activate the semester that contains today's date
        $currentSemester = Semester::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
            
        if ($currentSemester) {
            $currentSemester->update(['is_active' => true]);
        }
        
        // Deactivate past semesters (end_date is before today)
        Semester::where('end_date', '<', $today)
            ->update(['is_active' => false]);
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
        $this->reset(['name', 'semester', 'academic_year', 'start_date', 'end_date', 'isActive', 'errorMessage']);
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
        $this->academic_year = $this->extractAcademicYearFromName($this->editingSemester->name);
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

    /**
     * Extract academic year from semester name
     */
    private function extractAcademicYearFromName($name)
    {
        if (preg_match('/\|\s*(\d{4}-\d{4})$/', $name, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Check if a date range overlaps with any existing semester
     */
    private function checkDateOverlap($startDate, $endDate, $excludeId = null)
    {
        $query = Semester::where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function($q) use ($startDate, $endDate) {
                  $q->where('start_date', '<=', $startDate)
                    ->where('end_date', '>=', $endDate);
              });
        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if semester with same name and academic year already exists
     */
    private function checkDuplicateSemester($semesterType, $academicYear, $excludeId = null)
    {
        $semesterName = ucfirst($semesterType) . ($semesterType == 'midyear' ? '' : ' Semester') . ' | ' . $academicYear;
        
        $query = Semester::where('name', $semesterName);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if academic year is valid format
     */
    private function isValidAcademicYear($academicYear)
    {
        if (!preg_match('/^\d{4}-\d{4}$/', $academicYear)) {
            return false;
        }
        
        list($startYear, $endYear) = explode('-', $academicYear);
        
        // Check if years are consecutive and end year is start year + 1
        return ($endYear == $startYear + 1);
    }

    public function createSemester()
    {
        $validated = $this->validate([
            'semester' => 'required|in:first,second,midyear',
            'academic_year' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Log::info($validated);

        try {
            // Validate academic year format and validity
            if (!$this->isValidAcademicYear($this->academic_year)) {
                $this->errorMessage = "Academic year must be in valid format (e.g., 2025-2026) with consecutive years.";
                return;
            }

            // Check if academic year is available
            $availableYears = $this->getAcademicYearOptions();
            if (!in_array($this->academic_year, $availableYears)) {
                $this->errorMessage = "This academic year is not available or already has both semesters.";
                return;
            }

            // Check for duplicate semester (same type and academic year)
            if ($this->checkDuplicateSemester($this->semester, $this->academic_year)) {
                $semesterType = ucfirst($this->semester) . ($this->semester == 'midyear' ? '' : ' Semester');
                $this->errorMessage = "A {$semesterType} for academic year {$this->academic_year} already exists.";
                return;
            }

            // Check for date overlap with existing semesters
            if ($this->checkDateOverlap($this->start_date, $this->end_date)) {
                $this->errorMessage = "This semester's date range overlaps with an existing semester. Please choose different dates.";
                return;
            }

            // Construct name
            $semesterName = ucfirst($this->semester) . ($this->semester == 'midyear' ? '' : ' Semester') . ' | ' . $this->academic_year;

            // Check if this semester should be active (if current date falls within its range)
            $today = now()->format('Y-m-d');
            $shouldBeActive = ($this->start_date <= $today && $this->end_date >= $today);

            // If this semester should be active, deactivate all others
            if ($shouldBeActive) {
                Semester::where('is_active', true)->update(['is_active' => false]);
            }

            // Create new semester
            $semester = Semester::create([
                'name' => $semesterName,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'is_active' => $shouldBeActive,
            ]);

            Log::info('Semester created', [
                'id' => $semester->id,
                'name' => $semesterName,
                'user_id' => auth()->id() ?? null,
            ]);

            $this->reset(['semester', 'academic_year', 'start_date', 'end_date', 'errorMessage']);
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
        $this->academic_year = $this->extractAcademicYearFromName($this->editingSemester->name);
        $this->start_date = $this->editingSemester->start_date->format('Y-m-d');
        $this->end_date = $this->editingSemester->end_date->format('Y-m-d');
        $this->isActive = $this->editingSemester->is_active;
        $this->showEditModal = true;
        $this->resetErrorBag();
    }

    public function updateSemester()
    {
        $this->validate();

        // Extract semester type from current name
        $currentName = $this->editingSemester->name;
        $semesterType = '';
        
        if (str_contains($currentName, 'First Semester')) {
            $semesterType = 'first';
        } elseif (str_contains($currentName, 'Second Semester')) {
            $semesterType = 'second';
        } elseif (str_contains($currentName, 'Midyear')) {
            $semesterType = 'midyear';
        }

        // Validate academic year format
        if (!$this->isValidAcademicYear($this->academic_year)) {
            $this->addError('academic_year', "Academic year must be in valid format (e.g., 2025-2026) with consecutive years.");
            return;
        }

        // Check for duplicate semester (same type and academic year)
        if ($this->checkDuplicateSemester($semesterType, $this->academic_year, $this->editingSemester->id)) {
            $semesterTypeDisplay = ucfirst($semesterType) . ($semesterType == 'midyear' ? '' : ' Semester');
            $this->addError('academic_year', "A {$semesterTypeDisplay} for academic year {$this->academic_year} already exists.");
            return;
        }

        // Check for date overlap with other semesters
        if ($this->checkDateOverlap($this->start_date, $this->end_date, $this->editingSemester->id)) {
            $this->addError('start_date', 'This semester\'s date range overlaps with an existing semester.');
            return;
        }

        // Create new name with academic year
        $newName = ucfirst($semesterType) . ($semesterType == 'midyear' ? '' : ' Semester') . ' | ' . $this->academic_year;

        // Check if this semester should be active based on current date
        $today = now()->format('Y-m-d');
        $shouldBeActive = ($this->start_date <= $today && $this->end_date >= $today);

        // If this semester should be active, deactivate all others
        if ($shouldBeActive) {
            Semester::where('is_active', true)
                ->where('id', '!=', $this->editingSemester->id)
                ->update(['is_active' => false]);
            $this->isActive = true;
        } elseif ($this->isActive && !$shouldBeActive) {
            // If user manually set as active but dates don't match current date, respect manual choice
            Semester::where('is_active', true)
                ->where('id', '!=', $this->editingSemester->id)
                ->update(['is_active' => false]);
        } elseif (!$shouldBeActive) {
            // If shouldn't be active and user didn't manually set it, ensure it's inactive
            $this->isActive = false;
        }

        $this->editingSemester->update([
            'name' => $newName,
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
        // Auto-update semester statuses on every render
        $this->updateSemesterStatuses();

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