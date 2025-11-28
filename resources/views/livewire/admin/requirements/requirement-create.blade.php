<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex justify-between items-center text-white p-6 rounded-2xl shadow-lg"
        style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-white/20 rounded-xl">
                <i class="fa-solid fa-file-circle-plus text-white text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold">Create Requirement</h2>
                <p class="text-white/80 text-sm mt-1">Set up new requirements for selected programs</p>
            </div>
        </div>

        <button wire:click="cancel"
            class="btn bg-white/20 hover:bg-white/30 text-white text-sm flex items-center gap-2 border-0 rounded-xl shadow-md px-6 py-3 transition-all duration-200">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Back to Requirements</span>
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <div class="pb-5">
            @if ($this->activeSemester)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8 space-y-8">
                        <!-- STEP 1: Requirement Types -->
                        <div class="space-y-8">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="fa-solid fa-list-check text-blue-600 text-sm"></i>
                                    </div>
                                    Select Requirement Types
                                </h3>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-gray-500 font-medium">
                                        {{ count($selectedRequirementTypes) + ($isOtherSelected ? 1 : 0) }} selected
                                    </span>
                                    <button type="button" wire:click="$toggle('selectAllRequirements')"
                                        class="flex items-center space-x-2 p-3 bg-white rounded-xl border border-blue-300 cursor-pointer hover:bg-blue-50 transition-all duration-200 shadow-sm {{ $selectAllRequirements ? 'bg-blue-100 border-blue-400 shadow-md' : '' }}">
                                        <div
                                            class="w-5 h-5 rounded border border-blue-400 flex items-center justify-center {{ $selectAllRequirements ? 'bg-blue-500 border-blue-500' : 'bg-white' }}">
                                            @if ($selectAllRequirements)
                                                <i class="fa-solid fa-check text-white text-xs"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm font-semibold text-blue-800">
                                            {{ $selectAllRequirements ? 'Deselect All' : 'Select All' }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- Individual Requirements Grid -->
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach ($this->requirementTypes as $type)
                                        @if (!$type->is_folder || $type->children->isEmpty())
                                            @php
                                                $isRequirementCreated = $this->isRequirementCreated($type);
                                                $isChecked = in_array($type->id, (array) $selectedRequirementTypes);
                                            @endphp
                                            <div class="tooltip {{ $isRequirementCreated ? 'tooltip-warning' : '' }} flex items-center gap-3 p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer {{ $isRequirementCreated ? 'bg-gray-100 border-gray-300 opacity-75 cursor-not-allowed' : ($isChecked ? 'bg-green-50 border-green-500 shadow-sm' : 'bg-white border-gray-200 hover:border-green-500') }}"
                                                @if (!$isRequirementCreated) wire:click="toggleRequirement({{ $type->id }})" @endif
                                                @if ($isRequirementCreated) data-tip="This requirement has already been created" @endif>
                                                <div
                                                    class="w-5 h-5 rounded border flex items-center justify-center {{ $isChecked ? 'bg-green-500 border-green-500' : 'border-gray-400' }}">
                                                    @if ($isChecked)
                                                        <i class="fa-solid fa-check text-white text-sm"></i>
                                                    @endif
                                                </div>
                                                <span
                                                    class="font-medium {{ $isRequirementCreated ? 'text-gray-500' : 'text-gray-700' }} flex-1 text-sm">{{ $type->name }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Folders with Children -->
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    @foreach ($this->requirementTypes as $type)
                                        @if ($type->is_folder && $type->children->isNotEmpty())
                                            @php
                                                $isAnyChildSelected = false;
                                                $selectedChildrenCount = 0;
                                                $totalChildrenCount = 0;
                                                $isFolderCreated = $this->isRequirementCreated($type);
                                                $availableChildrenCount = 0;

                                                if ($type->is_folder && $type->children->isNotEmpty()) {
                                                    $childIds = $type->children->pluck('id')->toArray();
                                                    $selectedChildren = array_intersect(
                                                        $childIds,
                                                        (array) $selectedRequirementTypes,
                                                    );
                                                    $selectedChildrenCount = count($selectedChildren);
                                                    $totalChildrenCount = count($childIds);
                                                    $isAnyChildSelected = $selectedChildrenCount > 0;

                                                    // Count available (not created) children
                                                    foreach ($type->children as $child) {
                                                        if (!$this->isChildRequirementCreated($child, $type)) {
                                                            $availableChildrenCount++;
                                                        }
                                                    }
                                                }

                                                // Check if this is Midterm or Finals folder
                                                $isMidtermFolder = $type->name === 'Midterm';
                                                $isFinalsFolder = $type->name === 'Finals';
                                            @endphp

                                            <!-- Folder Card -->
                                            <div
                                                class="{{ $isFolderCreated ? 'bg-white border-gray-200 opacity-100' : ($isAnyChildSelected ? '' : 'bg-white') }}">
                                                <div class="flex items-center justify-between mb-4">
                                                    <div class="flex items-center gap-3">
                                                        <i
                                                            class="fa-solid fa-folder {{ $isFolderCreated ? 'text-gray-500' : ($isAnyChildSelected ? 'text-yellow-500' : 'text-yellow-500') }} text-lg"></i>
                                                        <div>
                                                            <h5
                                                                class="font-medium {{ $isFolderCreated ? 'text-gray-500' : 'text-gray-700' }}">
                                                                {{ $type->name }}</h5>
                                                            <p class="text-xs text-gray-500">
                                                                @if ($isFolderCreated)
                                                                    <span class="text-red-500">Already created</span>
                                                                @elseif($isAnyChildSelected)
                                                                    {{ $selectedChildrenCount }} of
                                                                    {{ $availableChildrenCount }} available selected
                                                                @else
                                                                    {{ $availableChildrenCount }} of
                                                                    {{ $totalChildrenCount }} available
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-2">
                                                        @if (($isMidtermFolder || $isFinalsFolder) && !$isFolderCreated && $availableChildrenCount > 0)
                                                            <button type="button"
                                                                wire:click="{{ $isMidtermFolder ? '$toggle(\'selectAllMidterm\')' : '$toggle(\'selectAllFinals\')' }}"
                                                                class="flex items-center space-x-2 p-2 bg-white rounded-lg border border-orange-300 hover:bg-orange-50 cursor-pointer transition-colors {{ ($isMidtermFolder && $selectAllMidterm) || ($isFinalsFolder && $selectAllFinals) ? 'bg-orange-100 border-orange-400' : '' }}">
                                                                <div
                                                                    class="w-5 h-5 rounded border border-orange-400 flex items-center justify-center {{ ($isMidtermFolder && $selectAllMidterm) || ($isFinalsFolder && $selectAllFinals) ? 'bg-orange-500 border-orange-500' : 'bg-white' }}">
                                                                    @if (($isMidtermFolder && $selectAllMidterm) || ($isFinalsFolder && $selectAllFinals))
                                                                        <i
                                                                            class="fa-solid fa-check text-white text-xs"></i>
                                                                    @endif
                                                                </div>
                                                                <span class="text-sm font-medium text-orange-800">
                                                                    {{ ($isMidtermFolder && $selectAllMidterm) || ($isFinalsFolder && $selectAllFinals) ? 'Deselect All' : 'Select All' }}
                                                                </span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Children Grid -->
                                                <div class="grid grid-cols-1 gap-2">
                                                    @foreach ($type->children as $child)
                                                        @php
                                                            $isChildChecked = in_array(
                                                                $child->id,
                                                                (array) $selectedRequirementTypes,
                                                            );
                                                            $isChildCreated = $this->isChildRequirementCreated(
                                                                $child,
                                                                $type,
                                                            );
                                                        @endphp

                                                        <div class="tooltip {{ $isChildCreated ? 'tooltip-warning' : '' }} flex items-center gap-3 p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer {{ $isChildCreated ? 'bg-gray-100 border-gray-200 opacity-75 cursor-not-allowed' : ($isChildChecked ? 'bg-green-50 border-green-400' : 'bg-white border-gray-200 hover:border-green-500') }}"
                                                            @if (!$isChildCreated) wire:click="toggleRequirement({{ $child->id }})" @endif
                                                            @if ($isChildCreated) data-tip="This requirement has already been created" @endif>
                                                            <div
                                                                class="w-5 h-5 rounded border flex items-center justify-center {{ $isChildChecked ? 'bg-green-500 border-green-500' : 'border-gray-400' }}">
                                                                @if ($isChildChecked)
                                                                    <i class="fa-solid fa-check text-white text-xs"></i>
                                                                @endif
                                                            </div>
                                                            <span
                                                                class="text-sm font-medium {{ $isChildCreated ? 'text-gray-500' : 'text-gray-700' }} flex-1">{{ $child->name }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Custom Requirement -->
                            <div
                                class="border-2 border-dashed border-purple-300 rounded-xl bg-purple-50 p-5 transition-all duration-300 {{ $isOtherSelected ? 'bg-purple-100 border-purple-400 shadow-sm' : '' }}">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" wire:model.live="isOtherSelected"
                                            class="checkbox checkbox-primary rounded" />
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-pen-to-square text-purple-600"></i>
                                            <h4 class="font-semibold text-gray-800">Custom Requirement</h4>
                                        </div>
                                    </div>
                                </label>

                                @if ($isOtherSelected)
                                    <div
                                        class="mt-4 p-4 bg-white border border-purple-200 rounded-lg transition-all duration-300">
                                        <x-text-fieldset type="text" name="otherRequirementName"
                                            wire:model="otherRequirementName" label="Custom Requirement Name"
                                            placeholder="e.g., Course Audit Report, Faculty Development Plan, etc."
                                            required />
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- STEP 2: Configuration & Assignment -->
                        <div class="border-t border-gray-200 pt-8">

                            <!-- Timeline & Files Section -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm mb-6">

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <!-- Timeline Card -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 mb-4">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                                <i class="fa-solid fa-calendar-day text-green-600"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-semibold text-gray-800">Timeline</h5>
                                                <p class="text-sm text-gray-600">Set submission deadline</p>
                                            </div>
                                        </div>

                                        <x-text-fieldset type="datetime-local" name="due" wire:model="due"
                                            label="Due Date & Time" :min="now()->format('Y-m-d\TH:i')" required />
                                    </div>

                                    <!-- Files Card -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 mb-4">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <i class="fa-solid fa-file-arrow-up text-blue-600"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-semibold text-gray-800">Files & Templates</h5>
                                                <p class="text-sm text-gray-600">Upload guides or templates</p>
                                            </div>
                                        </div>

                                        <x-file-fieldset name="required_files" wire:model="required_files"
                                            label="Required Files (Optional)"
                                            help="Max 5 files, 15MB each. Accepts images and PDFs"
                                            multiple
                                            accept="image/*,application/pdf" />
                                    </div>
                                </div>
                            </div>

                            <!-- Program Assignment Section -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                            <i class="fa-solid fa-users text-purple-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800 text-lg">Program Assignment</h4>
                                            <p class="text-sm text-gray-600">Select programs to assign these
                                                requirements</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <!-- College Filter -->
                                        <div class="flex items-center gap-2">
                                            <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Filter
                                                by College:</label>
                                            <select wire:model.live="selectedCollege"
                                                class="select select-bordered select-sm border-gray-300 rounded-lg focus:border-purple-500 focus:ring-purple-500">
                                                <option value="">All Colleges</option>
                                                @foreach ($this->colleges as $college)
                                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Select All Programs Button -->
                                        <button type="button" wire:click="$toggle('selectAllPrograms')"
                                            class="flex items-center space-x-2 p-3 bg-white rounded-xl border border-purple-300 cursor-pointer hover:bg-purple-50 transition-all duration-200 shadow-sm {{ $selectAllPrograms ? 'bg-purple-100 border-purple-400 shadow-md' : '' }}"
                                            @if ($this->programs->isEmpty()) disabled @endif>
                                            <div
                                                class="w-4 h-4 rounded border border-purple-400 flex items-center justify-center {{ $selectAllPrograms ? 'bg-purple-500 border-purple-500' : 'bg-white' }}">
                                                @if ($selectAllPrograms)
                                                    <i class="fa-solid fa-check text-white text-xs"></i>
                                                @endif
                                            </div>
                                            <span class="text-sm font-semibold text-purple-800">Select All
                                                Programs</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Programs Grid -->
                                <div class="mb-4">
                                    @if ($selectedCollege)
                                        <div class="text-sm text-gray-600 mb-3">
                                            <i class="fa-solid fa-filter text-purple-500 mr-1"></i>
                                            Showing programs from:
                                            <span class="font-semibold text-purple-700">
                                                {{ $this->colleges->find($selectedCollege)->name ?? 'Selected College' }}
                                            </span>
                                            <span class="text-gray-500 ml-2">
                                                ({{ count($this->programs) }}
                                                program{{ count($this->programs) !== 1 ? 's' : '' }})
                                            </span>
                                        </div>
                                    @endif

                                    @if ($this->programs->isEmpty())
                                        <div
                                            class="text-center py-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                                            <i class="fa-solid fa-inbox text-gray-400 text-3xl mb-3"></i>
                                            <p class="text-gray-500 font-medium">No programs found</p>
                                            <p class="text-sm text-gray-400 mt-1">
                                                @if ($selectedCollege)
                                                    No programs available for the selected college
                                                @else
                                                    No programs available in the system
                                                @endif
                                            </p>
                                        </div>
                                    @else
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-80 overflow-y-auto p-3 custom-scrollbar"
                                            wire:key="programs-grid-{{ $selectedCollege }}-{{ count($this->programs) }}">
                                            @foreach ($this->programs as $program)
                                                <div class="flex items-center gap-4 p-4 border-2 rounded-xl transition-all duration-200 cursor-pointer {{ in_array($program->id, $selectedPrograms) ? 'border-purple-400 bg-purple-50 shadow-md' : 'border-gray-200 bg-white hover:border-purple-300 hover:shadow-sm' }}"
                                                    wire:click="toggleProgram({{ $program->id }})"
                                                    wire:key="program-{{ $program->id }}-{{ in_array($program->id, $selectedPrograms) ? 'selected' : 'unselected' }}">
                                                    <div
                                                        class="w-5 h-5 rounded border border-gray-400 flex items-center justify-center {{ in_array($program->id, $selectedPrograms) ? 'bg-purple-500 border-purple-500' : 'bg-white' }}">
                                                        @if (in_array($program->id, $selectedPrograms))
                                                            <i class="fa-solid fa-check text-white text-xs"></i>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-semibold text-gray-800 truncate">
                                                            {{ $program->program_code }}</div>
                                                        <div class="text-sm text-gray-600 truncate">
                                                            {{ $program->program_name }}</div>
                                                        <!-- REMOVED COLLEGE NAME DISPLAY -->
                                                    </div>
                                                    <div
                                                        class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                                        <i
                                                            class="fa-solid fa-graduation-cap text-purple-600 text-sm"></i>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-3 pt-6">
                            <button type="button" wire:click="cancel" wire:loading.attr="disabled"
                                class="btn border-2 border-green-600 rounded-xl text-gray-700 px-5 py-3 text-sm font-semibold hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                Cancel
                            </button>
                            <button type="button"
                                class="btn bg-green-600 hover:bg-green-700 text-white rounded-xl px-5 py-3 text-sm font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center min-w-32"
                                wire:loading.attr="disabled" @if (empty($selectedRequirementTypes) && !$isOtherSelected) disabled @endif
                                wire:click="openConfirmationModal">

                                <span>
                                    Create Requirement
                                    @if (count($selectedRequirementTypes) + ($isOtherSelected ? 1 : 0) > 0)
                                        <span class="ml-2 bg-white/20 px-2 py-1 rounded-full text-sm">
                                            ({{ count($selectedRequirementTypes) + ($isOtherSelected ? 1 : 0) }})
                                        </span>
                                    @endif
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-sm p-8 text-center border border-red-100">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">No Active Semester</h3>
                    <p class="text-gray-600 mb-4">Please set an active semester before creating requirements.</p>
                    <button wire:click="cancel" class="btn btn-outline btn-gray rounded-xl px-6">
                        <i class="fa-solid fa-chevron-left mr-2"></i>
                        Back to Requirements
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Confirmation Modal -->
    <x-modal name="confirm-requirement-creation" :show="$showConfirmationModal" maxWidth="6xl">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-check text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Confirm Requirement Creation</h3>
                        <p class="text-gray-600 text-sm">Review your selections before proceeding</p>
                    </div>
                </div>
                <button wire:click="closeConfirmationModal"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Other Details -->
            <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-700 text-sm mb-2">Due Date & Time</h4>
                        <p class="text-gray-800 font-medium">{{ \Carbon\Carbon::parse($due)->format('M j, Y g:i A') }}
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 text-sm mb-2">Files Attached</h4>
                        <p class="text-gray-800 font-medium">
                            {{ count($required_files) }} file{{ count($required_files) !== 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2 Columns Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Requirements Column -->
                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fa-solid fa-file-lines text-blue-500"></i>
                            Requirements
                        </h4>
                        <span
                            class="badge bg-blue-100 text-blue-800 border-0 font-medium px-3 py-1 rounded-full text-sm">
                            {{ count($selectedRequirementTypes) + ($isOtherSelected ? 1 : 0) }} items
                        </span>
                    </div>

                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                        @php
                            $hasSelections = false;
                            $totalSelected = count($selectedRequirementTypes) + ($isOtherSelected ? 1 : 0);
                        @endphp

                        @if ($totalSelected === 0)
                            <div class="text-center py-4 text-gray-400">
                                <i class="fa-solid fa-inbox text-xl mb-2"></i>
                                <p class="text-sm">No requirements selected</p>
                            </div>
                        @else
                            <!-- Folder Selections -->
                            @foreach ($this->requirementTypes as $type)
                                @if ($type->is_folder && $type->children->isNotEmpty())
                                    @php
                                        $childIds = $type->children->pluck('id')->toArray();
                                        $selectedChildren = array_intersect(
                                            $childIds,
                                            (array) $selectedRequirementTypes,
                                        );
                                    @endphp
                                    @if (count($selectedChildren) > 0)
                                        @php $hasSelections = true; @endphp
                                        <div class="space-y-2">
                                            <div
                                                class="flex items-center gap-2 text-sm bg-blue-50 text-blue-800 px-3 py-2 rounded-lg border border-blue-200">
                                                <i class="fa-solid fa-folder text-blue-600"></i>
                                                <span class="font-semibold">{{ $type->name }}</span>
                                                <span
                                                    class="text-blue-700 text-xs ml-auto font-medium bg-blue-100 px-2 py-1 rounded-full">
                                                    {{ count($selectedChildren) }} selected
                                                </span>
                                            </div>
                                            @foreach ($type->children as $child)
                                                @if (in_array($child->id, $selectedChildren))
                                                    <div
                                                        class="flex items-center gap-2 text-sm bg-green-50 text-green-800 px-3 py-1 rounded-lg border border-green-200 ml-3">
                                                        <i class="fa-solid fa-check text-green-600 text-xs"></i>
                                                        <span
                                                            class="truncate font-medium text-xs">{{ $child->name }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    @if (in_array($type->id, (array) $selectedRequirementTypes))
                                        @php $hasSelections = true; @endphp
                                        <div
                                            class="flex items-center gap-2 text-sm bg-green-50 text-green-800 px-3 py-2 rounded-lg border border-green-200">
                                            <i class="fa-solid fa-check text-green-600 text-xs"></i>
                                            <span class="truncate font-medium">{{ $type->name }}</span>
                                        </div>
                                    @endif
                                @endif
                            @endforeach

                            <!-- Custom Requirement -->
                            @if ($isOtherSelected && !empty($otherRequirementName))
                                <div
                                    class="flex items-center gap-2 text-sm bg-purple-50 text-purple-800 px-3 py-2 rounded-lg border border-purple-200">
                                    <i class="fa-solid fa-pen-to-square text-purple-600 text-xs"></i>
                                    <span class="truncate font-medium">{{ $otherRequirementName }}</span>
                                    <span
                                        class="badge bg-purple-100 text-purple-800 border-0 text-xs font-medium px-2 py-1 rounded ml-auto">Custom</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Programs Column -->
                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fa-solid fa-graduation-cap text-green-500"></i>
                            Programs
                        </h4>
                        <span
                            class="badge bg-green-100 text-green-800 border-0 font-medium px-3 py-1 rounded-full text-sm">
                            @if ($selectAllPrograms)
                                All ({{ count($this->programs) }})
                            @else
                                {{ count(array_intersect($selectedPrograms, $this->programs->pluck('id')->toArray())) }}
                                selected
                            @endif
                        </span>
                    </div>

                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                        @if ($selectAllPrograms)
                            <div
                                class="flex items-center gap-2 text-sm bg-green-50 text-green-800 px-3 py-2 rounded-lg border border-green-200">
                                <i class="fa-solid fa-check-double text-green-600"></i>
                                <span class="font-semibold">
                                    All Programs
                                    @if ($selectedCollege)
                                        in {{ $this->colleges->find($selectedCollege)->name }}
                                    @else
                                        (All Colleges)
                                    @endif
                                </span>
                                <span class="text-green-700 text-xs ml-auto font-medium">({{ count($this->programs) }}
                                    total)</span>
                            </div>
                        @elseif(count($selectedPrograms) > 0)
                            @foreach ($this->programs->whereIn('id', $selectedPrograms)->take(5) as $program)
                                <div
                                    class="flex items-center gap-2 text-sm bg-blue-50 text-blue-800 px-3 py-2 rounded-lg border border-blue-200">
                                    <i class="fa-solid fa-check text-blue-600 text-xs"></i>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate text-xs">{{ $program->program_code }}</div>
                                        <div class="text-xs text-blue-700 truncate">{{ $program->program_name }}</div>
                                    </div>
                                </div>
                            @endforeach
                            @if (count($selectedPrograms) > 5)
                                <div class="text-center py-1">
                                    <span class="text-xs text-gray-500 font-medium">
                                        +{{ count($selectedPrograms) - 5 }} more programs
                                    </span>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4 text-gray-400">
                                <i class="fa-solid fa-users-slash text-lg mb-2"></i>
                                <p class="text-sm">No programs selected</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" wire:click="closeConfirmationModal"
                    class="btn border-2 border-gray-300 rounded-xl text-gray-700 px-5 py-2 text-sm font-semibold hover:bg-gray-50 transition-all duration-200">
                    Cancel
                </button>
                <button type="button" wire:click="createRequirement"
                    class="btn bg-green-600 hover:bg-green-700 text-white rounded-xl px-5 py-2 text-sm font-medium shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center min-w-32"
                    wire:loading.attr="disabled" wire:target="createRequirement">

                    <span wire:loading.remove wire:target="createRequirement">
                        Confirm
                        @if (count($selectedRequirementTypes) + ($isOtherSelected ? 1 : 0) > 0)
                            <span class="ml-2 bg-white/20 px-2 py-1 rounded-full text-sm">
                                ({{ count($selectedRequirementTypes) + ($isOtherSelected ? 1 : 0) }})
                            </span>
                        @endif
                    </span>

                    <span wire:loading wire:target="createRequirement" class="flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        Creating...
                    </span>
                </button>
            </div>
        </div>
    </x-modal>

    <script>
        document.addEventListener('alpine:init', () => {
            // Ensure program selection works properly
            Alpine.data('programSelection', () => ({
                init() {
                    // This ensures Livewire can properly handle the click events
                    console.log('Program selection initialized');
                }
            }));
        });
    </script>
</div>
