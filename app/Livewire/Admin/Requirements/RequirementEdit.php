<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class RequirementEdit extends Component
{
    use WithFileUploads;

    public $requirement;
    public $assignedUsers;
    public $assignedPrograms = [];

    // Form fields
    public $name = '';
    public $description = '';
    public $due = '';
    public $priority = '';
    public $required_files = [];
    public $showUploadModal = false;
    
    // Assignment properties (multiple selection)
    public $selectedPrograms = [];
    public $selectAllPrograms = false;
    
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
        $this->assignedUsers = $this->getAssignedUsers();
    }

    private function parseAssignedData()
    {
        // assigned_to is already an array due to the cast in Requirement model
        $assignedTo = $this->requirement->assigned_to ?? [];
        
        // Get assigned programs
        if (isset($assignedTo['programs']) && is_array($assignedTo['programs'])) {
            $this->selectedPrograms = $assignedTo['programs'];
            $this->assignedPrograms = Program::whereIn('id', $assignedTo['programs'])
                ->with('college')
                ->get();
        }

        // Handle select all case
        $this->selectAllPrograms = $assignedTo['selectAllPrograms'] ?? false;
    }

    private function getAssignedUsers()
    {
        $assignedTo = $this->requirement->assigned_to ?? [];
        
        $userQuery = \App\Models\User::query()->with([
            'department', 
            'college',
            'courseAssignments.course.program.college'
        ]);
        
        $hasConditions = false;
        
        // Specific programs assigned - get users through course assignments
        if (isset($assignedTo['programs']) && is_array($assignedTo['programs'])) {
            // Get courses that belong to the assigned programs
            $courseIds = \App\Models\Course::whereIn('program_id', $assignedTo['programs'])
                ->pluck('id')
                ->toArray();
            
            if (!empty($courseIds)) {
                // Get users who are assigned to these courses in the current semester
                $userQuery->whereHas('courseAssignments', function ($query) use ($courseIds) {
                    $query->whereIn('course_id', $courseIds);
                    if ($this->requirement->semester_id) {
                        $query->where('semester_id', $this->requirement->semester_id);
                    }
                });
                $hasConditions = true;
            }
        }
        
        // Handle "select all" case - get all users with course assignments
        if (isset($assignedTo['selectAllPrograms']) && $assignedTo['selectAllPrograms']) {
            $userQuery->whereHas('courseAssignments', function ($query) {
                if ($this->requirement->semester_id) {
                    $query->where('semester_id', $this->requirement->semester_id);
                }
            });
            $hasConditions = true;
        }
        
        if (!$hasConditions) {
            return collect();
        }
        
        return $userQuery->get();
    }

    public function updateRequirement()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:requirements,name,' . $this->requirement->id,
            'due' => 'required|date_format:Y-m-d\TH:i|after_or_equal:now',
            'priority' => 'required|in:low,normal,high',
            'selectedPrograms' => ['required', 'array', 'min:1'],
            'selectedPrograms.*' => ['exists:programs,id'],
        ]);

        $due = \DateTime::createFromFormat('Y-m-d\TH:i', $this->due);
        if (!$due) {
            $this->dispatch('showNotification', type: 'error', content: 'Invalid date format.');
            return;
        }

        // Prepare assignment data - no need to json_encode() since it's cast to array
        $assignedData = [
            'programs' => $this->selectedPrograms,
            'selectAllPrograms' => $this->selectAllPrograms,
        ];

        $this->requirement->update([
            'updated_by' => Auth::id(),
            'assigned_to' => $assignedData, // No json_encode() needed - cast handles it
            'name' => $this->name,
            'description' => $this->description,
            'due' => $due->format('Y-m-d H:i:s'),
            'priority' => $this->priority,
        ]);

        // Refresh assigned data
        $this->parseAssignedData();
        $this->assignedUsers = $this->getAssignedUsers();
        
        $this->dispatch('showNotification', 
            type: 'success', 
            content: 'Requirement updated successfully.'
        );
    }

    // Assignment hooks
    public function updatedSelectAllPrograms($value)
    {
        if ($value) {
            $this->selectedPrograms = Program::all()->pluck('id')->toArray();
        } else {
            $this->selectedPrograms = [];
        }
    }

    public function updatedSelectedPrograms()
    {
        $allPrograms = Program::all()->pluck('id')->toArray();
        $this->selectAllPrograms = !empty($allPrograms) && 
                                 count($this->selectedPrograms) === count($allPrograms);
    }

    // Computed property for programs
    public function getProgramsProperty()
    {
        return Program::with('college')->get();
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
            'programs' => $this->programs,
            'assignedPrograms' => $this->assignedPrograms,
        ]);
    }
}