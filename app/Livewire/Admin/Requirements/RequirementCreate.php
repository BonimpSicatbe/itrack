<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\College;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\RequirementType;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class RequirementCreate extends Component
{
    use WithFileUploads;

    // --- REQUIREMENT SELECTION PROPERTIES ---
    public $selectedRequirementTypes = [];
    public $otherRequirementName = '';
    public $isOtherSelected = false;

    // --- CORE PROPERTIES ---
    #[Validate('required|date|after_or_equal:today')]
    public $due = '';

    #[Validate('required|in:low,normal,high')]
    public $priority = 'normal';

    // --- ASSIGNMENT PROPERTIES ---
    public $selectedColleges = [];
    public $selectedDepartments = [];
    public $selectAllColleges = false;
    public $selectAllDepartments = false;

    // --- FILE PROPERTIES ---
    public $required_files = [];
    
    // Map for quick lookup of a folder's children (ID => [child_ids])
    protected array $folderChildrenMap = [];

    // Track previous selection for requirement types
    public $previousRequirementTypes = [];

    public function mount()
    {
        // Populate the map on mount for quick lookup
        $this->folderChildrenMap = $this->getFolderChildrenMap();
        $this->previousRequirementTypes = $this->selectedRequirementTypes;
    }

    protected function getFolderChildrenMap(): array
    {
        $map = [];
        // Fetch only folders and their children
        $folders = RequirementType::with('children')->where('is_folder', true)->get();
        foreach ($folders as $folder) {
            $map[$folder->id] = $folder->children->pluck('id')->toArray();
        }
        return $map;
    }

    // --- COMPUTED PROPERTIES ---
    #[Computed]
    public function activeSemester()
    {
        return Semester::where('is_active', true)->first();
    }
    
    #[Computed]
    public function requirementTypes()
    {
        return RequirementType::with('children')
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function colleges()
    {
        return College::with('departments')->get();
    }

    #[Computed]
    public function departments()
    {
        if ($this->selectAllColleges) {
            return Department::with('college')->get();
        }
        if (!empty($this->selectedColleges)) {
            return Department::with('college')->whereIn('college_id', $this->selectedColleges)->get();
        }
        return collect();
    }

    // --- VALIDATION RULES ---
    public function rules()
    {
        return [
            'otherRequirementName' => [
                'required_if:isOtherSelected,true', 
                'nullable', 
                'string', 
                'max:255', 
                function ($attribute, $value, $fail) {
                    // Only validate uniqueness if "Other" is selected AND we have a value
                    if ($this->isOtherSelected && !empty($value)) {
                        $exists = \App\Models\Requirement::where('name', $value)->exists();
                        if ($exists) {
                            $fail('This requirement name already exists.');
                        }
                    }
                }
            ],
            'selectedRequirementTypes' => ['nullable', 'array'],
            'selectedRequirementTypes.*' => ['exists:requirement_types,id'],
            'isOtherSelected' => ['boolean'],

            'due' => ['required', 'date', 'after_or_equal:today'],
            'priority' => ['required', 'in:low,normal,high'],
            
            'selectedColleges' => ['required', 'array', 'min:1'],
            'selectedColleges.*' => ['exists:colleges,id'],
            'selectedDepartments' => ['sometimes', 'array'],
            'selectedDepartments.*' => ['exists:departments,id'],
            
            'required_files' => ['nullable', 'array', 'max:5'],
            'required_files.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav'],
        ];
    }

    public function validationAttributes()
    {
        return [
            'selectedColleges' => 'colleges',
            'selectedDepartments' => 'departments',
            'otherRequirementName' => 'requirement name',
            'selectedRequirementTypes' => 'requirement type selection',
        ];
    }
    
    // --- REQUIREMENT SELECTION HOOKS ---
    public function updatedSelectedRequirementTypes($value)
    {
        // Get current and previous selection
        $currentSelection = $this->selectedRequirementTypes;
        $previousSelection = $this->previousRequirementTypes;
        
        // Find what was just changed (added or removed)
        $added = array_diff($currentSelection, $previousSelection);
        $removed = array_diff($previousSelection, $currentSelection);
        
        $finalSelection = $currentSelection;
        
        // Handle folder selection/deselection
        foreach ($added as $addedId) {
            // If a folder was added, add all its children
            if (isset($this->folderChildrenMap[$addedId])) {
                $finalSelection = array_merge($finalSelection, $this->folderChildrenMap[$addedId]);
            }
        }
        
        foreach ($removed as $removedId) {
            // If a folder was removed, remove all its children
            if (isset($this->folderChildrenMap[$removedId])) {
                $finalSelection = array_diff($finalSelection, $this->folderChildrenMap[$removedId]);
            }
        }
        
        // Handle child selection/deselection affecting parent folder
        foreach ($this->folderChildrenMap as $folderId => $childIds) {
            $selectedChildren = array_intersect($childIds, $finalSelection);
            
            if (count($selectedChildren) === count($childIds)) {
                // All children are selected, so select the folder
                if (!in_array($folderId, $finalSelection)) {
                    $finalSelection[] = $folderId;
                }
            } else {
                // Not all children are selected, so deselect the folder
                $finalSelection = array_diff($finalSelection, [$folderId]);
            }
        }
        
        $this->selectedRequirementTypes = array_values(array_unique($finalSelection));
        
        // Update previous selection for next change
        $this->previousRequirementTypes = $this->selectedRequirementTypes;
        
        // REMOVED: Don't automatically clear "Other" when selecting predefined types
        // This allows both to be selected simultaneously
    }

    
    public function updatedIsOtherSelected($value)
    {
        if (!$value) {
            // Only clear the "Other" input when the checkbox is unchecked
            $this->otherRequirementName = '';
        }
    }

    // --- ASSIGNMENT HOOKS ---
    public function updatedSelectAllColleges($value)
    {
        if ($value) {
            $this->selectedColleges = $this->colleges->pluck('id')->toArray();
            $this->selectedDepartments = $this->departments->pluck('id')->toArray();
            $this->selectAllDepartments = true;
        } else {
            $this->selectedColleges = [];
            $this->selectedDepartments = [];
            $this->selectAllDepartments = false;
        }
    }

    public function updatedSelectedColleges()
    {
        $this->selectAllColleges = false;
        $this->selectAllDepartments = false;
        
        // Reset departments when colleges change
        if (empty($this->selectedColleges)) {
            $this->selectedDepartments = [];
        } else {
            // Keep only departments that belong to selected colleges
            $validDepartments = $this->departments->pluck('id')->toArray();
            $this->selectedDepartments = array_intersect($this->selectedDepartments, $validDepartments);
        }
    }

    public function updatedSelectAllDepartments($value)
    {
        if ($value && (!empty($this->selectedColleges) || $this->selectAllColleges)) {
            $this->selectedDepartments = $this->departments->pluck('id')->toArray();
        } else {
            $this->selectedDepartments = [];
        }
    }

    public function updatedSelectedDepartments()
    {
        $this->selectAllDepartments = false;
        
        if (!empty($this->selectedColleges) || $this->selectAllColleges) {
            $allDepartments = $this->departments->pluck('id')->toArray();
            $this->selectAllDepartments = !empty($allDepartments) && 
                                         count($this->selectedDepartments) === count($allDepartments);
        }
    }

    // --- CREATE METHOD ---
    public function createRequirement()
    {
        $this->validate();

        try {
            if (!$activeSemester = $this->activeSemester) {
                throw new \Exception('No active semester found. Please set an active semester first.');
            }

            // 1. Prepare ASSIGNMENT DATA 
            $assignedData = [
                'colleges' => $this->selectedColleges,
                'departments' => $this->selectedDepartments,
                'selectAllColleges' => $this->selectAllColleges,
                'selectAllDepartments' => $this->selectAllDepartments
            ];

            // 2. Determine REQUIREMENTS TO CREATE (Name and Type ID)
            $requirementsToCreate = [];

            // Handle predefined requirement types (can be combined with "Other")
            if (!empty($this->selectedRequirementTypes)) {
                // Filter out parent IDs, only keep children (parent_id is not null) or standalones (is_folder is false)
                $allSelectedTypes = RequirementType::whereIn('id', $this->selectedRequirementTypes)->get();
                $typeIdsToCreate = [];
                
                foreach ($allSelectedTypes as $type) {
                    // We only create requirements for actual items, not the folders
                    if (!$type->is_folder) {
                        $typeIdsToCreate[] = $type->id;
                    }
                }

                $typesForCreation = RequirementType::with('parent')
                    ->whereIn('id', $typeIdsToCreate)
                    ->get();
                
                foreach ($typesForCreation as $type) {
                    if ($type->parent) {
                        // Create name as "TOS Midterm"
                        $fullName = $type->parent->name . ' ' . $type->name; 
                    } else {
                        $fullName = $type->name;
                    }
                    
                    $requirementsToCreate[] = [
                        'name' => $fullName, 
                        'type_id' => $type->id,
                    ];
                }
            }

            // Handle "Other" requirement (can be combined with predefined types)
            if ($this->isOtherSelected && !empty($this->otherRequirementName)) {
                $requirementsToCreate[] = [
                    'name' => $this->otherRequirementName,
                    'type_id' => null,
                ];
            }

            // Final validation - ensure at least one requirement is being created
            if (empty($requirementsToCreate)) {
                throw new \Exception('Please select at least one requirement type or specify a custom requirement.');
            }

            // 3. Loop and Create Requirements
            $createdCount = 0;
            $filesToUpload = $this->required_files; 
            $firstRequirement = null;

            foreach ($requirementsToCreate as $data) {
                $requirement = Requirement::create([
                    'name' => $data['name'], 
                    'due' => $this->due,
                    'priority' => $this->priority,
                    'assigned_to' => json_encode($assignedData), 
                    'requirement_type_ids' => $data['type_id'] ? json_encode([$data['type_id']]) : null, 
                    'created_by' => Auth::id(),
                    'semester_id' => $activeSemester->id,
                    'status' => 'pending'
                ]);

                if ($createdCount === 0) {
                    $firstRequirement = $requirement;
                }
                $createdCount++;
            }

            // 4. Handle file uploads (to the first created record)
            if ($firstRequirement && !empty($filesToUpload)) {
                foreach ($filesToUpload as $file) {
                    $firstRequirement->addMedia($file->getRealPath())
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('guides');
                }
            }

            $this->reset();
            
            session()->flash('notification', [
                'type' => 'success',
                'content' => "Successfully created {$createdCount} requirement(s).",
                'duration' => 3000
            ]);

            return redirect()->route('admin.requirements.index');
            
        } catch (\Exception $e) {
            Log::error('Requirement creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('notification', [
                'type' => 'error',
                'content' => 'Failed to create requirement: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function cancel()
    {
        return redirect()->route('admin.requirements.index');
    }

    public function render()
    {
        return view('livewire.admin.requirements.requirement-create');
    }
}