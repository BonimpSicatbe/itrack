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

    public $isCreating = false;
    public $showConfirmationModal = false;

    // --- CORE PROPERTIES ---
    #[Validate('required|date|after_or_equal:today')]
    public $due = '';

    // --- ASSIGNMENT PROPERTIES ---
    public $selectedCollege = '';
    public $selectedPrograms = [];
    public $selectAllPrograms = false;

    // --- SELECT ALL PROPERTIES ---
    public $selectAllRequirements = false;
    public $selectAllMidterm = false;
    public $selectAllFinals = false;

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
        // For folders, check if ALL children are already created
        if ($requirementType->is_folder && $requirementType->children->isNotEmpty()) {
            foreach ($requirementType->children as $child) {
                $childWithRelations = RequirementType::with('children')->find($child->id);
                
                if ($childWithRelations->children->isNotEmpty()) {
                    // Grandchild level - check if any grandchild is NOT created
                    foreach ($childWithRelations->children as $grandchild) {
                        $fullName = $grandchild->name . ' ' . $child->name . ' ' . $requirementType->name;
                        if (!in_array($fullName, $this->existingRequirementNames)) {
                            return false; // Found at least one not created
                        }
                    }
                } else {
                    // Child level - check if child is NOT created
                    $fullName = $child->name . ' ' . $requirementType->name;
                    if (!in_array($fullName, $this->existingRequirementNames)) {
                        return false; // Found at least one not created
                    }
                }
            }
            return true; // ALL children are created
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
    public function colleges()
    {
        return \App\Models\College::all();
    }

    #[Computed]
    public function programs()
    {
        if ($this->selectedCollege) {
            return Program::where('college_id', $this->selectedCollege)->get();
        }
        
        return Program::all();
    }

    // Get Midterm children IDs
    #[Computed]
    public function midtermChildrenIds()
    {
        $midterm = RequirementType::where('name', 'Midterm')->first();
        if ($midterm) {
            return $midterm->children->pluck('id')->toArray();
        }
        return [];
    }

    // Get Finals children IDs
    #[Computed]
    public function finalsChildrenIds()
    {
        $finals = RequirementType::where('name', 'Finals')->first();
        if ($finals) {
            return $finals->children->pluck('id')->toArray();
        }
        return [];
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
            
            'selectedPrograms' => ['nullable', 'array'],
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

    public function updatedSelectedCollege()
    {
        $this->selectAllPrograms = false;
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

        // Update select all states
        $this->updateSelectAllStates();
    }

    // Update select all states based on current selection
    public function updateSelectAllStates()
    {
        // Get all individual requirement IDs that are available (not created)
        $allAvailableIndividualIds = [];
        
        foreach ($this->requirementTypes as $type) {
            if ($type->is_folder && $type->children->isNotEmpty()) {
                foreach ($type->children as $child) {
                    if ($child->children->isNotEmpty()) {
                        foreach ($child->children as $grandchild) {
                            if (!$this->isGrandchildRequirementCreated($grandchild, $child, $type)) {
                                $allAvailableIndividualIds[] = $grandchild->id;
                            }
                        }
                    } else {
                        if (!$this->isChildRequirementCreated($child, $type)) {
                            $allAvailableIndividualIds[] = $child->id;
                        }
                    }
                }
            } else {
                if (!$this->isRequirementCreated($type)) {
                    $allAvailableIndividualIds[] = $type->id;
                }
            }
        }
        
        $allAvailableIndividualIds = array_unique($allAvailableIndividualIds);
        
        // Get currently selected IDs that are available
        $currentlySelectedAvailable = array_intersect($this->selectedRequirementTypes, $allAvailableIndividualIds);
        
        // Update Select All Requirements - true only if ALL available items are selected
        $this->selectAllRequirements = count($currentlySelectedAvailable) === count($allAvailableIndividualIds) && count($allAvailableIndividualIds) > 0;

        // Update Select All Midterm
        $midtermIds = $this->midtermChildrenIds;
        if (!empty($midtermIds)) {
            $availableMidtermIds = array_filter($midtermIds, function($id) {
                $type = RequirementType::find($id);
                return $type && !$this->isChildRequirementCreated($type, RequirementType::find($type->parent_id));
            });
            $this->selectAllMidterm = count(array_intersect($availableMidtermIds, $this->selectedRequirementTypes)) === count($availableMidtermIds) && count($availableMidtermIds) > 0;
        }

        // Update Select All Finals
        $finalsIds = $this->finalsChildrenIds;
        if (!empty($finalsIds)) {
            $availableFinalsIds = array_filter($finalsIds, function($id) {
                $type = RequirementType::find($id);
                return $type && !$this->isChildRequirementCreated($type, RequirementType::find($type->parent_id));
            });
            $this->selectAllFinals = count(array_intersect($availableFinalsIds, $this->selectedRequirementTypes)) === count($availableFinalsIds) && count($availableFinalsIds) > 0;
        }
    }

    public function toggleProgram($programId)
    {
        if (in_array($programId, $this->selectedPrograms)) {
            // Remove from selection
            $this->selectedPrograms = array_values(array_diff($this->selectedPrograms, [$programId]));
        } else {
            // Add to selection
            $this->selectedPrograms[] = $programId;
        }
        
        // Update select all programs state based on CURRENTLY VISIBLE programs
        $this->updateSelectAllProgramsState();
    }

    public function updateSelectAllProgramsState()
    {
        $currentProgramIds = $this->programs->pluck('id')->toArray();
        $this->selectAllPrograms = !empty($currentProgramIds) && 
            count(array_intersect($this->selectedPrograms, $currentProgramIds)) === count($currentProgramIds);
    }

    public function toggleRequirement($requirementId)
    {
        if (in_array($requirementId, $this->selectedRequirementTypes)) {
            // Remove from selection
            $this->selectedRequirementTypes = array_values(array_diff($this->selectedRequirementTypes, [$requirementId]));
        } else {
            // Add to selection
            $this->selectedRequirementTypes[] = $requirementId;
        }
        
        // Update the select all states
        $this->updateSelectAllStates();
    }

    // Select All Requirements - This selects ALL requirement types including standalone, midterms, and finals
    public function updatedSelectAllRequirements($value)
    {
        if ($value) {
            // Get all individual requirement IDs (including children and grandchildren) that are NOT created
            $allIndividualIds = [];
            
            foreach ($this->requirementTypes as $type) {
                if ($type->is_folder && $type->children->isNotEmpty()) {
                    // Add all children and grandchildren
                    foreach ($type->children as $child) {
                        if ($child->children->isNotEmpty()) {
                            // Add grandchildren that are not created
                            foreach ($child->children as $grandchild) {
                                if (!$this->isGrandchildRequirementCreated($grandchild, $child, $type)) {
                                    $allIndividualIds[] = $grandchild->id;
                                }
                            }
                        } else {
                            // Add child that is not created
                            if (!$this->isChildRequirementCreated($child, $type)) {
                                $allIndividualIds[] = $child->id;
                            }
                        }
                    }
                } else {
                    // Add standalone requirement that is not created
                    if (!$this->isRequirementCreated($type)) {
                        $allIndividualIds[] = $type->id;
                    }
                }
            }
            
            $this->selectedRequirementTypes = array_values(array_unique($allIndividualIds));
        } else {
            // Deselect all
            $this->selectedRequirementTypes = [];
        }
        
        // Update the select all states to reflect the current selection
        $this->updateSelectAllStates();
    }

    // Select All Midterm - This selects only Midterm children
    public function updatedSelectAllMidterm($value)
    {
        $midtermIds = $this->midtermChildrenIds;
        if (!empty($midtermIds)) {
            if ($value) {
                // Filter out already created requirements
                $availableMidtermIds = array_filter($midtermIds, function($id) {
                    $type = RequirementType::find($id);
                    return $type && !$this->isChildRequirementCreated($type, RequirementType::find($type->parent_id));
                });
                
                // Add available Midterm children to selection
                $this->selectedRequirementTypes = array_values(array_unique(array_merge($this->selectedRequirementTypes, $availableMidtermIds)));
            } else {
                // Remove Midterm children from selection
                $this->selectedRequirementTypes = array_values(array_diff($this->selectedRequirementTypes, $midtermIds));
            }
            $this->updateSelectAllStates();
        }
    }

    // Select All Finals - This selects only Finals children
    public function updatedSelectAllFinals($value)
    {
        $finalsIds = $this->finalsChildrenIds;
        if (!empty($finalsIds)) {
            if ($value) {
                // Filter out already created requirements
                $availableFinalsIds = array_filter($finalsIds, function($id) {
                    $type = RequirementType::find($id);
                    return $type && !$this->isChildRequirementCreated($type, RequirementType::find($type->parent_id));
                });
                
                // Add available Finals children to selection
                $this->selectedRequirementTypes = array_values(array_unique(array_merge($this->selectedRequirementTypes, $availableFinalsIds)));
            } else {
                // Remove Finals children from selection
                $this->selectedRequirementTypes = array_values(array_diff($this->selectedRequirementTypes, $finalsIds));
            }
            $this->updateSelectAllStates();
        }
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
        $currentProgramIds = $this->programs->pluck('id')->toArray();
        
        if ($value) {
            // Select only the currently visible/filtered programs
            $this->selectedPrograms = array_values(array_unique(array_merge($this->selectedPrograms, $currentProgramIds)));
        } else {
            // Deselect only the currently visible/filtered programs
            $this->selectedPrograms = array_values(array_diff($this->selectedPrograms, $currentProgramIds));
        }
        
        // Update the state
        $this->updateSelectAllProgramsState();
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
            $users = User::where('is_active', true)
                    ->whereHas('courseAssignments', function ($query) use ($activeSemester) {
                        $query->where('semester_id', $activeSemester->id)
                            ->whereHas('course', function ($courseQuery) {
                                $courseQuery->whereNotNull('program_id');
                            });
                    })
                    ->get();
        } 
        // If specific programs are selected
        elseif (!empty($assignedData['programs'])) {
            $users = User::where('is_active', true)
                    ->whereHas('courseAssignments', function ($query) use ($activeSemester, $assignedData) {
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

    // --- MODAL METHODS ---
    public function openConfirmationModal()
    {
        // Run validation before showing the modal
        $this->validate([
            'due' => ['required', 'date', 'after_or_equal:today'],
            'selectedPrograms' => ['required', 'array', 'min:1'],
        ], [
            'due.required' => 'Please set a due date and time.',
            'selectedPrograms.required' => 'Please select at least one program.',
            'selectedPrograms.min' => 'Please select at least one program.',
        ]);

        // Validate requirement selection
        if (empty($this->selectedRequirementTypes) && !$this->isOtherSelected) {
            session()->flash('notification', [
                'type' => 'error',
                'content' => 'Please select at least one requirement type or specify a custom requirement.',
                'duration' => 3000
            ]);
            return;
        }

        if ($this->isOtherSelected && empty($this->otherRequirementName)) {
            session()->flash('notification', [
                'type' => 'error',
                'content' => 'Please enter a custom requirement name.',
                'duration' => 3000
            ]);
            return;
        }

        // If we passed validation, show the modal
        $this->showConfirmationModal = true;
    }

    public function closeConfirmationModal()
    {
        $this->showConfirmationModal = false;
    }

    // --- CREATE METHOD ---
    public function createRequirement()
    {
        $this->validate();
        $this->isCreating = true;
        $this->showConfirmationModal = false;

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
                                        'requirement_group' => $this->getRequirementGroup($grandchild->id, $child->id, $type->id),
                                    ];
                                }
                            } else {
                                // If the child has NO children, create requirement for the child
                                // Format: "Child Name + Parent Name" (e.g., "TOS Midterm")
                                $fullName = $child->name . ' ' . $type->name;
                                
                                $requirementsToCreate[] = [
                                    'name' => $fullName, 
                                    'type_ids' => [$child->id], // Store the CHILD ID
                                    'requirement_group' => $this->getRequirementGroup($child->id, $type->id),
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
                            'requirement_group' => $this->getRequirementGroup($type->id, $parent->id, $grandparent?->id),
                        ];
                    }
                    // If this type has NO CHILDREN and NO PARENT (standalone leaf node)
                    elseif (!$type->parent_id && $type->children->isEmpty()) {
                        $requirementsToCreate[] = [
                            'name' => $type->name,
                            'type_ids' => [$type->id],
                            'requirement_group' => null,
                        ];
                    }
                }
            }

            // Handle "Other" requirement (can be combined with predefined types)
            if ($this->isOtherSelected && !empty($this->otherRequirementName)) {
                $requirementsToCreate[] = [
                    'name' => $this->otherRequirementName,
                    'type_ids' => [], // Empty array for custom requirements
                    'requirement_group' => null,
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

            // 4. Store uploaded files permanently first to prevent cleanup issues
            $storedFiles = [];
            if (!empty($this->required_files)) {
                foreach ($this->required_files as $file) {
                    // Generate a unique filename to avoid conflicts
                    $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('temp/requirements', $fileName, 'public');
                    
                    $storedFiles[] = [
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'name' => $file->getClientOriginalName(),
                        'full_path' => storage_path('app/public/' . $path)
                    ];
                }
            }

            // 5. Loop and Create Requirements
            $createdCount = 0;

            foreach ($requirementsToCreate as $data) {
                $requirement = Requirement::create([
                    'name' => $data['name'], 
                    'due' => $this->due,
                    'assigned_to' => $assignedData, 
                    'requirement_type_ids' => $data['type_ids'],
                    'requirement_group' => $data['requirement_group'],
                    'created_by' => Auth::id(),
                    'semester_id' => $activeSemester->id,
                    'status' => 'pending'
                ]);

                // Add stored files to each requirement - CREATE COPIES for each requirement
                if (!empty($storedFiles)) {
                    foreach ($storedFiles as $storedFile) {
                        // Verify file exists before trying to add it
                        if (file_exists($storedFile['full_path'])) {
                            // Create a temporary copy for this requirement
                            $tempCopyPath = storage_path('app/public/temp/copy_' . uniqid() . '_' . $storedFile['original_name']);
                            copy($storedFile['full_path'], $tempCopyPath);
                            
                            $requirement->addMedia($tempCopyPath)
                                ->usingName($storedFile['name'])
                                ->usingFileName($storedFile['original_name'])
                                ->toMediaCollection('guides');
                            
                            // Clean up the temporary copy after it's been processed
                            if (file_exists($tempCopyPath)) {
                                unlink($tempCopyPath);
                            }
                        } else {
                            Log::warning("File not found during requirement creation: " . $storedFile['full_path']);
                        }
                    }
                }

                // Send notifications to assigned users
                foreach ($assignedUsers as $user) {
                    $user->notify(new \App\Notifications\NewRequirementNotification($requirement));
                }

                $createdCount++;
            }

            // 6. Clean up original stored files after successful creation
            if (!empty($storedFiles)) {
                foreach ($storedFiles as $storedFile) {
                    if (file_exists($storedFile['full_path'])) {
                        unlink($storedFile['full_path']);
                    }
                }
            }

            session()->flash('notification', [
                'type' => 'success',
                'content' => "Successfully created {$createdCount} requirement(s). Notifications sent to {$assignedUsers->count()} users.",
                'duration' => 3000
            ]);

            // Reset form fields
            $this->selectedRequirementTypes = [];
            $this->otherRequirementName = '';
            $this->isOtherSelected = false;
            $this->due = '';
            $this->selectedPrograms = [];
            $this->selectAllPrograms = false;
            $this->selectAllRequirements = false;
            $this->selectAllMidterm = false;
            $this->selectAllFinals = false;
            $this->required_files = [];
            $this->previousRequirementTypes = [];

            $this->isCreating = false;

            return redirect()->route('admin.requirements.index');
            
        } catch (\Exception $e) {
            $this->isCreating = false;
            $this->showConfirmationModal = false;
            
            // Clean up any stored files if error occurred
            if (isset($storedFiles) && !empty($storedFiles)) {
                foreach ($storedFiles as $storedFile) {
                    if (file_exists($storedFile['full_path'])) {
                        unlink($storedFile['full_path']);
                    }
                }
            }
            
            Log::error('Requirement creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('notification', [
                'type' => 'error',
                'content' => 'Failed to create requirement: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    // Helper method to copy media from one requirement to another
    protected function copyMediaFromRequirement(Requirement $target, Requirement $source)
    {
        $mediaItems = $source->getMedia('guides');
        
        foreach ($mediaItems as $mediaItem) {
            // This creates a new media record pointing to the same file
            $target->addMedia($mediaItem->getPath())
                ->usingName($mediaItem->name)
                ->usingFileName($mediaItem->file_name)
                ->toMediaCollection('guides');
        }
    }

    // Helper method to determine requirement group based on type hierarchy
    protected function getRequirementGroup(...$typeIds)
    {
        // Auto-detect partnership groups based on requirement types
        foreach ($typeIds as $typeId) {
            $type = RequirementType::find($typeId);
            if ($type) {
                // Check if this is TOS or Examinations in Midterm
                if (($type->name === 'TOS' || $type->name === 'Examinations') && $this->isMidtermType($type)) {
                    return 'midterm_assessment';
                }
                // Check if this is TOS or Examinations in Finals
                if (($type->name === 'TOS' || $type->name === 'Examinations') && $this->isFinalsType($type)) {
                    return 'finals_assessment';
                }
            }
        }

        return null;
    }

    // Check if type belongs to Midterm hierarchy
    protected function isMidtermType($type)
    {
        $current = $type;
        while ($current) {
            if ($current->name === 'Midterm') {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    // Check if type belongs to Finals hierarchy
    protected function isFinalsType($type)
    {
        $current = $type;
        while ($current) {
            if ($current->name === 'Finals') {
                return true;
            }
            $current = $current->parent;
        }
        return false;
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