<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Requirement as ModelsRequirement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
    public $target_id; // college_id or department_id

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
        Log::info('Starting requirement creation', [
            'user_id' => Auth::id(),
            'input' => [
                'name' => $this->name,
                'description' => $this->description,
                'due' => $this->due,
                'target' => $this->target,
                'target_id' => $this->target_id,
            ]
        ]);

        $validated = $this->validate();

        Log::info('Validation passed', $validated);

        $requirement = ModelsRequirement::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'due' => $validated['due'],
            'target' => $validated['target'],
            'target_id' => $validated['target_id'],
            'created_by' => Auth::id(),
        ]);

        Log::info('Requirement created', ['requirement_id' => $requirement->id]);

        $media = $requirement->addMedia($validated['required_files'])
            ->toMediaCollection('requirements');

        Log::info('Media added to requirement', [
            'requirement_id' => $requirement->id,
            'media_id' => $media->id ?? null
        ]);

        // dd('success', $requirement->getMedia('requirements'));

        session()->flash('success', 'Requirement created successfully.');
        $this->reset(['name', 'description', 'due', 'required_files']);
    }

    public function updateRequirement($requirementId, $data)
    {
        $requirement = ModelsRequirement::findOrFail($requirementId);

        // Validate the data
        $validated = validator($data, [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'due' => 'required|date|after_or_equal:today',
            'target' => 'required|in:college,department',
            'target_id' => 'required|integer',
            'required_files' => 'nullable|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav',
        ])->validate();

        // Update requirement fields
        $requirement->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'due' => $validated['due'],
            'target' => $validated['target'],
            'target_id' => $validated['target_id'],
        ]);

        // If a new file is uploaded, replace the media
        if (!empty($validated['required_files'])) {
            $requirement->clearMediaCollection('requirements');
            $requirement->addMedia($validated['required_files'])
                ->toMediaCollection('requirements');
        }

        session()->flash('success', 'Requirement updated successfully.');
    }

    public function deleteRequirement($requirementId)
    {
        $requirement = ModelsRequirement::findOrFail($requirementId);

        // Delete associated media
        $requirement->clearMediaCollection('requirements');

        // Delete the requirement
        $requirement->delete();

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
