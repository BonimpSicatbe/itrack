<div wire:poll.500ms class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
    {{-- header title --}}
    <div class="text-lg font-bold uppercase">Requirements</div>
    {{-- header actions --}}
    <div class="flex flex-row gap-4 w-full">
        <input type="text" name="search" id="search" class="input input-sm input-bordered w-128"
            placeholder="Search requirements" wire:model="search">
        <div class="grow"></div>
        <label for="createRequirement" class="btn btn-sm btn-default">Create Requirement</label>
    </div>
    {{-- content --}}
    <div class="overflow-x-auto">
        <table class="table table-zebra w-full text-center">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Target</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @if ($requirements->isNotEmpty())
                    @foreach ($requirements as $requirement)
                        <tr class="items-center cursor-pointer hover:bg-gray-100 transition-colors">
                            <td class="text-start">
                                <i class="fa-solid fa-file min-w-[20px] text-center"></i>
                                <span class="font-semibold">{{ $requirement->name }}</span>
                            </td>
                            <td>
                                {{ $requirement->target === 'department'
                                    ? optional(\App\Models\Department::where('id', $requirement->target_id)->first())->name
                                    : optional(\App\Models\Requirement::where('id', $requirement->target_id)->first())->name }}
                            </td>
                            <td>{{ $requirement->created_by }}</td>
                            <td>{{ $requirement->created_at }}</td>
                            <td>
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

    {{-- createRequirement modal --}}
    <input type="checkbox" id="createRequirement" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Create Requirement</h3>
            <form wire:submit='createRequirement'>
                <x-text-fieldset type="text" name="name" label="Requirement name" />
                <x-textarea-fieldset name="description" label="Requirement description" />
                <x-text-fieldset type="date" name="due" label="Requirement due" />
                <x-file-fieldset name="required_files" label="Required Files" />

                <x-select-fieldset name="target" label="Select Target">
                    <option value="college">College</option>
                    <option value="department">Department</option>
                </x-select-fieldset>

                @if ($target === 'college')
                    <x-select-fieldset name="target_id" label="Select College">
                        @foreach ($colleges as $college)
                            <option value="{{ $college->id }}">{{ $college->name }}</option>
                        @endforeach
                    </x-select-fieldset>
                @elseif ($target === 'department')
                    <x-select-fieldset name="target_id" label="Select Department">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </x-select-fieldset>
                @endif


                <div class="modal-action">
                    <label for="createRequirement" class="btn btn-sm btn-default uppercase">cancel</label>
                    <button type="submit" class="btn btn-sm btn-default uppercase">submit</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="createRequirement"></label>
    </div>
</div>
