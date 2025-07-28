<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class RequirementIndex extends Component
{
    use WithFileUploads;

    public $assigned_to = '';
    public $name = '';
    public $description = '';
    public $due = '';
    public $priority = '';
    public $required_files = [];

    #[Validate('required|in:college,department')]
    public $sector = '';
    public $search = '';

    public function createRequirement()
    {
        try {
            Log::debug('Starting requirement creation', [
                'assigned_to' => $this->assigned_to,
                'description' => $this->description,
                'due' => $this->due,
                'priority' => $this->priority,
                'sector' => $this->sector,
                'user_id' => Auth::id(),
            ]);

            $validated = $this->validate([
                'assigned_to' => 'required|string|max:255',
                'name' => 'required|string|max:255|unique:requirements,name',
                'description' => 'required|string',
                'due' => 'required|date_format:Y-m-d\TH:i|after_or_equal:now',
                'priority' => 'required|in:low,normal,high',
                'required_files' => 'nullable|array',
                'required_files.*' => 'file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav',
            ]);
            Log::debug('Validation successful', $validated);

            $requirement = Requirement::create(array_merge($validated, ['created_by' => Auth::id()]));
            Log::debug('Requirement created', ['requirement_id' => $requirement->id]);

            if (!empty($this->required_files)) {
                foreach ($this->required_files as $file) {
                    $media = $requirement->addMedia($file->getRealPath())
                        ->usingFileName($file->getClientOriginalName())
                        ->preservingOriginal()
                        ->toMediaCollection('requirement/requirement_required_files', 'public');
                }
            }
            Log::debug('Media added to requirement', ['media_id' => $media->id ?? null]);

            $assignedUsers = $requirement->targetUsers()->whereNotIn('role', ['admin', 'super-admin']);

            $this->reset();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function deleteRequirement($requirementId)
    {
        try {
            $requirement = Requirement::findOrFail($requirementId);

            // Delete associated media files
            $requirement->clearMediaCollection('requirement/requirement_required_files');

            // Delete the requirement
            $requirement->delete();

            $this->reset();
            Log::info('Requirement deleted', ['requirement_id' => $requirementId]);
        } catch (\Exception $e) {
            Log::error('Requirement deletion failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function render()
    {
        $requirements = Requirement::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        $colleges = College::all();
        $departments = Department::all();

        return view('livewire.admin.requirements.requirement-index', [
            'requirements' => $requirements,
            'colleges' => $colleges,
            'departments' => $departments,
        ]);
    }
}
