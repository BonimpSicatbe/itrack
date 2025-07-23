<?php

namespace App\Livewire\Admin\Requirements;

use Livewire\Component;
use Livewire\WithFileUploads;

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

    public function showUser($user) {
        return redirect()->route('admin.users.show', $user);
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

    public function downloadFile($fileId)
    {
        $file = $this->requirement->getMedia('requirement/requirement_required_files')->find($fileId);
        if ($file) {
            return response()->download($file->getPath(), $file->file_name);
        }
        session()->flash('error', 'File not found.');
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

    public function render()
    {
        return view('livewire.admin.requirements.requirement-show', [
            'requirement' => $this->requirement,
            'assignedUsers' => $this->assignedUsers,
            'requiredFiles' => $this->requirement->getMedia('requirement/requirement_required_files'),
        ]);
    }
}
