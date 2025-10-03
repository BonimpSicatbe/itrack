<div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-5">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 sticky top-0 z-10 bg-gradient-to-r from-green-800 to-green-600">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="text-2xl fa-solid fa-circle-info"></i> Edit Requirement Details
        </h2>
        <button type="button" onclick="history.back()" 
                class="bg-white text-green-700 px-4 py-1.5 rounded-full shadow font-semibold text-sm transition-all duration-200 flex items-center gap-2 hover:bg-gray-50">
            <i class="fa-solid fa-chevron-left text-green-700"></i> Back
        </button>
    </div>

    <!-- Main Content - 3 Section Layout -->
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Section 1: Details with Files -->
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-info-circle text-green-600"></i> Requirement Details
                </h3>
                
                <!-- Basic Info -->
                <div class="space-y-4 mb-6">
                    <!-- Name -->
                    <div>
                        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1">Name</p>
                        <x-text-fieldset name="name" wire:model="name" type="text" placeholder="Enter requirement name" />
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Due Date -->
                    <div>
                        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1">Due Date</p>
                        <x-text-fieldset name="due" wire:model="due" type="datetime-local" 
                            :min="now()->format('Y-m-d\TH:i')" />
                        @error('due') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1">Description</p>
                        <x-textarea-fieldset name="description" wire:model="description" placeholder="Enter requirement description" />
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Required Files -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-folder-open text-green-600"></i> Required Files
                        </h4>
                        <button type="button" 
                                class="btn btn-sm bg-blue-600 text-white hover:bg-blue-700 rounded-full transition flex items-center gap-2"
                                wire:click="$set('showUploadModal', true)">
                            <i class="fa-solid fa-upload"></i>
                            Upload Files
                        </button>
                    </div>
                    <div class="space-y-2">
                        @forelse ($requiredFiles as $file)
                            <div class="bg-white rounded-lg p-3 border border-gray-300 hover:bg-gray-50 transition duration-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-file text-green-600"></i>
                                        <span class="font-medium text-gray-900">{{ $file->file_name }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('guide.download', $file->id) }}" 
                                           class="text-blue-500 hover:text-blue-700 transition duration-200"
                                           title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        @if($this->isPreviewable($file->mime_type))
                                        <a href="{{ route('guide.preview', $file->id) }}" 
                                           target="_blank" 
                                           class="text-green-500 hover:text-green-700 transition duration-200"
                                           title="Preview">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @endif
                                        <button wire:click.prevent="confirmFileRemoval({{ $file->id }})" 
                                                wire:loading.attr="disabled"
                                                type="button"
                                                class="text-red-500 hover:text-red-700 transition duration-200" 
                                                title="Delete">
                                            <span wire:loading.remove wire:target="removeFile">
                                                <i class="fa-solid fa-trash"></i>
                                            </span>
                                            <span wire:loading wire:target="removeFile">
                                                <i class="fa-solid fa-spinner animate-spin"></i>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white rounded-lg p-3 border border-gray-300 text-center">
                                <p class="text-gray-500 text-sm">No required files attached.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Section 2: Assigned To (Multiple Selection) -->
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-users text-green-600"></i> Assign To
                </h3>

                <div class="space-y-6">
                    <!-- Colleges Selection -->
                    <div class="form-control">
                        <label class="label justify-start gap-2 pb-2">
                            <span class="label-text font-semibold text-gray-700">Colleges</span>
                            @error('selectedColleges')
                                <span class="label-text-alt text-red-600 ml-auto">{{ $message }}</span>
                            @enderror
                        </label>
                        <div class="border border-gray-200 rounded-xl p-4 bg-white">
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
                                @foreach($colleges as $college)
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
                        <div class="border border-gray-200 rounded-xl p-4 bg-white {{ empty($selectedColleges) && !$selectAllColleges ? 'opacity-60 bg-gray-50' : '' }}">
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
                                    @foreach($departments as $department)
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
            </div>
        </div>
    </div>

    <!-- Update Button - Now positioned outside the main content -->
    <div class="px-6 pb-6 pt-4 border-t border-gray-200 bg-white">
        <div class="flex justify-end">
            <button type="submit" wire:click.prevent="updateRequirement" 
                    class="btn px-6 py-2 bg-green-600 text-white hover:bg-green-700 rounded-full transition flex items-center gap-2"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="updateRequirement">
                    <i class="fa-solid fa-check mr-2"></i> Update Requirement
                </span>
                <span wire:loading wire:target="updateRequirement">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Updating...
                </span>
            </button>
        </div>
    </div>

    {{-- Upload Modal --}}
    <input type="checkbox" id="upload_required_files_modal" class="modal-toggle" @checked($showUploadModal) />
    <div class="modal" role="dialog" wire:ignore.self>
        <form wire:submit.prevent="uploadRequiredFiles" class="modal-box flex flex-col gap-3 rounded-xl">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Upload Required Files</h3>
                <button type="button" 
                        class="btn btn-ghost btn-sm btn-circle"
                        wire:click="$set('showUploadModal', false)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <x-file-fieldset name="required_files" wire:model="required_files" multiple />
            <button type="submit" class="btn px-4 py-1.5 bg-green-600 text-white hover:bg-green-700 rounded-full transition w-full">
                Submit
            </button>
        </form>
        <label class="modal-backdrop" wire:click="$set('showUploadModal', false)"></label>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <x-modal name="delete-file-confirmation-modal" :show="$showDeleteModal" maxWidth="md">
            <div class="bg-red-600 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm File Deletion</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-4">
                    @php
                        $fileToDelete = $requiredFiles->find($fileToDelete);
                    @endphp
                    <p class="text-gray-700">
                        Are you sure you want to delete the file 
                        <span class="font-semibold text-red-600">"{{ $fileToDelete->file_name ?? 'this file' }}"</span>?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. The file will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)" 
                            class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="removeFile" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="removeFile">
                            <i class="fa-solid fa-trash mr-2"></i> Delete
                        </span>
                        <span wire:loading wire:target="removeFile">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <style>
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