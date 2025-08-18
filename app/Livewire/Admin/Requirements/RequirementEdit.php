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

    // Form fields
    public $assigned_to = '';
    public $name = '';
    public $description = '';
    public $due = '';
    public $priority = '';
    public $required_files = [];
    public $showUploadModal = false;

    #[Validate('required|in:college,department')]
    public $sector = '';
    public $search = '';

    public function mount($requirement)
    {
        $this->requirement = $requirement;
        $this->assigned_to = $requirement->assigned_to;
        $this->name = $requirement->name;
        $this->description = $requirement->description;
        $this->due = $requirement->due->format('Y-m-d\TH:i');
        $this->priority = $requirement->priority;
        $this->sector = College::where('name', $requirement->assigned_to)->exists() ? 'college' : 'department';
        
        // Load assigned users with their relationships
        $this->assignedUsers = $requirement->assignedTargets()->map(function($user) {
        return $user->load(['department', 'college']);
    });
    }

    public function updateRequirement()
    {
        $this->validate([
            'assigned_to' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:requirements,name,' . $this->requirement->id,
            'description' => 'required|string',
            'due' => 'required|date_format:Y-m-d\TH:i|after_or_equal:now',
            'priority' => 'required|in:low,normal,high',
        ]);

        $due = \DateTime::createFromFormat('Y-m-d\TH:i', $this->due);
        if (!$due) {
            $this->dispatch('showNotification', type: 'error', content: 'Invalid date format.');
            return;
        }

        $this->requirement->update([
            'updated_by' => Auth::id(),
            'assigned_to' => $this->assigned_to,
            'name' => $this->name,
            'description' => $this->description,
            'due' => $due->format('Y-m-d H:i:s'),
            'priority' => $this->priority,
        ]);

        $this->assignedUsers = $this->requirement->assignedTargets();
        $this->dispatch('showNotification', 
            type: 'success', 
            content: 'Requirement updated successfully.'
        );
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

    public function removeFile($fileId)
    {
        try {
            $file = $this->requirement->getMedia('guides')->find($fileId);
            
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
            'colleges' => College::all(),
            'departments' => Department::all(),
        ]);
    }
}