<div>
    <input type="checkbox" id="createRequirement" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box w-full max-w-2xl">
            <h3 class="text-lg font-bold">Create Requirement</h3>
            <form wire:submit.prevent="createRequirement" class="grid grid-cols-2 gap-x-4 gap-y-2">
                <div class="col-span-2">
                    <x-text-fieldset type="text" name="name" wire:model="name" label="Requirement Name" />
                </div>
                <div class="col-span-2">
                    <x-textarea-fieldset name="description" wire:model="description" label="Description" />
                </div>
                <x-text-fieldset type="datetime-local" name="due" wire:model="due" label="Due Date" />
                <x-select-fieldset name="priority" wire:model="priority" label="Priority">
                    <option value="low">Low</option>
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
                    <option value="">Select {{ $sector === 'college' ? 'College' : 'Department' }}</option>
                    @foreach ($this->sector_ids() as $item)
                        <option value="{{ $item->name }}">{{ $item->name }}</option>
                    @endforeach
                </x-select-fieldset>
                <div class="col-span-2">
                    <x-file-fieldset name="required_files" wire:model="required_files" label="Required Files" />
                </div>
                <div class="col-span-2 modal-action">
                    <label for="createRequirement" class="btn">Cancel</label>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="createRequirement"></label>
    </div>
</div>