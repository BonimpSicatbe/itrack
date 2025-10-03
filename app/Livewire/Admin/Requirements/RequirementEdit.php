<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\College;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class RequirementEdit extends Component
{
    use WithFileUploads;

    public $requirement;
    public $assignedUsers;
    public $assignedColleges = [];
    public $assignedDepartments = [];

    // Form fields
    public $name = '';
    public $description = '';
    public $due = '';
    public $priority = '';
    public $required_files = [];
    public $showUploadModal = false;
    
    // Assignment properties (multiple selection)
    public $selectedColleges = [];
    public $selectedDepartments = [];
    public $selectAllColleges = false;
    public $selectAllDepartments = false;
    
    // Add these properties for delete confirmation
    public $showDeleteModal = false;
    public $fileToDelete = null;

    public function mount($requirement)
    {
        $this->requirement = $requirement;
        $this->name = $requirement->name;
        $this->description = $requirement->description;
        $this->due = $requirement->due->format('Y-m-d\TH:i');
        $this->priority = $requirement->priority;
        
        // Parse assigned data for multiple selection
        $this->parseAssignedData();
        
        // Load assigned users with their relationships
        $this->assignedUsers = $requirement->assignedTargets()->map(function($user) {
            return $user->load(['department', 'college']);
        });
    }

    private function parseAssignedData()
    {
        $assignedTo = json_decode($this->requirement->assigned_to, true) ?? [];
        
        // Get assigned colleges
        if (isset($assignedTo['colleges']) && is_array($assignedTo['colleges'])) {
            $this->selectedColleges = $assignedTo['colleges'];
            $this->assignedColleges = College::whereIn('id', $assignedTo['colleges'])->get();
        }
        
        // Get assigned departments
        if (isset($assignedTo['departments']) && is_array($assignedTo['departments'])) {
            $this->selectedDepartments = $assignedTo['departments'];
            $this->assignedDepartments = Department::whereIn('id', $assignedTo['departments'])
                ->with('college')
                ->get();
        }

        // Handle select all cases
        $this->selectAllColleges = $assignedTo['selectAllColleges'] ?? false;
        $this->selectAllDepartments = $assignedTo['selectAllDepartments'] ?? false;
    }

    public function updateRequirement()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:requirements,name,' . $this->requirement->id,
            'due' => 'required|date_format:Y-m-d\TH:i|after_or_equal:now',
            'priority' => 'required|in:low,normal,high',
            'selectedColleges' => ['required', 'array', 'min:1'],
            'selectedColleges.*' => ['exists:colleges,id'],
            'selectedDepartments' => ['sometimes', 'array'],
            'selectedDepartments.*' => ['exists:departments,id'],
        ]);

        $due = \DateTime::createFromFormat('Y-m-d\TH:i', $this->due);
        if (!$due) {
            $this->dispatch('showNotification', type: 'error', content: 'Invalid date format.');
            return;
        }

        // Prepare assignment data
        $assignedData = [
            'colleges' => $this->selectedColleges,
            'departments' => $this->selectedDepartments,
            'selectAllColleges' => $this->selectAllColleges,
            'selectAllDepartments' => $this->selectAllDepartments
        ];

        $this->requirement->update([
            'updated_by' => Auth::id(),
            'assigned_to' => json_encode($assignedData),
            'name' => $this->name,
            'description' => $this->description,
            'due' => $due->format('Y-m-d H:i:s'),
            'priority' => $this->priority,
        ]);

        // Refresh assigned data
        $this->parseAssignedData();
        $this->assignedUsers = $this->requirement->assignedTargets();
        
        $this->dispatch('showNotification', 
            type: 'success', 
            content: 'Requirement updated successfully.'
        );
    }

    // Assignment hooks (same as RequirementCreate)
    public function updatedSelectAllColleges($value)
    {
        if ($value) {
            $this->selectedColleges = College::all()->pluck('id')->toArray();
            $this->selectedDepartments = $this->getDepartmentsProperty()->pluck('id')->toArray();
            $this->selectAllDepartments = true;
        } else {
            $this->selectedColleges = [];
            $this->selectedDepartments = [];
            $this->selectAllDepartments = false;
        }
    }

    public function updatedSelectedColleges()
    {
        $this->selectAllColleges = false;
        $this->selectAllDepartments = false;
        
        // Reset departments when colleges change
        if (empty($this->selectedColleges)) {
            $this->selectedDepartments = [];
        } else {
            // Keep only departments that belong to selected colleges
            $validDepartments = $this->getDepartmentsProperty()->pluck('id')->toArray();
            $this->selectedDepartments = array_intersect($this->selectedDepartments, $validDepartments);
        }
    }

    public function updatedSelectAllDepartments($value)
    {
        if ($value && (!empty($this->selectedColleges) || $this->selectAllColleges)) {
            $this->selectedDepartments = $this->getDepartmentsProperty()->pluck('id')->toArray();
        } else {
            $this->selectedDepartments = [];
        }
    }

    public function updatedSelectedDepartments()
    {
        $this->selectAllDepartments = false;
        
        if (!empty($this->selectedColleges) || $this->selectAllColleges) {
            $allDepartments = $this->getDepartmentsProperty()->pluck('id')->toArray();
            $this->selectAllDepartments = !empty($allDepartments) && 
                                         count($this->selectedDepartments) === count($allDepartments);
        }
    }

    // Computed properties for colleges and departments
    public function getCollegesProperty()
    {
        return College::with('departments')->get();
    }

    public function getDepartmentsProperty()
    {
        if ($this->selectAllColleges) {
            return Department::with('college')->get();
        }
        if (!empty($this->selectedColleges)) {
            return Department::with('college')->whereIn('college_id', $this->selectedColleges)->get();
        }
        return collect();
    }

    public function uploadRequiredFiles()
    {
        $this->validate([
            'required_files' => 'nullable|array',
            'required_files.*' => 'file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav',
        ]);

        try {
            foreach ($this->required_files as $file) {
                $this->requirement->addMedia($file->getRealPath())
                    ->usingFileName($file->getClientOriginalName())
                    ->preservingOriginal()
                    ->toMediaCollection('guides');
            }

            $this->reset(['required_files', 'showUploadModal']);
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'Files uploaded successfully.'
            );
            
            // Refresh the media collection
            $this->requirement->refresh();
            
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Upload failed: ' . $e->getMessage()
            );
        }
    }

    public function confirmFileRemoval($fileId)
    {
        $this->fileToDelete = $fileId;
        $this->showDeleteModal = true;
    }

    public function removeFile()
    {
        try {
            $file = $this->requirement->getMedia('guides')->find($this->fileToDelete);
            
            if ($file) {
                $file->delete();
                $this->dispatch('showNotification', 
                    type: 'success', 
                    content: 'File removed successfully.'
                );
                
                // Refresh the media collection
                $this->requirement->load('media');
            } else {
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'File not found.'
                );
            }
        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Error removing file: ' . $e->getMessage()
            );
        } finally {
            $this->reset(['showDeleteModal', 'fileToDelete']);
        }
    }

    public function isPreviewable($mimeType)
    {
        return str_starts_with($mimeType, 'image/') || 
               str_starts_with($mimeType, 'application/pdf') ||
               str_starts_with($mimeType, 'text/');
    }

    public function render()
    {
        // Explicitly load the media relationship if not already loaded
        if (!$this->requirement->relationLoaded('media')) {
            $this->requirement->load('media');
        }

        return view('livewire.admin.requirements.requirement-edit', [
            'requirement' => $this->requirement,
            'assignedUsers' => $this->assignedUsers,
            'requiredFiles' => $this->requirement->getMedia('guides'),
            'colleges' => $this->colleges,
            'departments' => $this->departments,
            'assignedColleges' => $this->assignedColleges,
            'assignedDepartments' => $this->assignedDepartments,
        ]);
    }
}