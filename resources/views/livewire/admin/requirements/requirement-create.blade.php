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

    <div class="grid grid-cols-1 gap-6">
        <!-- Main Content - Full Width -->
        <div class="pb-5">
            @if($this->activeSemester)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-800">Requirement Details</h2>
                    <p class="text-sm text-gray-500 mt-1">Fill in the details for the new requirement</p>
                </div>
                
                <form wire:submit.prevent="createRequirement" class="p-6 space-y-8" enctype="multipart/form-data">
                    <!-- Requirement Types & Timeline Side by Side -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Left Column: Select Requirement Types -->
                        <div class="space-y-6">
                            <div class="form-control">
                                <label class="label justify-start gap-2 pb-2">
                                    <span class="label-text font-semibold text-gray-700 text-lg flex items-center gap-2">
                                        <i class="fa-solid fa-list-check text-blue-500"></i>
                                        Select Requirement Types
                                    </span>
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
                                        <div class="accordion-content overflow-hidden transition-all duration-300 max-h-[400px]">
                                            <div class="p-4">
                                                <div class="space-y-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                                                    @foreach($this->requirementTypes as $type)
                                                        @php
                                                            $isChecked = in_array($type->id, (array)$selectedRequirementTypes);
                                                            $allChildrenSelected = false;
                                                            $selectedChildrenCount = 0;
                                                            $isFolderCreated = $this->isRequirementCreated($type);
                                                            
                                                            if ($type->is_folder && $type->children->isNotEmpty()) {
                                                                $childIds = $type->children->pluck('id')->toArray();
                                                                $selectedChildren = array_intersect($childIds, (array)$selectedRequirementTypes);
                                                                $selectedChildrenCount = count($selectedChildren);
                                                                $allChildrenSelected = $selectedChildrenCount === count($childIds);
                                                            }
                                                        @endphp

                                                        @if($type->is_folder && $type->children->isNotEmpty())
                                                            <div class="border border-gray-200 rounded-xl p-4 transition-all duration-200 {{ $isFolderCreated ? 'bg-gray-100 border-gray-300 opacity-75' : ($allChildrenSelected ? 'bg-yellow-50 border-yellow-300 shadow-sm' : ($selectedChildrenCount > 0 ? 'bg-blue-50 border-blue-300 shadow-sm' : 'bg-white hover:bg-gray-50')) }}">
                                                                <div class="flex items-center justify-between mb-3">
                                                                    <div class="flex items-center space-x-3">
                                                                        <i class="fa-solid fa-folder {{ $isFolderCreated ? 'text-gray-500' : 'text-yellow-500' }} text-lg"></i>
                                                                        <h4 class="font-bold {{ $isFolderCreated ? 'text-gray-500' : 'text-gray-800' }}">{{ $type->name }}</h4>
                                                                    </div>
                                                                    <div class="flex items-center gap-2">
                                                                        @if($isFolderCreated)
                                                                            <span class="badge bg-gray-200 text-gray-700 border-0 text-xs font-medium px-2 py-1 rounded-full">Already Created</span>
                                                                        @elseif($allChildrenSelected)
                                                                            <span class="badge bg-yellow-100 text-yellow-800 border-0 text-xs font-medium px-2 py-1 rounded-full">All Selected</span>
                                                                        @elseif($selectedChildrenCount > 0)
                                                                            <span class="badge bg-blue-100 text-blue-800 border-0 text-xs font-medium px-2 py-1 rounded-full">{{ $selectedChildrenCount }}/{{ count($type->children) }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="space-y-2 pl-2">
                                                                    @foreach($type->children as $child)
                                                                        @php
                                                                            $isChildChecked = in_array($child->id, (array)$selectedRequirementTypes);
                                                                            $isChildCreated = $this->isChildRequirementCreated($child, $type);
                                                                        @endphp
                                                                        <label class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 {{ $isChildCreated ? 'bg-gray-100 border border-gray-300 opacity-75 cursor-not-allowed' : ($isChildChecked ? 'bg-green-50 border border-green-200' : 'bg-white border border-transparent hover:border-gray-200') }}">
                                                                            <input 
                                                                                type="checkbox" 
                                                                                wire:model.live="selectedRequirementTypes"
                                                                                value="{{ $child->id }}"
                                                                                class="checkbox checkbox-sm checkbox-primary rounded" 
                                                                                {{ $isChildCreated ? 'disabled' : '' }}
                                                                            />
                                                                            <span class="text-sm font-medium {{ $isChildCreated ? 'text-gray-500' : 'text-gray-700' }}">{{ $child->name }}</span>
                                                                            @if($isChildCreated)
                                                                                <i class="fa-solid fa-check text-gray-500 text-xs ml-auto"></i>
                                                                            @elseif($isChildChecked)
                                                                                <i class="fa-solid fa-check text-green-500 text-xs ml-auto"></i>
                                                                            @endif
                                                                        </label>
                                                                        
                                                                        <!-- Grandchildren level -->
                                                                        @if($child->children->isNotEmpty())
                                                                            <div class="space-y-1 ml-4 border-l-2 border-gray-200 pl-3">
                                                                                @foreach($child->children as $grandchild)
                                                                                    @php
                                                                                        $isGrandchildChecked = in_array($grandchild->id, (array)$selectedRequirementTypes);
                                                                                        $isGrandchildCreated = $this->isGrandchildRequirementCreated($grandchild, $child, $type);
                                                                                    @endphp
                                                                                    <label class="flex items-center space-x-3 p-2 rounded-lg transition-all duration-200 {{ $isGrandchildCreated ? 'bg-gray-100 border border-gray-300 opacity-75 cursor-not-allowed' : ($isGrandchildChecked ? 'bg-green-50 border border-green-200' : 'bg-white border border-transparent hover:border-gray-200') }}">
                                                                                        <input 
                                                                                            type="checkbox" 
                                                                                            wire:model.live="selectedRequirementTypes"
                                                                                            value="{{ $grandchild->id }}"
                                                                                            class="checkbox checkbox-xs checkbox-primary rounded" 
                                                                                            {{ $isGrandchildCreated ? 'disabled' : '' }}
                                                                                        />
                                                                                        <span class="text-xs font-medium {{ $isGrandchildCreated ? 'text-gray-500' : 'text-gray-700' }}">{{ $grandchild->name }}</span>
                                                                                        @if($isGrandchildCreated)
                                                                                            <i class="fa-solid fa-check text-gray-500 text-xs ml-auto"></i>
                                                                                        @elseif($isGrandchildChecked)
                                                                                            <i class="fa-solid fa-check text-green-500 text-xs ml-auto"></i>
                                                                                        @endif
                                                                                    </label>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @else
                                                            @php
                                                                $isRequirementCreated = $this->isRequirementCreated($type);
                                                            @endphp
                                                            <label class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 {{ $isRequirementCreated ? 'bg-gray-100 border border-gray-300 opacity-75 cursor-not-allowed' : ($isChecked ? 'bg-green-50 border border-green-300' : 'bg-white border border-transparent hover:border-gray-200') }}">
                                                                <input 
                                                                    type="checkbox" 
                                                                    wire:model.live="selectedRequirementTypes"
                                                                    value="{{ $type->id }}"
                                                                    class="checkbox checkbox-primary rounded" 
                                                                    {{ $isRequirementCreated ? 'disabled' : '' }}
                                                                />
                                                                <span class="font-medium {{ $isRequirementCreated ? 'text-gray-500' : 'text-gray-700' }}">{{ $type->name }}</span>
                                                                @if($isRequirementCreated)
                                                                    <i class="fa-solid fa-check text-gray-500 ml-auto"></i>
                                                                @elseif($isChecked)
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

                        <!-- Right Column: Timeline & Files -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fa-solid fa-calendar-day text-green-500"></i>
                                Timeline & Files
                            </h3>
                            
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <x-text-fieldset 
                                    type="datetime-local" 
                                    name="due" 
                                    wire:model="due" 
                                    label="Due Date & Time"
                                    :min="now()->format('Y-m-d\TH:i')"
                                    required
                                />
                                <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info text-blue-500"></i>
                                    Set the deadline for requirement submission
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <div class="form-control">
                                    <x-file-fieldset 
                                        name="required_files" 
                                        wire:model="required_files" 
                                        label="Required Files (Optional)"
                                        help="Upload guide files or templates. Max 5 files, 15MB each."
                                        multiple
                                    />
                                </div>
                                <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                    <i class="fa-solid fa-upload text-blue-500"></i>
                                    Supported formats: PDF, DOC, XLS, PPT, Images, ZIP, etc.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Programs Assignment - Full Width Below -->
                    <div class="space-y-6">
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-users text-purple-500"></i>
                                Assignment
                            </h3>
                            
                            <!-- Programs -->
                            <div class="form-control">
                                <div class="flex items-center justify-between mb-4">
                                    <label class="label justify-start gap-2 p-0">
                                        <span class="label-text font-semibold text-gray-700">Select Programs</span>
                                        @error('selectedPrograms')
                                            <span class="label-text-alt text-red-600 ml-2">{{ $message }}</span>
                                        @enderror
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <span class="text-sm font-medium text-gray-600">Select All</span>
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="selectAllPrograms"
                                            class="toggle toggle-primary toggle-sm" 
                                        />
                                    </label>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto p-2 custom-scrollbar">
                                    @foreach($this->programs as $program)
                                        <label class="flex items-center space-x-3 p-3 border-2 rounded-xl transition-all duration-200 cursor-pointer {{ in_array($program->id, $selectedPrograms) ? 'border-purple-300 bg-purple-50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                            <input 
                                                type="checkbox" 
                                                wire:model.live="selectedPrograms"
                                                value="{{ $program->id }}"
                                                class="checkbox checkbox-sm checkbox-primary rounded" 
                                                {{ $selectAllPrograms ? 'disabled' : '' }}
                                            />
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium text-gray-700 truncate">{{ $program->program_code }}</div>
                                                <div class="text-xs text-gray-500 truncate">{{ $program->program_name }}</div>
                                            </div>
                                            @if(in_array($program->id, $selectedPrograms))
                                                <i class="fa-solid fa-check text-purple-500"></i>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selection Summary - MOVED TO BOTTOM -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl shadow-sm p-6 border border-blue-100">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                                <i class="fa-solid fa-list-check text-blue-600"></i>
                                Selection Summary
                            </h3>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Requirement Types Summary -->
                                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-700 flex items-center gap-2">
                                            <i class="fa-solid fa-file-lines text-blue-500"></i>
                                            Requirement Types
                                        </h4>
                                        <span class="badge bg-blue-100 text-blue-800 border-0 font-medium px-3 py-1 rounded-full">
                                            @php
                                                $totalRequirements = 0;
                                                $folderSelections = [];
                                                $individualSelections = [];
                                                $customSelections = [];
                                                
                                                foreach($this->requirementTypes as $type) {
                                                    if($type->is_folder && $type->children->isNotEmpty()) {
                                                        $childIds = $type->children->pluck('id')->toArray();
                                                        $selectedChildren = array_intersect($childIds, (array)$selectedRequirementTypes);
                                                        if(count($selectedChildren) > 0) {
                                                            $folderSelections[] = [
                                                                'folder' => $type,
                                                                'selected_children' => $selectedChildren,
                                                                'all_selected' => count($selectedChildren) === count($childIds)
                                                            ];
                                                            $totalRequirements += count($selectedChildren);
                                                        }
                                                    } else {
                                                        if(in_array($type->id, (array)$selectedRequirementTypes)) {
                                                            $individualSelections[] = $type;
                                                            $totalRequirements++;
                                                        }
                                                    }
                                                }
                                                
                                                if($isOtherSelected) {
                                                    if(!empty($otherRequirementName)) {
                                                        $customSelections[] = $otherRequirementName;
                                                        $totalRequirements++;
                                                    } else {
                                                        $customSelections[] = 'Unnamed Custom Requirement';
                                                        $totalRequirements++;
                                                    }
                                                }
                                            @endphp
                                            {{ $totalRequirements }} selected
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-3 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                                        <!-- Folder Selections -->
                                        @foreach($folderSelections as $folderData)
                                            <div class="space-y-2">
                                                <div class="flex items-center gap-2 text-sm bg-yellow-50 text-yellow-800 px-3 py-2 rounded-lg border border-yellow-200">
                                                    <i class="fa-solid fa-folder text-yellow-600"></i>
                                                    <span class="font-medium">{{ $folderData['folder']->name }}</span>
                                                    <span class="text-yellow-700 text-xs ml-auto font-medium">
                                                        @if($folderData['all_selected'])
                                                            (All selected)
                                                        @else
                                                            ({{ count($folderData['selected_children']) }} selected)
                                                        @endif
                                                    </span>
                                                </div>
                                                @foreach($folderData['folder']->children as $child)
                                                    @if(in_array($child->id, $folderData['selected_children']))
                                                        <div class="flex items-center gap-2 text-sm bg-green-50 text-green-800 px-3 py-2 rounded-lg border border-green-200 ml-4">
                                                            <i class="fa-solid fa-check text-green-600 text-xs"></i>
                                                            <span class="truncate">{{ $child->name }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endforeach
                                        
                                        <!-- Individual Selections -->
                                        @foreach($individualSelections as $requirement)
                                            <div class="flex items-center gap-2 text-sm bg-green-50 text-green-800 px-3 py-2 rounded-lg border border-green-200">
                                                <i class="fa-solid fa-check text-green-600 text-xs"></i>
                                                <span class="truncate">{{ $requirement->name }}</span>
                                            </div>
                                        @endforeach
                                        
                                        <!-- Custom Requirements -->
                                        @if($isOtherSelected)
                                            @foreach($customSelections as $customReq)
                                                <div class="flex items-center gap-2 text-sm bg-purple-50 text-purple-800 px-3 py-2 rounded-lg border border-purple-200">
                                                    <i class="fa-solid fa-pen-to-square text-purple-600 text-xs"></i>
                                                    <span class="truncate">{{ $customReq }}</span>
                                                    <span class="badge bg-purple-100 text-purple-800 border-0 text-xs font-medium px-2 py-1 rounded ml-auto">Custom</span>
                                                </div>
                                            @endforeach
                                        @endif
                                        
                                        @if($totalRequirements === 0)
                                            <div class="text-center py-4 text-gray-400">
                                                <i class="fa-solid fa-inbox text-2xl mb-2"></i>
                                                <p class="text-sm">No requirements selected yet</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Programs Summary -->
                                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-700 flex items-center gap-2">
                                            <i class="fa-solid fa-graduation-cap text-green-500"></i>
                                            Assigned Programs
                                        </h4>
                                        <span class="badge bg-green-100 text-green-800 border-0 font-medium px-3 py-1 rounded-full">
                                            @if($selectAllPrograms)
                                                All ({{ count($this->programs) }})
                                            @else
                                                {{ count($selectedPrograms) }} selected
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                                        @if($selectAllPrograms)
                                            <div class="flex items-center gap-2 text-sm bg-green-50 text-green-800 px-3 py-2 rounded-lg border border-green-200">
                                                <i class="fa-solid fa-check-double text-green-600"></i>
                                                <span class="font-medium">All Programs</span>
                                                <span class="text-green-700 text-xs ml-auto">({{ count($this->programs) }} total)</span>
                                            </div>
                                        @elseif(count($selectedPrograms) > 0)
                                            @foreach($this->programs->whereIn('id', $selectedPrograms) as $program)
                                                <div class="flex items-center gap-2 text-sm bg-blue-50 text-blue-800 px-3 py-2 rounded-lg border border-blue-200">
                                                    <i class="fa-solid fa-check text-blue-600 text-xs"></i>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-medium truncate">{{ $program->program_code }}</div>
                                                        <div class="text-xs text-blue-700 truncate">{{ $program->program_name }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-4 text-gray-400">
                                                <i class="fa-solid fa-users-slash text-2xl mb-2"></i>
                                                <p class="text-sm">No programs selected yet</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Active Semester Info -->
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <i class="fa-solid fa-calendar-check text-green-500"></i>
                                        <span>Active Semester:</span>
                                        <span class="font-medium text-gray-800">{{ $this->activeSemester->name }}</span>
                                    </div>
                                    <div class="text-gray-500">
                                        {{ $this->activeSemester->start_date->format('M d, Y') }} - {{ $this->activeSemester->end_date->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                        <button type="button" wire:click="cancel" class="btn btn-outline btn-gray rounded-full px-8 py-3 text-lg font-medium">
                            <i class="fa-solid fa-xmark mr-2"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-full px-8 py-3 text-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200">
                            <i class="fa-solid fa-plus mr-2"></i>
                            Create Requirement
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="bg-white rounded-2xl shadow-sm p-8 text-center border border-red-100">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No Active Semester</h3>
                <p class="text-gray-600 mb-4">Please set an active semester before creating requirements.</p>
                <button wire:click="cancel" class="btn btn-outline btn-gray rounded-full px-6">
                    <i class="fa-solid fa-chevron-left mr-2"></i>
                    Back to Requirements
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .accordion-toggle:checked + .accordion-header .accordion-arrow {
        transform: rotate(180deg);
    }
    .accordion-toggle:checked ~ .accordion-content {
        max-height: 500px;
    }
    .accordion-toggle:not(:checked) ~ .accordion-content {
        max-height: 0;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush