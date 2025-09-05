<div>
    <input type="checkbox" id="createRequirement" class="modal-toggle" />
    <div class="modal bg-gray-800 bg-opacity-75 modal-no-animation" role="dialog">
        <div class="modal-box w-full max-w-2xl rounded-xl bg-white">
            <div class="pb-4 flex items-center space-x-3 border-b border-gray-200">
                <i class="fa-solid fa-file-circle-plus text-green-600 text-2xl"></i>
                <h3 class="text-xl text-gray-800 font-semibold">Create Requirement</h3>
            </div>
            
            @if($this->activeSemester)
                <div class="text-sm mb-4 p-3 rounded-lg bg-green-100 border-l-4 border-green-600 text-green-800 mt-4">
                    <span class="font-semibold">Active Semester:</span>
                    <span>{{ $this->activeSemester->name }}</span>
                    <span>({{ $this->activeSemester->start_date->format('M d, Y') }} - {{ $this->activeSemester->end_date->format('M d, Y') }})</span>
                </div>
            @else
                <div class="alert alert-error mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6 float-left mr-3" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>No active semester found. Please set an active semester first.</span>
                </div>
            @endif
            
            @if($this->activeSemester)
            <form wire:submit.prevent="createRequirement" class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-4" enctype="multipart/form-data">
                <div class="md:col-span-2">
                    <x-text-fieldset 
                        type="text" 
                        name="name" 
                        wire:model="name" 
                        label="Requirement Name" 
                        placeholder="Enter requirement name"
                        required
                    />
                </div>
                
                <div class="md:col-span-2">
                    <x-textarea-fieldset 
                        name="description" 
                        wire:model="description" 
                        label="Description" 
                        rows="3"
                        placeholder="Enter requirement description"
                        required
                    />
                </div>
                
                <x-text-fieldset 
                    type="datetime-local" 
                    name="due" 
                    wire:model="due" 
                    label="Due Date"
                    :min="now()->format('Y-m-d\TH:i')"
                    required
                />
                
                <x-select-fieldset 
                    name="priority" 
                    wire:model="priority" 
                    label="Priority"
                    required
                >
                    <option value="">Select Priority</option>
                    <option value="low">Low</option>
                    <option value="normal" selected>Normal</option>
                    <option value="high">High</option>
                </x-select-fieldset>
                
                <x-select-fieldset 
                    name="sector" 
                    wire:model.live="sector" 
                    label="Sector"
                    required
                >
                    <option value="">Select Sector</option>
                    <option value="college">College</option>
                    <option value="department">Department</option>
                </x-select-fieldset>
                
                <x-select-fieldset 
                    name="assigned_to" 
                    wire:model="assigned_to" 
                    :label="$sector ? ($sector === 'college' ? 'Select College' : 'Select Department') : 'Select Sector First'"
                    :disabled="!$sector"
                    required
                >
                    <option value="">Select {{ $sector ? ($sector === 'college' ? 'College' : 'Department') : 'Sector First' }}</option>
                    @foreach($this->sectorOptions as $option)
                        <option value="{{ $option->name }}">{{ $option->name }}</option>
                    @endforeach
                </x-select-fieldset>
                
                <div class="md:col-span-2">
                    <x-file-fieldset 
                        name="required_files" 
                        wire:model="required_files" 
                        label="Required Files" 
                        multiple 
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.txt,.zip,.rar,.7z,.mp4,.avi,.mkv,.mp3,.wav"
                    />
                    <p class="text-xs text-gray-500 mt-1">Max file size: 15MB per file, Max files: 5</p>
                </div>
                
                <div class="md:col-span-2 flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-4">
                    <label for="createRequirement" class="btn btn-outline rounded-full px-6 py-2 border-gray-300 text-gray-700 hover:bg-gray-100">
                        Cancel
                    </label>
                    <button 
                        type="submit" 
                        class="btn bg-green-600 hover:bg-green-700 text-white rounded-full px-6 py-2"
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
            @endif
        </div>
        <label class="modal-backdrop" for="createRequirement"></label>
    </div>
</div>