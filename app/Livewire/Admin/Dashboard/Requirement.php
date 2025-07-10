<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement as ModelsRequirement;
use App\Models\RequirementMedia;
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

    #[Validate('required|string|max:255|unique:requirements,name')]
    public $name = '';

    #[Validate('required|string')]
    public $description = '';

    #[Validate('required|date|after_or_equal:today')]
    public $due = '';

    #[Validate('required|in:low,normal,high')]
    public $priority = '';

    #[Validate('required|in:college,department')]
    public $sector = ""; // college or department

    #[Validate('required|string|max:255')]
    public $assigned_to = ''; // name of the college or department

    #[Validate('required|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav')]
    public $required_files = null;

    #[Computed()]
    public function sectors()
    {
        return collect([
            'college' => 'College',
            'department' => 'Department',
        ]);
    }

    #[Computed()]
    public function sector_ids()
    {
        return $this->sector === 'college' ?
            College::all() :
            Department::all();
    }

    public function updatedSector()
    {
        $this->assigned_to = null;
    }

    public function mount()
    {
        $this->sector = $this->sector; // Default sector
        $this->assigned_to = $this->assigned_to; // Default sector ID
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // ========== ========== REQUIREMENT CRUD | START ========== ==========
    public function createRequirement()
    {
        try {
            $validated = $this->validate();

            Log::info('Validation passed', $validated);

            $requirement = ModelsRequirement::create(array_merge($validated, ['created_by' => Auth::id()]));

            Log::info('Requirement created', ['requirement_id' => $requirement->id]);

            $media = $requirement->addMedia($validated['required_files'])
                ->toMediaCollection('requirements');

            $requirement_media = RequirementMedia::create([
                'requirement_id' => $requirement->id,
                'media_id' => $media->id,
            ]);


            /**
             *
             * notifies the users assigned to the requirement
             * that a new requirement has been created
             *
             **/
            $assignedUsers = $requirement->assignedTargets(); // Make sure this method returns a collection of User models

            foreach ($assignedUsers as $user) {
                Log::info('Notifying user', ['user_id' => $user->id, 'requirement_id' => $requirement->id]);
                $user->notify(new \App\Notifications\RequirementNotification(Auth::user(), $requirement));
            }

            // Log the details of the media added to the requirement
            Log::info('Media added to requirement');
            Log::info('Requirement ID: ' . $requirement->id);
            Log::info('Media ID: ' . ($media->id ?? 'null'));
            Log::info('Media Record: ' . json_encode($requirement_media->toArray()));
            Log::info('File Name: ' . ($media->file_name ?? 'null'));
            Log::info('File Size (bytes): ' . ($media->size ?? 'null'));
            Log::info('Uploaded At: ' . ($media->created_at ?? 'null'));

            session()->flash('success', 'Requirement created successfully.');
            $this->reset(['name', 'description', 'due', 'priority', 'required_files', 'sector', 'assigned_to']);
            $this->dispatch('close-modal');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation: duplicate entry
                session()->flash('error', 'A requirement with this name already exists.');
            } else {
                session()->flash('error', 'An error occurred while creating the requirement.');
            }
            Log::error('Requirement creation failed', ['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            session()->flash('error', 'An unexpected error occurred.');
            Log::error('Requirement creation failed', ['error' => $e->getMessage()]);
        }
    }

    public function showRequirement($requirementId)
    {
        return redirect()->route('admin.requirements.show', ['requirement' => $requirementId]);
    }
    // ========== ========== REQUIREMENT CRUD | END ========== ==========

    // ========== ========== SEARCH AND SORT | START ========== ==========
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'asc';
    // protected $queryString = ['sortField', 'sortDirection'];

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : 'asc';

        $this->sortField = $field;
    }
    // ========== ========== SEARCH AND SORT | END ========== ==========

    public function render()
    {
        // sleep(1); // for testing purposes, simulating a delay

        return view('livewire.admin.dashboard.requirement', [
            'sector' => $this->sector,
            'colleges' => College::all(),
            'departments' => Department::all(),
            'requirements' => ModelsRequirement::search('name', $this->search)->orderBy($this->sortField, $this->sortDirection)->paginate(20),
        ]);
    }
}
