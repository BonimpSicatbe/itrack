{{--
    // TODO add priority option to requirement creation
    // TODO make the due date format to date time
--}}

<div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
    {{-- header title --}}
    <div class="text-lg font-bold uppercase">Requirements</div>
    {{-- header actions --}}
    <div class="flex flex-row gap-4 w-full">
        {{-- filters --}}
        <input type="text" wire:model.live="search" id="search" class="input input-sm input-bordered w-128"
            placeholder="Search requirement name">

        <div class="grow"></div>

        <label for="createRequirement" class="btn btn-sm btn-default">Create Requirement</label>
    </div>
    {{-- content --}}
    <div class="overflow-auto max-h-[500px] rounded-lg shadow-md">
        <x-table>
            <x-table.head>
                <x-table.row>
                    <x-table.header sortable :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">name</x-table.header>
                    <x-table.header>description</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'due' ? $sortDirection : null" wire:click="sortBy('due')">due</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'assigned_to' ? $sortDirection : null" wire:click="sortBy('assigned_to')">assigned to</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'status' ? $sortDirection : null" wire:click="sortBy('status')">status</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'priority' ? $sortDirection : null" wire:click="sortBy('priority')">priority</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'created_by' ? $sortDirection : null" wire:click="sortBy('created_by')">created by</x-table.header>
                </x-table.row>
            </x-table.head>
            <x-table.body>
                @forelse ($requirements as $requirement)
                    <x-table.row wire:click='showRequirement({{ $requirement->id }})'
                        wire:loading.class.delay="opacity-50" class="hover:bg-base-200 cursor-pointer">
                        <x-table.cell>{{ $requirement->name }}</x-table.cell>
                        <x-table.cell>{{ $requirement->description }}</x-table.cell>
                        <x-table.cell>{{ \Carbon\Carbon::parse($requirement->due)->format('F d, Y') }}</x-table.cell>
                        <x-table.cell>{{ $requirement->assigned_to }}</x-table.cell>
                        <x-table.cell>{{ $requirement->status }}</x-table.cell>
                        <x-table.cell>{{ $requirement->priority }}</x-table.cell>
                        <x-table.cell>{{ $requirement->createdBy->firstname }}
                            {{ $requirement->createdBy->lastname }}</x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="7">
                            <div
                                class="flex font-bold h-full items-center justify-center p-4 text-center text-lg truncate w-full text-gray-500">
                                No Results Found
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>

    <div class="w-full text-center">
        {{ $requirements->links() }}
    </div>

    {{-- createRequirement modal --}}
    <input type="checkbox" id="createRequirement" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box w-full max-w-2xl sm:w-3/4 md:w-2/3 lg:w-1/2 xl:max-w-5xl">
            <h3 class="text-lg font-bold">Create Requirement</h3>
            <form wire:submit.prevent='createRequirement' class="grid grid-cols-2 gap-x-4"
                enctype="multipart/form-data">
                <div class="col-span-2">
                    <x-text-fieldset type="text" name="name" wire:model="name" label="Requirement Name" />
                </div>
                <div class="col-span-2">
                    <x-textarea-fieldset name="description" wire:model="description" label="Requirement Description" />
                </div>
                <x-text-fieldset type="datetime-local" name="due" wire:model="due" label="due date" />

                <x-select-fieldset name="priority" wire:model="priority" label="Select Priority">
                    <option value="low">Low</option>
                    <option value="normal" selected>Normal</option>
                    <option value="high">High</option>
                </x-select-fieldset>

                <x-select-fieldset name="sector" wire:model.live="sector" label="Select Sector">
                    @foreach ($this->sectors as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </x-select-fieldset>

                <x-select-fieldset name="assigned_to" wire:model.live="assigned_to"
                    label="{{ !$this->sector ? 'Select Sector First' : ($this->sector === 'college' ? 'Select College' : 'Select Department') }}"
                    :disabled="!$this->sector">
                    @foreach ($this->sector_ids as $sector)
                        <option value="{{ $sector->name }}">{{ $sector->name }}</option>
                    @endforeach
                </x-select-fieldset>

                <div class="col-span-2">
                    <x-file-fieldset name="required_files" wire:model="required_files" label="Required Files" />
                </div>

                <div class=" col-span-2 modal-action flex flex-col sm:flex-row gap-2 sm:gap-4">
                    <label for="createRequirement"
                        class="btn btn-sm btn-default uppercase w-full sm:w-auto">cancel</label>
                    <button type="submit" class="btn btn-sm btn-default uppercase w-full sm:w-auto">submit</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="createRequirement"></label>
    </div>

    <script>
        // close modal after creation of new requirement
        document.addEventListener('livewire:init', () => {
            Livewire.on('close-modal', () => {
                document.getElementById('createRequirement').checked = false;
            });
        });
    </script>
</div>
