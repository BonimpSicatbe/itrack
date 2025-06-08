<?php

namespace App\Livewire\Admin;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Tasks extends Component
{
    use WithFileUploads;

    // Validation rules
    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required|string|max:1000')]
    public $description;

    #[Validate('required|in:college,department')]
    public $taskTarget;

    #[Validate('nullable|exists:colleges,id')]
    public $targetCollege;

    #[Validate('nullable|exists:departments,id')]
    public $targetDepartment;

    public function updated($property)
    {
        // Ensure at least one of targetCollege or targetDepartment is filled
        if (
            empty($this->targetCollege) &&
            empty($this->targetDepartment)
        ) {
            $this->addError('target', 'Either College or Department must be selected.');
        } else {
            $this->resetErrorBag('target');
        }
    }

    #[Validate('nullable|file|max:15240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,bmp,svg,mp4,avi,mov,wmv,mkv,webm')] // max: 10MB, allowed: pdf, word, ppt, excel, image, video
    public $uploadedFile;

    public function create()
    {
        $validated = $this->validate();
        $filePath = null;

        $task = Task::create(
            $this->only(['name', 'description']) + [
                'created_by' => Auth::id(),
            ]
        );

        $task->target()->create([
            'target_type' => $this->taskTarget,
            'target_id' => $this->taskTarget === 'college' ? $this->targetCollege : $this->targetDepartment,
        ]);

        // Handle file upload if any
        if ($this->uploadedFile) {
            $filePath = $this->uploadedFile->store('tasks/files', 'public');

            $task->files()->create([
                'filename' => $this->uploadedFile->getClientOriginalName(),
                'type' => $this->uploadedFile->getClientOriginalExtension(),
                'path' => $filePath,
                'status' => 'active',
                'size' => $this->uploadedFile->getSize(),
                'college_id' => $this->taskTarget === 'college' ? $this->targetCollege : null,
                'department_id' => $this->taskTarget === 'department' ? $this->targetDepartment : null,
                'uploaded_by' => Auth::id(),
            ]);
        }

        Log::info('Task created', [
            'task_id' => $task->id,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['name', 'description', 'taskTarget', 'targetCollege', 'targetDepartment', 'uploadedFile']);

        return redirect()->route('admin.dashboard')->with('success', 'Task created successfully!');
    }


    public function render()
    {
        return view('livewire.admin.tasks', [
            'tasks' => Task::latest()->get(),
        ]);
    }
}
