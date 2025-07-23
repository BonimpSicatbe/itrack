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

    public $assigned_to = '';
    public $name = '';
    public $description = '';
    public $due = '';
    public $priority = '';
    public $required_files = [];

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

        $this->assignedUsers = $requirement->assignedTargets();
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

        // Convert due date to Y-m-d H:i:s format
        $due = \DateTime::createFromFormat('Y-m-d\TH:i', $this->due);
        if (!$due) {
            $this->addError('due', 'Invalid date format.');
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

        session()->flash('success', 'Requirement updated successfully.');
    }

    public function uploadRequiredFiles()
    {
        $this->validate([
            'required_files' => 'nullable|array',
            'required_files.*' => 'file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav',
        ]);

        foreach ($this->required_files as $file) {
            $this->requirement->addMedia($file->getRealPath())
                ->usingFileName($file->getClientOriginalName())
                ->preservingOriginal()
                ->toMediaCollection('requirement/requirement_required_files');
        }

        $this->reset('required_files');
        session()->flash('success', 'Required files uploaded successfully.');
    }

    public function removeFile($fileId)
    {
        $file = $this->requirement->getMedia('requirement/requirement_required_files')->find($fileId);
        if ($file) {
            $file->delete();
            session()->flash('success', 'File removed successfully.');
        } else {
            session()->flash('error', 'File not found.');
        }
    }

    public function downloadFile($fileId)
    {
        $file = $this->requirement->getMedia('requirement/requirement_required_files')->find($fileId);
        if ($file) {
            return response()->download($file->getPath(), $file->file_name);
        } else {
            session()->flash('error', 'File not found.');
        }
    }

    public function render()
    {
        return view('livewire.admin.requirements.requirement-edit', [
            'requirement' => $this->requirement,
            'assignedUsers' => $this->assignedUsers,
            'requiredFiles' => $this->requirement->getMedia('requirement/requirement_required_files'),
            'colleges' => College::all(),
            'departments' => Department::all(),
        ]);
    }
}
