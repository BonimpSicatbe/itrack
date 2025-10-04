<?php

namespace App\Livewire\Admin\Requirements;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\College;
use App\Models\Department;
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
    public $assignedColleges = [];
    public $assignedDepartments = [];

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
        
        // Get assigned colleges
        if (isset($assignedTo['colleges']) && is_array($assignedTo['colleges'])) {
            $this->assignedColleges = College::whereIn('id', $assignedTo['colleges'])->get();
        }
        
        // Get assigned departments
        if (isset($assignedTo['departments']) && is_array($assignedTo['departments'])) {
            $this->assignedDepartments = Department::whereIn('id', $assignedTo['departments'])
                ->with('college')
                ->get();
        }
    }

    private function getAssignedUsers()
    {
        // assigned_to is already an array due to the cast in Requirement model
        $assignedTo = $this->requirement->assigned_to ?? [];
        
        $userQuery = User::query()->with(['department', 'college']);
        
        $hasConditions = false;
        
        // Specific colleges AND departments combination
        if (isset($assignedTo['colleges']) && is_array($assignedTo['colleges']) && 
            isset($assignedTo['departments']) && is_array($assignedTo['departments'])) {
            
            $userQuery->where(function ($query) use ($assignedTo) {
                // Users in assigned colleges
                $query->whereIn('college_id', $assignedTo['colleges'])
                      // AND in assigned departments
                      ->whereIn('department_id', $assignedTo['departments']);
            });
            $hasConditions = true;
        }
        // Only colleges assigned
        elseif (isset($assignedTo['colleges']) && is_array($assignedTo['colleges'])) {
            $userQuery->whereIn('college_id', $assignedTo['colleges']);
            $hasConditions = true;
        }
        // Only departments assigned  
        elseif (isset($assignedTo['departments']) && is_array($assignedTo['departments'])) {
            $userQuery->whereIn('department_id', $assignedTo['departments']);
            $hasConditions = true;
        }
        
        // Handle "select all" cases
        if (isset($assignedTo['selectAllColleges']) && $assignedTo['selectAllColleges']) {
            $userQuery->orWhereNotNull('college_id');
            $hasConditions = true;
        }
        
        if (isset($assignedTo['selectAllDepartments']) && $assignedTo['selectAllDepartments']) {
            $userQuery->orWhereNotNull('department_id');
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
        
        // Add colleges
        if ($this->assignedColleges->isNotEmpty()) {
            $collegeNames = $this->assignedColleges->pluck('name')->toArray();
            if (count($collegeNames) > 2) {
                $parts[] = count($collegeNames) . ' colleges';
            } else {
                $parts[] = implode(', ', $collegeNames);
            }
        }
        
        // Add departments
        if ($this->assignedDepartments->isNotEmpty()) {
            $deptNames = $this->assignedDepartments->pluck('name')->toArray();
            if (count($deptNames) > 2) {
                $parts[] = count($deptNames) . ' departments';
            } else {
                $parts[] = implode(', ', $deptNames);
            }
        }
        
        // Handle select all cases - use the array directly
        $assignedTo = $this->requirement->assigned_to ?? [];
        if (isset($assignedTo['selectAllColleges']) && $assignedTo['selectAllColleges']) {
            $parts[] = 'All Colleges';
        }
        if (isset($assignedTo['selectAllDepartments']) && $assignedTo['selectAllDepartments']) {
            $parts[] = 'All Departments';
        }
        
        return $parts ? implode('; ', $parts) : 'Not assigned';
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
            'colleges' => College::all(),
            'departments' => Department::all(),
            'assignedColleges' => $this->assignedColleges,
            'assignedDepartments' => $this->assignedDepartments,
            'assignedToDisplay' => $this->getAssignedToDisplayAttribute(),
        ]);
    }
}