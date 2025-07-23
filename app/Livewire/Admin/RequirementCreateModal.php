<?php

namespace App\Livewire\Admin;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement as ModelsRequirement;
use App\Models\RequirementMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class RequirementCreateModal extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255|unique:requirements,name')]
    public $name = '';

    #[Validate('required|string')]
    public $description = '';

    #[Validate('required|date|after_or_equal:today')]
    public $due = '';

    #[Validate('required|in:low,normal,high')]
    public $priority = 'normal';

    #[Validate('required|in:college,department')]
    public $sector = "";

    #[Validate('required|string|max:255')]
    public $assigned_to = '';

    #[Validate('required|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav')]
    public $required_files = null;

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    #[Computed]
    public function sector_ids()
    {
        if (!$this->sector) {
            return collect();
        }

        return $this->sector === 'college' 
            ? College::all() 
            : Department::all();
    }

    public function updatedSector()
    {
        $this->assigned_to = '';
    }

    public function createRequirement()
    {
        try {
            $validated = $this->validate();

            $requirement = ModelsRequirement::create(array_merge($validated, ['created_by' => Auth::id()]));

            $media = $requirement->addMedia($validated['required_files'])
                ->toMediaCollection('requirementRequiredFiles');

            RequirementMedia::create([
                'requirement_id' => $requirement->id,
                'media_id' => $media->id,
            ]);

            // Notify all users in the assigned sector
            $users = $requirement->assignedTargets(); // This now returns a collection directly

            foreach ($users as $user) {
                if (!in_array($user->role, ['admin', 'super-admin'])) {
                    $user->notify(new \App\Notifications\NewRequirementNotification($requirement));
                }
            }

            session()->flash('success', 'Requirement created successfully.');
            $this->reset();
            $this->dispatch('close-modal');
            $this->dispatch('requirementCreated');
            
        } catch (\Exception $e) {
            Log::error('Requirement creation failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function via($notifiable)
    {
        return ['database']; // Ensure this is present
    }

    public function render()
    {
        return view('livewire.admin.requirement-create-modal');
    }
}