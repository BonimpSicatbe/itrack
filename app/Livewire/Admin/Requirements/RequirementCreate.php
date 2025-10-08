<?php

namespace App\Livewire\Admin\Requirements;

use App\Models\Program;
use App\Models\Requirement;
use App\Models\RequirementType;
use App\Models\Semester;
use App\Models\User;
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

    // --- ASSIGNMENT PROPERTIES ---
    public $selectedPrograms = [];
    public $selectAllPrograms = false;

    // --- FILE PROPERTIES ---
    public $required_files = [];
    
    // Map for quick lookup of a folder's children (ID => [child_ids])
    protected array $folderChildrenMap = [];

    // Track previous selection for requirement types
    public $previousRequirementTypes = [];

    // Track existing requirement names to disable already created types
    public $existingRequirementNames = [];

    public function mount()
    {
        // Populate the map on mount for quick lookup
        $this->folderChildrenMap = $this->getFolderChildrenMap();
        $this->previousRequirementTypes = $this->selectedRequirementTypes;
        
        // Get all existing requirement names for the active semester
        $this->loadExistingRequirementNames();
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

    protected function loadExistingRequirementNames()
    {
        $activeSemester = $this->activeSemester;
        if ($activeSemester) {
            $this->existingRequirementNames = Requirement::where('semester_id', $activeSemester->id)
                ->pluck('name')
                ->toArray();
        }
    }

    // Check if a requirement type is already created
    public function isRequirementCreated($requirementType)
    {
        if ($requirementType->is_folder && $requirementType->children->isNotEmpty()) {
            // For folders, check if any child requirement name exists
            foreach ($requirementType->children as $child) {
                $childWithRelations = RequirementType::with('children')->find($child->id);
                
                if ($childWithRelations->children->isNotEmpty()) {
                    // Grandchild level
                    foreach ($childWithRelations->children as $grandchild) {
                        $fullName = $grandchild->name . ' ' . $child->name . ' ' . $requirementType->name;
                        if (in_array($fullName, $this->existingRequirementNames)) {
                            return true;
                        }
                    }
                } else {
                    // Child level
                    $fullName = $child->name . ' ' . $requirementType->name;
                    if (in_array($fullName, $this->existingRequirementNames)) {
                        return true;
                    }
                }
            }
            return false;
        } else {
            // For individual requirements
            if ($requirementType->parent_id) {
                $parent = RequirementType::with('parent')->find($requirementType->parent_id);
                $grandparent = $parent->parent ?? null;
                
                if ($grandparent) {
                    $fullName = $requirementType->name . ' ' . $parent->name . ' ' . $grandparent->name;
                } else {
                    $fullName = $requirementType->name . ' ' . $parent->name;
                }
                
                return in_array($fullName, $this->existingRequirementNames);
            } else {
                return in_array($requirementType->name, $this->existingRequirementNames);
            }
        }
    }

    // Check if a specific child requirement is already created
    public function isChildRequirementCreated($child, $parent)
    {
        $childWithRelations = RequirementType::with('children')->find($child->id);
        
        if ($childWithRelations->children->isNotEmpty()) {
            // This child has grandchildren
            foreach ($childWithRelations->children as $grandchild) {
                $fullName = $grandchild->name . ' ' . $child->name . ' ' . $parent->name;
                if (in_array($fullName, $this->existingRequirementNames)) {
                    return true;
                }
            }
            return false;
        } else {
            // This child is a leaf node
            $fullName = $child->name . ' ' . $parent->name;
            return in_array($fullName, $this->existingRequirementNames);
        }
    }

    // Check if a specific grandchild requirement is already created
    public function isGrandchildRequirementCreated($grandchild, $child, $parent)
    {
        $fullName = $grandchild->name . ' ' . $child->name . ' ' . $parent->name;
        return in_array($fullName, $this->existingRequirementNames);
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
    public function programs()
    {
        return Program::all();
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
            
            'selectedPrograms' => ['required', 'array', 'min:1'],
            'selectedPrograms.*' => ['exists:programs,id'],
            
            'required_files' => ['nullable', 'array', 'max:5'],
            'required_files.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav'],
        ];
    }

    public function validationAttributes()
    {
        return [
            'selectedPrograms' => 'programs',
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
    }

    
    public function updatedIsOtherSelected($value)
    {
        if (!$value) {
            // Only clear the "Other" input when the checkbox is unchecked
            $this->otherRequirementName = '';
        }
    }

    // --- ASSIGNMENT HOOKS ---
    public function updatedSelectAllPrograms($value)
    {
        if ($value) {
            $this->selectedPrograms = $this->programs->pluck('id')->toArray();
        } else {
            $this->selectedPrograms = [];
        }
    }

    // --- HELPER METHOD TO GET ASSIGNED USERS ---
    protected function getAssignedUsers($assignedData)
    {
        $users = collect();
        
        // Get the active semester
        $activeSemester = $this->activeSemester;
        if (!$activeSemester) {
            return $users;
        }

        // If all programs are selected, get all users from courses in all programs for the active semester
        if ($assignedData['selectAllPrograms']) {
            $users = User::whereHas('courseAssignments', function ($query) use ($activeSemester) {
                    $query->where('semester_id', $activeSemester->id)
                        ->whereHas('course', function ($courseQuery) {
                            $courseQuery->whereNotNull('program_id');
                        });
                })
                ->get();
        } 
        // If specific programs are selected
        elseif (!empty($assignedData['programs'])) {
            $users = User::whereHas('courseAssignments', function ($query) use ($activeSemester, $assignedData) {
                    $query->where('semester_id', $activeSemester->id)
                        ->whereHas('course', function ($courseQuery) use ($assignedData) {
                            $courseQuery->whereIn('program_id', $assignedData['programs']);
                        });
                })
                ->get();
        }
        
        // Remove duplicates and filter out admin users
        return $users->unique('id')->filter(function ($user) {
            return !in_array($user->role ?? 'user', ['admin', 'super-admin']);
        });
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
                'programs' => $this->selectedPrograms,
                'selectAllPrograms' => $this->selectAllPrograms,
            ];

            // 2. Determine REQUIREMENTS TO CREATE (Name and Type ID)
            $requirementsToCreate = [];

            // Handle predefined requirement types
            if (!empty($this->selectedRequirementTypes)) {
                // Get all selected types with their relationships
                $allSelectedTypes = RequirementType::with(['parent', 'children'])
                    ->whereIn('id', $this->selectedRequirementTypes)
                    ->get();

                foreach ($allSelectedTypes as $type) {
                    // If this type HAS CHILDREN (is a parent folder), create requirements for ALL its children
                    if ($type->children->isNotEmpty()) {
                        foreach ($type->children as $child) {
                            // Check if the child itself has children (nested folders)
                            $childWithRelations = RequirementType::with('children')->find($child->id);
                            
                            if ($childWithRelations->children->isNotEmpty()) {
                                // If the child has children, create requirements for THOSE grandchildren
                                foreach ($childWithRelations->children as $grandchild) {
                                    // Format: "Grandchild Name + Child Name + Parent Name" 
                                    // (e.g., "Question 1 TOS Midterm")
                                    $fullName = $grandchild->name . ' ' . $child->name . ' ' . $type->name;
                                    
                                    $requirementsToCreate[] = [
                                        'name' => $fullName, 
                                        'type_ids' => [$grandchild->id], // Store the GRANDCHILD ID
                                    ];
                                }
                            } else {
                                // If the child has NO children, create requirement for the child
                                // Format: "Child Name + Parent Name" (e.g., "TOS Midterm")
                                $fullName = $child->name . ' ' . $type->name;
                                
                                $requirementsToCreate[] = [
                                    'name' => $fullName, 
                                    'type_ids' => [$child->id], // Store the CHILD ID
                                ];
                            }
                        }
                    } 
                    // If this type has NO CHILDREN but has a PARENT (is a leaf node)
                    elseif ($type->parent_id && $type->children->isEmpty()) {
                        $parent = RequirementType::with('parent')->find($type->parent_id);
                        $grandparent = $parent->parent;
                        
                        if ($grandparent) {
                            // Three levels: "Leaf + Parent + Grandparent" (e.g., "Question 1 TOS Midterm")
                            $fullName = $type->name . ' ' . $parent->name . ' ' . $grandparent->name;
                        } else {
                            // Two levels: "Leaf + Parent" (e.g., "TOS Midterm")
                            $fullName = $type->name . ' ' . $parent->name;
                        }
                        
                        $requirementsToCreate[] = [
                            'name' => $fullName,
                            'type_ids' => [$type->id],
                        ];
                    }
                    // If this type has NO CHILDREN and NO PARENT (standalone leaf node)
                    elseif (!$type->parent_id && $type->children->isEmpty()) {
                        $requirementsToCreate[] = [
                            'name' => $type->name,
                            'type_ids' => [$type->id],
                        ];
                    }
                }
            }

            // Handle "Other" requirement (can be combined with predefined types)
            if ($this->isOtherSelected && !empty($this->otherRequirementName)) {
                $requirementsToCreate[] = [
                    'name' => $this->otherRequirementName,
                    'type_ids' => [], // Empty array for custom requirements
                ];
            }

            // Final validation - ensure at least one requirement is being created
            if (empty($requirementsToCreate)) {
                throw new \Exception('Please select at least one requirement type or specify a custom requirement.');
            }

            // Remove duplicate requirements (in case same requirement was selected multiple ways)
            $requirementsToCreate = collect($requirementsToCreate)
                ->unique('name')
                ->values()
                ->toArray();

            // 3. Get assigned users for notifications
            $assignedUsers = $this->getAssignedUsers($assignedData);

            // 4. Loop and Create Requirements
            $createdCount = 0;
            $filesToUpload = $this->required_files; 
            $firstRequirement = null;

            foreach ($requirementsToCreate as $data) {
                $requirement = Requirement::create([
                    'name' => $data['name'], 
                    'due' => $this->due,
                    'assigned_to' => $assignedData, 
                    'requirement_type_ids' => $data['type_ids'], // This will be automatically cast to JSON
                    'created_by' => Auth::id(),
                    'semester_id' => $activeSemester->id,
                    'status' => 'pending'
                ]);

                // Send notifications to assigned users
                foreach ($assignedUsers as $user) {
                    $user->notify(new \App\Notifications\NewRequirementNotification($requirement));
                }

                if ($createdCount === 0) {
                    $firstRequirement = $requirement;
                }
                $createdCount++;
            }

            // 5. Handle file uploads (to the first created record)
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
                'content' => "Successfully created {$createdCount} requirement(s). Notifications sent to {$assignedUsers->count()} users.",
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