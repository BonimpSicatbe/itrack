{{--
    // TODO add priority option to requirement creation
    // TODO: Create a reusable table component that accepts headings, rows, and cells as inputs.
    //       Add support for a SORTABLE attribute to enable column sorting.
    //       This component should be used for all tables in the project to ensure consistency and reusability.
--}}

<div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
    {{-- header title --}}
    <div class="text-lg font-bold uppercase">Requirements</div>
    {{-- header actions --}}
    <div class="flex flex-row gap-4 w-full">
        {{-- filters --}}
        <input type="text" wire:model.live="search" id="search" class="input input-sm input-bordered w-128"
            placeholder="Search requirements">

        <div class="grow"></div>

        <label for="createRequirement" class="btn btn-sm btn-default">Create Requirement</label>
    </div>
    {{-- content --}}
    <div class="overflow-x-auto max-h-[500px]">
        <table class="table table-fixed text-nowrap table-zebra w-full">
            <thead>
                <tr class="capitalize">
                    <th class="cursor-pointer" wire:click="sortBy('name')">
                        Name
                        @if ($sortField === 'name')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                    <th>
                        Target
                    </th>
                    <th>
                        due date
                    </th>
                    <th class="cursor-pointer" wire:click="sortBy('created_by')">
                        Created by
                        @if ($sortField === 'created_by')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                    <th class="cursor-pointer" wire:click="sortBy('created_at')">
                        Created At
                        @if ($sortField === 'created_at')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @if ($requirements->isNotEmpty())
                    @foreach ($requirements as $requirement)
                        <tr class="items-center cursor-pointer hover:bg-gray-100 transition-colors">
                            <td class="truncate">{{ $requirement->name }}</td>
                            <td class=" truncate">
                                {{ $requirement->target === 'department'
                                    ? optional(\App\Models\Department::where('id', $requirement->target_id)->first())->name
                                    : optional(\App\Models\College::where('id', $requirement->target_id)->first())->name }}
                            </td>
                            <td class="truncate">{{ \Carbon\Carbon::parse($requirement->due)->format('F j, Y') }}</td>
                            <td class="truncate">{{ $requirement->createdBy->firstname }}
                                {{ $requirement->createdBy->middlename }} {{ $requirement->createdBy->lastname }}</td>
                            <td class="truncate">{{ $requirement->created_at->format('F j, Y - h:i A') }}</td>
                            <td class="truncate">
                                <a href="{{ route('admin.requirements.show', ['requirement' => $requirement]) }}"
                                    class="btn btn-sm btn-ghost btn-success btn-square text-success hover:text-white"><i
                                        class="fa-solid fa-eye min-w-[20px] text-center"></i></a>
                                <a href="{{ route('admin.requirements.show', ['requirement' => $requirement]) }}"
                                    class="btn btn-sm btn-ghost btn-info btn-square text-info hover:text-white"><i
                                        class="fa-solid fa-edit min-w-[20px] text-center"></i></a>
                                <button type="button" wire:click='deleteRequirement({{ $requirement->id }})'
                                    class="btn btn-sm btn-ghost btn-error btn-square text-error hover:text-white"><i
                                        class="fa-solid fa-trash min-w-[20px] text-center"></i></button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 text-sm">No requirements found.</td>
                    </tr>
                @endif
            </tbody>
        </table>

    </div>

    <div class="w-full text-center">
        {{ $requirements->links() }}
    </div>

    {{-- createRequirement modal --}}
    <input type="checkbox" id="createRequirement" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Create Requirement</h3>
            <form wire:submit.prevent='createRequirement'>
                <x-text-fieldset type="text" wire:model="name" label="Requirement Name" />
                <x-textarea-fieldset wire:model="description" label="Requirement Description" />
                <x-text-fieldset type="date" wire:model="due" label="Requirement Due" />
                <x-file-fieldset wire:model="required_files" label="Required Files" />

                <x-select-fieldset wire:model.live="target" label="Select Target">
                    @foreach ($this->targets as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </x-select-fieldset>

                <x-select-fieldset wire:model.live="target_id"
                    label="{{ !$this->target ? 'Select Target First' : ($this->target === 'college' ? 'Select College' : 'Select Department') }}"
                    :disabled="!$this->target">
                    @foreach ($this->target_ids as $target)
                        <option value="{{ $target->id }}">{{ $target->name }}</option>
                    @endforeach
                </x-select-fieldset>


                <div class="modal-action">
                    <label for="createRequirement" class="btn btn-sm btn-default uppercase">cancel</label>
                    <button type="submit" class="btn btn-sm btn-default uppercase">submit</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="createRequirement"></label>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('close-modal', () => {
                document.getElementById('createRequirement').checked = false;
            });
        });
    </script>
</div>
