<div>
    <input type="checkbox" id="createRequirement" class="modal-toggle" />
    <div class="modal bg-gray-800" role="dialog">
        <div class="modal-box w-full max-w-2xl rounded-xl">
            <div class="pb-4 flex items-center space-x-3">
                <i class="fa-solid fa-file-circle-plus text-lg"></i>
                <h3 class="text-xl text-gray-800 font-semibold">Create Requirement</h3>
            </div>
            @if($this->activeSemester)                 
                <div class="text-sm mb-4 p-3 rounded-lg" style="background-color: #73E2A7; border-left: 4px solid #1C7C54;">                     
                    <span class="font-semibold" style="color: #1B512D;">Active Semester:</span>                      
                    <span style="color: #1B512D;">{{ $this->activeSemester->name }}</span>                      
                    <span style="color: #1B512D;">({{ $this->activeSemester->start_date->format('M d, Y') }} - {{ $this->activeSemester->end_date->format('M d, Y') }})</span>                 
                </div>             
            @else
                <div class="alert alert-error mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>No active semester found. Please set an active semester first.</span>
                </div>
            @endif
            
            <form wire:submit.prevent="createRequirement" class="grid grid-cols-2 gap-x-4 gap-y-2" enctype="multipart/form-data">
                <div class="col-span-2">
                    <x-text-fieldset type="text" name="name" wire:model="name" label="Requirement Name" />
                </div>
                <div class="col-span-2">
                    <x-textarea-fieldset name="description" wire:model="description" label="Description" />
                </div>
                <x-text-fieldset type="datetime-local" name="due" wire:model="due" label="Due Date" />
                <x-select-fieldset name="priority" wire:model="priority" label="Priority">
                    <option value="low">Low</option>s
                    <option value="normal" selected>Normal</option>
                    <option value="high">High</option>
                </x-select-fieldset>
                <x-select-fieldset name="sector" wire:model.live="sector" label="Sector">
                    <option value="">Select Sector</option>
                    <option value="college">College</option>
                    <option value="department">Department</option>
                </x-select-fieldset>
                <x-select-fieldset name="assigned_to" wire:model="assigned_to" 
                    label="{{ $sector ? ($sector === 'college' ? 'Select College' : 'Select Department') : 'Select Sector First' }}"
                    :disabled="!$sector">
                    <option value="">Select {{ $sector ? ($sector === 'college' ? 'College' : 'Department') : 'Sector First' }}</option>
                    @foreach($this->sectorOptions as $option)
                        <option value="{{ $option->name }}">{{ $option->name }}</option>
                    @endforeach
                </x-select-fieldset>
                <div class="col-span-2">
                    <x-file-fieldset name="required_files" wire:model="required_files" label="Required Files" multiple />
                </div>
                <div class="col-span-2 modal-action">
                    <label for="createRequirement" class="btn rounded-full">Cancel</label>
                    <button type="submit" class="btn bg-1C7C54 text-white rounded-full">Submit</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="createRequirement"></label>
    </div>
</div>