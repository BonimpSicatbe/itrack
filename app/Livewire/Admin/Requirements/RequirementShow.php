<?php

namespace App\Livewire\Admin\Requirements;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Program;
use App\Models\Course;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Support\Str;

class RequirementShow extends Component
{
    use WithFileUploads;

    public $requirement;
    public $assignedUsers;
    public $required_files = [];
    public $selectedViewFile = null;
    public $assignedPrograms = [];

    public function mount($requirement)
    {
        $this->requirement = $requirement;
        $this->assignedUsers = $this->getAssignedUsers();
        $this->parseAssignedData();
    }

    private function parseAssignedData()
    {
        // assigned_to is already an array due to the cast in Requirement model
        $assignedTo = $this->requirement->assigned_to ?? [];
        
        // Get assigned programs
        if (isset($assignedTo['programs']) && is_array($assignedTo['programs'])) {
            $this->assignedPrograms = Program::whereIn('id', $assignedTo['programs'])
                ->with('college')
                ->get();
        }
    }

    private function getAssignedUsers()
    {
        $assignedTo = $this->requirement->assigned_to ?? [];
        
        $userQuery = User::query()->with([
            'college',
            'courseAssignments.course.program.college'
        ])->where('is_active', true); // Only active users
        
        $hasConditions = false;
        
        // Specific programs assigned - get users through course assignments
        if (isset($assignedTo['programs']) && is_array($assignedTo['programs'])) {
            // Get courses that belong to the assigned programs
            $courseIds = Course::whereIn('program_id', $assignedTo['programs'])
                ->pluck('id')
                ->toArray();
            
            if (!empty($courseIds)) {
                // Get users who are assigned to these courses in the current semester
                $userQuery->whereHas('courseAssignments', function ($query) use ($courseIds) {
                    $query->whereIn('course_id', $courseIds);
                    if ($this->requirement->semester_id) {
                        $query->where('semester_id', $this->requirement->semester_id);
                    }
                });
                $hasConditions = true;
            }
        }
        
        // Handle "select all" case - get all users with course assignments
        if (isset($assignedTo['selectAllPrograms']) && $assignedTo['selectAllPrograms']) {
            $userQuery->whereHas('courseAssignments', function ($query) {
                if ($this->requirement->semester_id) {
                    $query->where('semester_id', $this->requirement->semester_id);
                }
            });
            $hasConditions = true;
        }
        
        if (!$hasConditions) {
            return collect();
        }
        
        return $userQuery->get();
    }

    public function getAssignedToDisplayAttribute()
    {
        $parts = [];
        
        // Add programs
        if ($this->assignedPrograms->isNotEmpty()) {
            $programNames = $this->assignedPrograms->pluck('program_name')->toArray();
            if (count($programNames) > 2) {
                $parts[] = count($programNames) . ' programs';
            } else {
                $parts[] = implode(', ', $programNames);
            }
        }
        
        // Handle select all case - use the array directly
        $assignedTo = $this->requirement->assigned_to ?? [];
        if (isset($assignedTo['selectAllPrograms']) && $assignedTo['selectAllPrograms']) {
            $parts[] = 'All Programs';
        }
        
        return $parts ? implode('; ', $parts) : 'Not assigned';
    }

    // Helper method to get user's programs for display (filtered by semester if available)
    private function getUserPrograms($user)
    {
        if (!$user->relationLoaded('courseAssignments')) {
            return collect();
        }
        
        $courseAssignments = $user->courseAssignments;
        
        // Filter by semester if available
        if ($this->requirement->semester_id) {
            $courseAssignments = $courseAssignments->where('semester_id', $this->requirement->semester_id);
        }
        
        return $courseAssignments
            ->pluck('course.program')
            ->unique('id')
            ->filter();
    }

    // Helper method to get user's colleges for display (filtered by semester if available)
    private function getUserColleges($user)
    {
        $programs = $this->getUserPrograms($user);
        return $programs->pluck('college')->unique('id')->filter();
    }

    // Add the missing isPreviewable method
    public function isPreviewable($mimeType)
    {
        return Str::startsWith($mimeType, 'image/') ||
               Str::startsWith($mimeType, 'application/pdf') ||
               Str::startsWith($mimeType, 'text/');
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
                ->toMediaCollection('guides');
        }

        $this->reset('required_files');
        session()->flash('success', 'Required files uploaded successfully.');
    }

    public function downloadFile($fileId)
    {
        $file = $this->requirement->getMedia('guides')->find($fileId);
        if ($file) {
            return redirect()->route('guide.download', ['media' => $file->id]);
        }
        session()->flash('error', 'File not found.');
        return null;
    }

    public function render()
    {
        return view('livewire.admin.requirements.requirement-show', [
            'requirement' => $this->requirement,
            'assignedUsers' => $this->assignedUsers,
            'requiredFiles' => $this->requirement->getMedia('guides'),
            'programs' => Program::all(),
            'assignedPrograms' => $this->assignedPrograms,
            'assignedToDisplay' => $this->getAssignedToDisplayAttribute(),
        ]);
    }
}