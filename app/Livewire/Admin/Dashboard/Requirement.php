<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Requirement as ModelsRequirement;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Requirement extends Component
{
    use WithFileUploads;

    public $name;
    public $description;
    public $due;
    public $required_files;
    public $target; // college or department
    public $target_id; // college or department

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'due' => 'required|date|after_or_equal:today',
        'required_files' => 'required|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav',
        'target' => 'required|in:college,department',
        'target_id' => 'required|integer',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function mount()
    {
        $this->target = $this->target; // default target
    }

    public function createRequirement()
    {
        $validated = $this->validate();

        $requirement = ModelsRequirement::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'due' => $validated['due'],
            'target' => $validated['target'],
            'target_id' => $validated['target_id'],
            'created_by' => Auth::id(),
        ]);

        dd($requirement);

        session()->flash('success', 'Requirement created successfully.');
        $this->reset(['name', 'description', 'due', 'required_files']);
    }

    public function updateRequirement($requirementId, $data)
    {
        // 1. update requirement by id with data
        // 2. update media collection with requirement id or name if needed
        session()->flash('success', 'Requirement updated successfully.');
    }

    public function deleteRequirement($requirementId)
    {
        // 1. delete requirement by id
        // 2. delete media collection with requirement id or name
        session()->flash('success', 'Requirement deleted successfully.');
    }


    public function render()
    {

        return view('livewire.admin.dashboard.requirement', [
            'target' => $this->target,
            'colleges' => \App\Models\College::all(),
            'departments' => \App\Models\Department::all(),
            'requirements' => ModelsRequirement::all(),
        ]);
    }
}
