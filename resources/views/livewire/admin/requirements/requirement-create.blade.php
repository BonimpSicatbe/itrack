<div class="flex flex-col gap-6">
    <!-- Header - Original Design -->
    <div class="flex justify-between items-center text-white p-4 rounded-xl shadow-md" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="flex items-center gap-3">
            <div class="pl-3">
                <i class="fa-solid fa-file-circle-plus text-white text-2xl"></i>
            </div>
            <h2 class="text-xl md:text-xl font-semibold">Create Requirement</h2>
        </div>

        <button wire:click="cancel" class="btn bg-white/20 hover:bg-white/30 text-white text-sm flex items-center gap-2 border-0 rounded-full shadow-md px-4 py-1.5">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Back</span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar - Progress/Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Active Semester Card -->
            @if($this->activeSemester)
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <i class="fa-solid fa-calendar-check text-green-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-700">Active Semester</h3>
                    </div>
                    <div class="space-y-2">
                        <p class="text-gray-800 font-medium">{{ $this->activeSemester->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $this->activeSemester->start_date->format('M d, Y') }} - 
                            {{ $this->activeSemester->end_date->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-red-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                        </div>
                        <h3 class="font-semibold text-gray-700">No Active Semester</h3>
                    </div>
                    <p class="text-red-600 text-sm">Please set an active semester before creating requirements.</p>
                </div>
            @endif
            
            <!-- Selection Summary - Enhanced -->
            @if($this->activeSemester)
            <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-blue-500"></i>
                    Selection Summary
                </h3>
                <div class="space-y-4">
                    <!-- Requirement Types -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-600">Requirement Types</span>
                            <span class="text-sm font-medium text-gray-700">
                                @php
                                    $totalRequirements = 0;
                                    $folderSelections = [];
                                    $individualSelections = [];
                                    $customSelections = [];
                                    
                                    // Process folder selections and their children
                                    foreach($this->requirementTypes as $type) {
                                        if($type->is_folder && $type->children->isNotEmpty()) {
                                            $childIds = $type->children->pluck('id')->toArray();
                                            $selectedChildren = array_intersect($childIds, (array)$selectedRequirementTypes);
                                            
                                            if(count($selectedChildren) > 0) {
                                                // Always show folder if it has any selected children
                                                $folderSelections[] = [
                                                    'folder' => $type,
                                                    'selected_children' => $selectedChildren,
                                                    'all_selected' => count($selectedChildren) === count($childIds)
                                                ];
                                                $totalRequirements += count($selectedChildren);
                                            }
                                        } else {
                                            // Individual requirement types
                                            if(in_array($type->id, (array)$selectedRequirementTypes)) {
                                                $individualSelections[] = $type;
                                                $totalRequirements++;
                                            }
                                        }
                                    }
                                    
                                    // Add custom requirement - FIXED: Check if custom is selected AND has a name
                                    if($isOtherSelected) {
                                        if(!empty($otherRequirementName)) {
                                            $customSelections[] = $otherRequirementName;
                                            $totalRequirements++;
                                        } else {
                                            // Custom is selected but no name yet - show placeholder
                                            $customSelections[] = 'Unnamed Custom Requirement';
                                            $totalRequirements++;
                                        }
                                    }
                                @endphp
                                {{ $totalRequirements }} selected
                            </span>
                        </div>
                        <div class="space-y-2 max-h-32 overflow-y-auto custom-scrollbar pr-1">
                            <!-- Folder Selections -->
                            @foreach($folderSelections as $folderData)
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 text-xs bg-yellow-50 text-yellow-700 px-2 py-1 rounded border border-yellow-200">
                                        <i class="fa-solid fa-folder text-yellow-500 text-xs"></i>
                                        <span class="font-medium">{{ $folderData['folder']->name }}</span>
                                        <span class="text-yellow-600 text-xs ml-auto">
                                            @if($folderData['all_selected'])
                                                (All)
                                            @else
                                                ({{ count($folderData['selected_children']) }}/{{ count($folderData['folder']->children) }})
                                            @endif
                                        </span>
                                    </div>
                                    @foreach($folderData['folder']->children as $child)
                                        @if(in_array($child->id, $folderData['selected_children']))
                                            <div class="flex items-center gap-2 text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded ml-3 border border-blue-200">
                                                <i class="fa-solid fa-check text-green-500 text-xs"></i>
                                                <span class="truncate">{{ $child->name }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                            
                            <!-- Individual Selections -->
                            @foreach($individualSelections as $requirement)
                                <div class="flex items-center gap-2 text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-200">
                                    <i class="fa-solid fa-check text-green-500 text-xs"></i>
                                    <span class="truncate">{{ $requirement->name }}</span>
                                </div>
                            @endforeach
                            
                            <!-- Custom Requirements - FIXED: Always show when isOtherSelected is true -->
                            @if($isOtherSelected)
                                @foreach($customSelections as $customReq)
                                    <div class="flex items-center gap-2 text-xs bg-purple-50 text-purple-700 px-2 py-1 rounded border border-purple-200">
                                        <i class="fa-solid fa-pen-to-square text-purple-500 text-xs"></i>
                                        <span class="truncate">{{ $customReq }}</span>
                                        <span class="badge bg-purple-100 text-purple-800 border-0 text-xs font-medium px-1 py-0 rounded ml-auto">Custom</span>
                                    </div>
                                @endforeach
                            @endif
                            
                            @if($totalRequirements === 0)
                                <p class="text-xs text-gray-400 italic">No requirements selected</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Colleges -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-600">Colleges</span>
                            <span class="text-sm font-medium text-gray-700">
                                @if($selectAllColleges)
                                    All ({{ count($this->colleges) }})
                                @else
                                    {{ count($selectedColleges) }} selected
                                @endif
                            </span>
                        </div>
                        <div class="space-y-1 max-h-24 overflow-y-auto custom-scrollbar pr-1">
                            @if($selectAllColleges)
                                <div class="flex items-center gap-2 text-xs bg-green-50 text-green-700 px-2 py-1 rounded border border-green-200">
                                    <i class="fa-solid fa-check-double text-green-500 text-xs"></i>
                                    <span>All Colleges</span>
                                </div>
                            @elseif(count($selectedColleges) > 0)
                                @foreach($this->colleges->whereIn('id', $selectedColleges) as $college)
                                    <div class="flex items-center gap-2 text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-200">
                                        <i class="fa-solid fa-check text-green-500 text-xs"></i>
                                        <span class="truncate">{{ $college->acronym }}</span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-xs text-gray-400 italic">No colleges selected</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Departments -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-600">Departments</span>
                            <span class="text-sm font-medium text-gray-700">
                                @if($selectAllColleges || $selectAllDepartments)
                                    All ({{ count($this->departments) }})
                                @else
                                    {{ count($selectedDepartments) }} selected
                                @endif
                            </span>
                        </div>
                        <div class="space-y-1 max-h-24 overflow-y-auto custom-scrollbar pr-1">
                            @if($selectAllColleges || $selectAllDepartments)
                                <div class="flex items-center gap-2 text-xs bg-green-50 text-green-700 px-2 py-1 rounded border border-green-200">
                                    <i class="fa-solid fa-check-double text-green-500 text-xs"></i>
                                    <span>All Departments</span>
                                </div>
                            @elseif(count($selectedDepartments) > 0)
                                @foreach($this->departments->whereIn('id', $selectedDepartments)->take(8) as $department)
                                    <div class="flex items-center gap-2 text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-200">
                                        <i class="fa-solid fa-check text-green-500 text-xs"></i>
                                        <span class="truncate">{{ $department->college->acronym }}-{{ \Illuminate\Support\Str::limit($department->name, 12) }}</span>
                                    </div>
                                @endforeach
                                @if(count($selectedDepartments) > 8)
                                    <div class="text-xs text-gray-500 text-center">
                                        +{{ count($selectedDepartments) - 8 }} more
                                    </div>
                                @endif
                            @else
                                <p class="text-xs text-gray-400 italic">
                                    @if(!empty($selectedColleges))
                                        All departments from selected colleges
                                    @else
                                        No departments selected
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3 pb-5">
            @if($this->activeSemester)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-800">Requirement Details</h2>
                    <p class="text-sm text-gray-500 mt-1">Fill in the details for the new requirement</p>
                </div>
                
                <form wire:submit.prevent="createRequirement" class="p-6 space-y-8" enctype="multipart/form-data">
                    <!-- Requirement Types Section -->
                    <div class="space-y-4">
                        
                        <div class="form-control">
                            <label class="label justify-start gap-2 pb-2">
                                <span class="label-text font-semibold text-gray-700">Select requirement types</span>
                                @error('selectedRequirementTypes')
                                    <span class="label-text-alt text-red-600 ml-auto">{{ $message }}</span>
                                @enderror
                                @error('otherRequirementName')
                                    <span class="label-text-alt text-red-600 ml-auto">{{ $message }}</span>
                                @enderror
                            </label>
                            
                            <!-- Predefined Requirements -->
                            <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                                <div class="accordion">
                                    <input type="checkbox" id="predefined-accordion" class="accordion-toggle hidden" checked />
                                    <label for="predefined-accordion" class="accordion-header flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 cursor-pointer border-b border-gray-200 transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <i class="fa-solid fa-folder-open text-blue-500"></i>
                                            <h3 class="font-medium text-gray-700">Predefined Requirements</h3>
                                        </div>
                                        <i class="fa-solid fa-chevron-down accordion-arrow transition-transform duration-300 text-gray-500"></i>
                                    </label>
                                    <div class="accordion-content overflow-hidden transition-all duration-300 max-h-[500px]">
                                        <div class="p-4">
                                            <div class="space-y-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                                                @foreach($this->requirementTypes as $type)
                                                    @php
                                                        $isChecked = in_array($type->id, (array)$selectedRequirementTypes);
                                                        $allChildrenSelected = false;
                                                        $selectedChildrenCount = 0;
                                                        if ($type->is_folder && $type->children->isNotEmpty()) {
                                                            $childIds = $type->children->pluck('id')->toArray();
                                                            $selectedChildren = array_intersect($childIds, (array)$selectedRequirementTypes);
                                                            $selectedChildrenCount = count($selectedChildren);
                                                            $allChildrenSelected = $selectedChildrenCount === count($childIds);
                                                        }
                                                    @endphp

                                                    @if($type->is_folder && $type->children->isNotEmpty())
                                                        <div class="border border-gray-200 rounded-xl p-4 transition-all duration-200 {{ $allChildrenSelected ? 'bg-yellow-50 border-yellow-300 shadow-sm' : ($selectedChildrenCount > 0 ? 'bg-blue-50 border-blue-300 shadow-sm' : 'bg-white hover:bg-gray-50') }}">
                                                            <div class="flex items-center justify-between mb-3">
                                                                <div class="flex items-center space-x-3">
                                                                    <i class="fa-solid fa-folder text-yellow-500 text-lg"></i>
                                                                    <h4 class="font-bold text-gray-800">{{ $type->name }}</h4>
                                                                </div>
                                                                @if($allChildrenSelected)
                                                                    <span class="badge bg-yellow-100 text-yellow-800 border-0 text-xs font-medium px-2 py-1 rounded-full">All Selected</span>
                                                                @elseif($selectedChildrenCount > 0)
                                                                    <span class="badge bg-blue-100 text-blue-800 border-0 text-xs font-medium px-2 py-1 rounded-full">{{ $selectedChildrenCount }}/{{ count($type->children) }}</span>
                                                                @endif
                                                            </div>
                                                            
                                                            <div class="space-y-2 pl-2">
                                                                @foreach($type->children as $child)
                                                                    @php
                                                                        $isChildChecked = in_array($child->id, (array)$selectedRequirementTypes);
                                                                    @endphp
                                                                    <label class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 {{ $isChildChecked ? 'bg-green-50 border border-green-200' : 'bg-white border border-transparent hover:border-gray-200' }}">
                                                                        <input 
                                                                            type="checkbox" 
                                                                            wire:model.live="selectedRequirementTypes"
                                                                            value="{{ $child->id }}"
                                                                            class="checkbox checkbox-sm checkbox-primary rounded" 
                                                                        />
                                                                        <span class="text-sm font-medium text-gray-700">{{ $child->name }}</span>
                                                                        @if($isChildChecked)
                                                                            <i class="fa-solid fa-check text-green-500 text-xs ml-auto"></i>
                                                                        @endif
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <label class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 {{ $isChecked ? 'bg-green-50 border border-green-300' : 'bg-white border border-transparent hover:border-gray-200' }}">
                                                            <input 
                                                                type="checkbox" 
                                                                wire:model.live="selectedRequirementTypes"
                                                                value="{{ $type->id }}"
                                                                class="checkbox checkbox-primary rounded" 
                                                            />
                                                            <span class="font-medium text-gray-700">{{ $type->name }}</span>
                                                            @if($isChecked)
                                                                <i class="fa-solid fa-check text-green-500 ml-auto"></i>
                                                            @endif
                                                        </label>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Other Requirements -->
                            <div class="border border-gray-200 rounded-xl bg-white mt-4 overflow-hidden">
                                <div class="p-4">
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <div class="flex items-center space-x-3">
                                            <input 
                                                type="checkbox" 
                                                wire:model.live="isOtherSelected"
                                                class="checkbox checkbox-primary rounded" 
                                            />
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-pen-to-square text-purple-500"></i>
                                                <h3 class="font-medium text-gray-700">Custom Requirement</h3>
                                            </div>
                                        </div>
                                        @if($isOtherSelected)
                                            <span class="badge bg-purple-100 text-purple-800 border-0 text-xs font-medium px-2 py-1 rounded-full">Active</span>
                                        @endif
                                    </label>
                                    
                                    @if($isOtherSelected)
                                        <div class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-xl transition-all duration-300">
                                            <x-text-fieldset 
                                                type="text" 
                                                name="otherRequirementName" 
                                                wire:model="otherRequirementName" 
                                                label="Custom Requirement Name" 
                                                placeholder="e.g., Course Audit Report, Faculty Development Plan, etc."
                                                required
                                            />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Due Date & Files -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-text-fieldset 
                            type="datetime-local" 
                            name="due" 
                            wire:model="due" 
                            label="Due Date"
                            :min="now()->format('Y-m-d\TH:i')"
                            required
                        />

                        <div class="form-control">
                            <x-file-fieldset 
                                name="required_files" 
                                wire:model="required_files" 
                                label="Required Files" 
                                multiple 
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.txt,.zip,.rar,.7z,.mp4,.avi,.mkv,.mp3,.wav"
                            />
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fa-solid fa-circle-info text-blue-500 mr-1"></i>
                                Max file size: 15MB per file, Max files: 5
                            </p>
                        </div>
                    </div>
                    
                    <!-- Colleges & Departments - SIDE BY SIDE LAYOUT -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-5">
                        
                        <!-- Colleges Selection -->
                        <div class="form-control">
                            <label class="label justify-start gap-2 pb-2">
                                <span class="label-text font-semibold text-gray-700">Colleges</span>
                                @error('selectedColleges')
                                    <span class="label-text-alt text-red-600 ml-auto">{{ $message }}</span>
                                @enderror
                            </label>
                            <div class="border border-gray-200 rounded-xl p-4 bg-white h-full">
                                <label class="flex items-center space-x-3 mb-4 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors duration-200">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selectAllColleges"
                                        class="checkbox checkbox-primary rounded" 
                                    />
                                    <span class="font-medium text-gray-700">Select All Colleges</span>
                                    @if($selectAllColleges)
                                        <span class="badge bg-green-100 text-green-800 border-0 text-xs font-medium px-2 py-1 rounded-full ml-auto">All selected</span>
                                    @endif
                                </label>
                                <div class="grid grid-cols-1 gap-3 max-h-60 overflow-y-auto p-2 custom-scrollbar">
                                    @foreach($this->colleges as $college)
                                        <label class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 {{ in_array($college->id, $selectedColleges) ? 'bg-blue-50 border border-blue-200' : 'bg-white border border-transparent hover:border-gray-200' }}">
                                            <input 
                                                type="checkbox" 
                                                wire:model.live="selectedColleges"
                                                value="{{ $college->id }}"
                                                class="checkbox checkbox-sm checkbox-primary rounded" 
                                            />
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-700">{{ $college->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $college->acronym }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <!-- Departments Selection -->
                        <div class="form-control">
                            <label class="label pb-2">
                                <span class="label-text font-semibold text-gray-700">Departments</span>
                                @if($selectAllColleges || !empty($selectedColleges))
                                    <span class="label-text-alt text-green-600">Select departments (optional)</span>
                                @endif
                            </label>
                            <div class="border border-gray-200 rounded-xl p-4 bg-white h-full {{ empty($selectedColleges) && !$selectAllColleges ? 'opacity-60 bg-gray-50' : '' }}">
                                <label class="flex items-center space-x-3 mb-4 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors duration-200 {{ (empty($selectedColleges) && !$selectAllColleges) ? 'opacity-60 cursor-not-allowed' : '' }}"
                                    @if(empty($selectedColleges) && !$selectAllColleges) title="Please select colleges first" @endif>
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selectAllDepartments"
                                        class="checkbox checkbox-primary rounded" 
                                        @if(empty($selectedColleges) && !$selectAllColleges) disabled @endif
                                    />
                                    <span class="font-medium text-gray-700 {{ (empty($selectedColleges) && !$selectAllColleges) ? 'text-gray-400' : '' }}">
                                        Select All Departments
                                        @if($selectAllColleges)
                                            <span class="text-xs text-gray-500 ml-2">(auto-selected with all colleges)</span>
                                        @endif
                                    </span>
                                    @if($selectAllDepartments && !$selectAllColleges)
                                        <span class="badge bg-green-100 text-green-800 border-0 text-xs font-medium px-2 py-1 rounded-full ml-auto">All selected</span>
                                    @endif
                                </label>
                                
                                @if($selectAllColleges)
                                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                                        <div class="flex items-center">
                                            <i class="fa-solid fa-circle-info text-blue-500 mr-3 text-lg"></i>
                                            <span class="text-sm text-blue-700">All departments are automatically included because all colleges are selected.</span>
                                        </div>
                                    </div>
                                @elseif(!empty($selectedColleges))
                                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                                        <div class="flex items-center">
                                            <i class="fa-solid fa-filter text-green-500 mr-3 text-lg"></i>
                                            <span class="text-sm text-green-700">Showing departments from selected colleges. You can select specific departments.</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-4">
                                        <div class="flex items-center">
                                            <i class="fa-solid fa-circle-info text-gray-500 mr-3 text-lg"></i>
                                            <span class="text-sm text-gray-700">Please select colleges first to enable department selection.</span>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="grid grid-cols-1 gap-3 max-h-60 overflow-y-auto p-2 custom-scrollbar">
                                    @if($selectAllColleges || !empty($selectedColleges))
                                        @foreach($this->departments as $department)
                                            <label class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 {{ $selectAllColleges ? 'bg-gray-50 opacity-70' : (in_array($department->id, $selectedDepartments) ? 'bg-green-50 border border-green-200' : 'bg-white border border-transparent hover:border-gray-200') }}"
                                                @if($selectAllColleges) title="Included via college selection" @endif>
                                                <input 
                                                    type="checkbox" 
                                                    wire:model.live="selectedDepartments"
                                                    value="{{ $department->id }}"
                                                    class="checkbox checkbox-sm checkbox-primary rounded" 
                                                    @if($selectAllColleges) disabled checked @endif
                                                />
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-medium text-gray-700 {{ $selectAllColleges ? 'text-gray-500' : '' }}">{{ $department->name }}</span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $department->college->acronym }}
                                                        @if($selectAllColleges)
                                                            <span class="text-green-500 ml-1">(included)</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </label>
                                        @endforeach
                                    @else
                                        <div class="col-span-full text-center py-8 text-gray-400">
                                            <i class="fa-solid fa-building text-4xl mb-3"></i>
                                            <p>Select colleges first to view departments</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-between gap-4 pt-6 border-t border-gray-200 mt-6">
                        <button type="button" wire:click="cancel" class="btn btn-outline rounded-xl px-6 py-3 border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 order-2 sm:order-1">
                            <i class="fa-solid fa-xmark mr-2"></i> Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="btn bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl px-8 py-3 shadow-md hover:shadow-lg transition-all duration-200 order-1 sm:order-2"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="createRequirement">
                                <i class="fa-solid fa-plus mr-2"></i> Create Requirement
                            </span>
                            <span wire:loading wire:target="createRequirement">
                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>

    <style>
.accordion-toggle:checked + .accordion-header .accordion-arrow {
    transform: rotate(180deg);
}
.accordion-toggle:checked ~ .accordion-content {
    max-height: 0;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
    </style>
</div>