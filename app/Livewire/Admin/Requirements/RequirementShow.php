<?php

namespace App\Livewire\Admin\Requirements;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\College;
use App\Models\Department;
use App\Models\Requirement;
use Illuminate\Support\Str;

class RequirementShow extends Component
{
    use WithFileUploads;

    public $requirement;
    public $assignedUsers;
    public $required_files = [];
    public $selectedViewFile = null;

    public function mount($requirement)
    {
        $this->requirement = $requirement;
        $this->assignedUsers = $requirement->assignedTargets();
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
                ->toMediaCollection('guides');
        }

        $this->reset('required_files');
        session()->flash('success', 'Required files uploaded successfully.');
    }

    public function downloadFile($fileId)
    {
        $file = $this->requirement->getMedia('guides')->find($fileId);
        if ($file) {
            return redirect()->route('guide.download', ['media' => $file->id]);
        }
        session()->flash('error', 'File not found.');
        return null;
    }

    public function isPreviewable($mimeType)
    {
        return Str::startsWith($mimeType, 'image/') ||
               Str::startsWith($mimeType, 'application/pdf') ||
               Str::startsWith($mimeType, 'text/');
    }

    public function render()
    {
        return view('livewire.admin.requirements.requirement-show', [
            'requirement' => $this->requirement,
            'assignedUsers' => $this->assignedUsers,
            'requiredFiles' => $this->requirement->getMedia('guides'),
            'colleges' => College::all(),
            'departments' => Department::all(),
            'requirements' => Requirement::all(),
        ]);
    }
}
