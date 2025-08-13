<?php

namespace App\Livewire\Admin;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement as ModelsRequirement;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

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

    public $required_files = [];

    #[Computed]
    public function activeSemester()
    {
        return Semester::where('is_active', true)->first();
    }

    #[Computed]
    public function sectorOptions()
    {
        if (!$this->sector) {
            return collect();
        }

        return $this->sector === 'college' 
            ? College::all() 
            : Department::all();
    }

    public function rules()
    {
        return [
            'required_files' => ['nullable', 'array'],
            'required_files.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav'],
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedSector()
    {
        $this->reset('assigned_to');
    }

    public function createRequirement()
    {
        try {
            $validated = $this->validate();

            $activeSemester = Semester::where('is_active', true)->first();

            if (!$activeSemester) {
                throw new \Exception('No active semester found. Please set an active semester first.');
            }

            $requirement = ModelsRequirement::create(array_merge($validated, [
                'created_by' => Auth::id(),
                'semester_id' => $activeSemester->id,
                'status' => 'pending'
            ]));

            // Handle file uploads to 'guides' collection
            if (!empty($this->required_files)) {
                Log::info('Starting file upload to guides collection', [
                    'file_count' => count($this->required_files),
                    'requirement_id' => $requirement->id
                ]);
                
                foreach ($this->required_files as $file) {
                    try {
                        $media = $requirement->addMedia($file->getRealPath())
                            ->usingName($file->getClientOriginalName())
                            ->usingFileName($file->getClientOriginalName())
                            ->toMediaCollection('guides'); // Changed to 'guides'
                            
                        Log::info('File uploaded to guides collection', [
                            'media_id' => $media->id,
                            'file_name' => $media->file_name
                        ]);
                    } catch (\Exception $e) {
                        Log::error('File upload to guides failed', [
                            'error' => $e->getMessage(),
                            'file' => $file->getClientOriginalName()
                        ]);
                        throw $e;
                    }
                }
            }

                $users = $requirement->assignedTargets();
                foreach ($users as $user) {
                    if (!in_array($user->role, ['admin', 'super-admin'])) {
                        $user->notify(new \App\Notifications\NewRequirementNotification($requirement));
                    }
                }

                $this->reset();
                $this->dispatch('showNotification', 
                    type: 'success', 
                    content: 'Requirement created successfully.',
                    duration: 3000
                );
                $this->dispatch('requirement-created');
                
                // Fixed auto-close with proper delay (1500ms = 1.5 seconds)
                $this->js(<<<'JS'
                    setTimeout(() => {
                        document.getElementById('createRequirement').checked = false;
                    }, 1500);
                JS);
                
            } catch (\Exception $e) {
            Log::error('Requirement creation failed', ['error' => $e->getMessage()]);
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'File upload failed: '.$e->getMessage(),
                duration: 5000
            );
        }
    }
    public function render()
    {
        return view('livewire.admin.requirement-create-modal');
    }
}