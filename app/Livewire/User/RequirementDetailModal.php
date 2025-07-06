<?php

namespace App\Livewire\user;

use Livewire\Component;
use App\Models\Requirement;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Models\SubmittedRequirement;

class RequirementDetailModal extends Component
{
    use WithFileUploads;

    public $requirement;
    public $file;
    public $uploading = false;
    public $submissionNotes = '';

    protected $listeners = ['showRequirementDetail' => 'loadRequirement'];

    public function loadRequirement($requirementId)
    {
        $this->requirement = Requirement::with([
            'guides',
            'userSubmissions' => function($query) {
                $query->with(['submissionFile', 'reviewer'])
                    ->latest();
            }
        ])->find($requirementId);
        
        if ($this->requirement) {
            $this->requirement->due = \Carbon\Carbon::parse($this->requirement->due);
        }
        
        $this->reset(['file', 'submissionNotes']);
    }

    public function closeModal()
    {
        $this->dispatch('closeModal');
        $this->reset(['requirement', 'file', 'submissionNotes']);
    }

    public function submitRequirement()
    {
        $this->validate([
            'file' => 'required|file|max:10240',
            'submissionNotes' => 'nullable|string|max:500',
        ]);

        $this->uploading = true;
        
        try {
            DB::transaction(function () {
                $submission = SubmittedRequirement::create([
                    'requirement_id' => $this->requirement->id,
                    'user_id' => auth()->id(),
                    'status' => SubmittedRequirement::STATUS_UNDER_REVIEW,
                    'admin_notes' => $this->submissionNotes,
                ]);

                $submission->addMedia($this->file->getRealPath())
                    ->usingName($this->file->getClientOriginalName())
                    ->usingFileName($this->file->getClientOriginalName())
                    ->toMediaCollection('submission_files');
            });

            $this->dispatch('notify', 
                type: 'success', 
                message: 'Requirement submitted successfully! Status: Under Review'
            );
            
            $this->reset(['file', 'submissionNotes']);
            $this->requirement->refresh();
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                message: 'Submission failed: '.$e->getMessage()
            );
        } finally {
            $this->uploading = false;
        }
    }

    public function render()
    {
        return view('livewire.user.requirement-detail-modal');
    }
}