<div class="flex flex-col gap-6 w-full">
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        {{-- requirements table list --}}
        <div class="flex flex-col gap-4 w-full h-full">
            {{-- header / actions --}}
            <div class="flex flex-row items-center gap-4 w-full">
                <div class="grow">
                    <h2 class="text-lg font-semibold">Requirements List</h2>
                </div>
                <div class="">
                    <input type="text" wire:model.live="search" id="search" class="input input-bordered input-sm w-xs"
                        placeholder="Search requirements...">
                </div>
                <div>
                    <label for="create_requirement_modal" class="btn btn-sm btn-success">
                        <i class="fa-solid fa-plus"></i>
                        <span>Create Requirement</span>
                    </label>
                </div>
            </div>

            {{-- body / table --}}
            <div class="max-h-[500px] overflow-y-auto">
                <table class="table table-fixed table-striped table-pin-rows table-sm">
                    <thead>
                        <tr class="bg-base-300 font-bold uppercase">
                            <th>name</th>
                            <th>description</th>
                            <th>due date</th>
                            <th>status</th>
                            <th>priority</th>
                            <th>created at</th>
                            <th>action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requirements as $requirement)
                            <tr>
                                <td class="truncate">{{ $requirement->name }}</td>
                                <td class="truncate">{{ $requirement->description }}</td>
                                <td class="truncate">{{ \Carbon\Carbon::parse($requirement->due)->format('F d, Y') }}</td>
                                <td class="truncate">{{ $requirement->status }}</td>
                                <td class="truncate">{{ $requirement->priority }}</td>
                                <td class="truncate">{{ \Carbon\Carbon::parse($requirement->created_at)->format('F d, Y') }}</td>
                                <td class="flex flex-row gap-2 truncate">
                                    <a href="{{ route('admin.requirements.show', ['requirement' => $requirement]) }}"
                                        class="btn btn-xs btn-ghost btn-success">
                                        <i class="fa-solid fa-eye"></i>
                                        {{-- <span>View</span> --}}
                                    </a>
                                    <form wire:submit.live='deleteRequirement({{ $requirement->id }})'>
                                        <button type="submit" class="btn btn-xs btn-ghost btn-error">
                                            <i class="fa-solid fa-trash"></i>
                                            {{-- <span>Delete</span> --}}
                                        </button>
                                    </form>
                                    <a href="" class="btn btn-xs btn-ghost btn-info">
                                        <i class="fa-solid fa-edit"></i>
                                        {{-- <span>Edit</span> --}}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No requirements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- create requirement modal --}}
    <input type="checkbox" id="create_requirement_modal" class="modal-toggle" />
    <div class="modal" role="dialog">
        {{-- header --}}
        <div class="modal-box w-full max-w-5xl">
            <div class="flex flex-row gap-4 justify-between items-center w-full">
                <h3 class="text-lg font-bold">Create New Requirement</h3>
                <label for="create_requirement_modal" class="btn btn-sm btn-circle btn-ghost btn-default"><i
                        class="fa-solid fa-xmark"></i></label>
            </div>

            {{-- content --}}
            <form wire:submit.prevent='createRequirement' class="grid grid-cols-2 gap-2" enctype="multipart/form-data">
                {{-- requirement name --}}
                <div class="col-span-2">
                    <x-text-fieldset label="requirement name" name="name" wire:model="name" type="text" />
                </div>

                <div class="col-span-2">
                    <x-textarea-fieldset label="requirement description" name="description"
                        wire:model="description" />
                </div>

                {{-- requirement due date --}}
                <x-text-fieldset label="requirement due date & time" name="due" wire:model="due"
                    type="datetime-local" />

                {{-- requirement priority --}}
                <x-select-fieldset label="requirement priority" name="priority" wire:model="priority">
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                </x-select-fieldset>

                {{-- requirement sector --}}
                <x-select-fieldset wire:model.live="sector" label="requirement sector">
                    <option value="college">College</option>
                    <option value="department">Department</option>
                </x-select-fieldset>

                {{-- requirement assigned to --}}
                @if ($this->sector === 'college')
                    <x-select-fieldset name="assigned_to" wire:model="assigned_to" label="select college">
                        @foreach ($colleges as $college)
                            <option value="{{ $college->id }}">{{ $college->name }}</option>
                        @endforeach
                    </x-select-fieldset>
                @elseif ($this->sector === 'department')
                    <x-select-fieldset name="assigned_to" wire:model="assigned_to" label="select department">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </x-select-fieldset>
                @else
                    <x-select-fieldset label="select sector first" disabled>
                        <option value="">select sector first</option>
                    </x-select-fieldset>
                @endif

                <div class="col-span-2">
                    <x-file-fieldset label="requirement file" name="required_files" wire:model="required_files" multiple />
                </div>


                <div class="col-span-2 text-center">
                    <button type="submit" class="btn btn-sm btn-wide btn-success">Create Requirement</button>
                </div>
            </form>
        </div>

        {{-- backdrop close function --}}
        <label class="modal-backdrop" for="create_requirement_modal">Close</label>
    </div>
</div>
