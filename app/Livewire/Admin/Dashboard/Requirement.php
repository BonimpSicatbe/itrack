<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement as ModelsRequirement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;

class Requirement extends Component
{
    use WithFileUploads, WithPagination;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|string')]
    public $description = '';

    #[Validate('required|date|after_or_equal:today')]
    public $due = '';

    #[Validate('required|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav')]
    public $required_files = '';

    #[Validate('required|in:college,department')]
    public $target = ""; // college or department

    #[Validate('required|integer')]
    public $target_id = ""; // college_id or department_id

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    #[Computed()]
    public function targets()
    {
        return collect([
            'college' => 'College',
            'department' => 'Department',
        ]);
    }

    #[Computed()]
    public function target_ids()
    {
        return $this->target === 'college' ?
            College::all() :
            Department::all();
    }

    public function updatedTarget()
    {
        $this->target_id = null;
    }

    public function mount()
    {
        $this->target = $this->target; // Default target
        $this->target_id = $this->target_id; // Default target ID
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
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
        $this->reset(['name', 'description', 'due', 'required_files', 'target', 'target_id']);
        $this->dispatch('close-modal');
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
            'updated_by' => Auth::id(),
        ]);

        // If a new file is uploaded, replace the media
        if (!empty($validated['required_files'])) {
            $requirement->clearMediaCollection('requirements');
            $requirement->addMedia($validated['required_files'])
                ->toMediaCollection('requirements');
        }

        $this->dispatchBrowserEvent('toast-success', ['message' => 'Requirement created successfully.']);
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

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = ModelsRequirement::with('createdBy');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.admin.dashboard.requirement', [
            'target' => $this->target,
            'colleges' => College::all(),
            'departments' => Department::all(),
            'requirements' => $query->paginate(20),
        ]);
    }
}
