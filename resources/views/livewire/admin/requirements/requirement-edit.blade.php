<div class="flex flex-col gap-6 w-full">
    {{-- edit requirement details --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <h2 class="text-xl font-bold">Edit Requirement Details</h2>
        <form wire:submit.prevent='updateRequirement({{ $requirement->id }})' class="grid grid-cols-2 gap-2"
            enctype="multipart/form-data">
            {{-- requirement name --}}
            <div class="col-span-2">
                <x-text-fieldset label="requirement name" name="name" wire:model="name" type="text" />
            </div>

            <div class="col-span-2">
                <x-textarea-fieldset label="requirement description" name="description" wire:model="description" />
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

            <div class="col-span-2 text-end">
                <button type="button" onclick="history.back()" class="btn btn-sm btn-default">Cancel</button>
                <button type="submit" class="btn btn-sm btn-success">Update Requirement</button>
            </div>
        </form>
    </div>

    {{-- requirement required files --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <div class="flex flex-row items-center justify-between w-full gap-4">
            <h2 class="text-xl font-bold">Requirement Required Files</h2>
            <label for="upload_required_files_modal" class="btn btn-sm btn-ghost btn-default">
                <i class="fa-solid fa-upload"></i>
                <span>Upload</span>
            </label>
        </div>
        <div class=" max-h-[500px] overflow-y-auto">
            <table class="table table-fixed table-sm table-striped">
                <thead>
                    <tr class="bg-gray-200">
                        <th>File Name</th>
                        <th>File Type</th>
                        <th>File Size</th>
                        <th>Date Modified</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requiredFiles as $file)
                        <tr>
                            <td class="truncate">{{ $file->file_name }}</td>
                            <td class="truncate">{{ $file->extension }}</td>
                            <td class="truncate">{{ $file->humanReadableSize }}</td>
                            <td class="truncate">{{ $file->updated_at->format('d/m/Y h:i a') }}</td>
                            <td class="space-x-2">
                                <button type="button"
                                    class="text-green-500 hover:text-green-700 hover:link">view</button>
                                <button wire:click='downloadFile({{ $file->id }})' type="button"
                                    class="text-blue-500 hover:text-blue-700 hover:link">download</button>
                                <button wire:click='removeFile({{ $file->id }})' type="button"
                                    class="text-red-500 hover:text-red-700 hover:link">remove</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No required files attached to this requirement.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- requirement assigned users --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <h2 class="text-xl font-bold">Requirement Assigned Users</h2>
        <div class=" max-h-[500px] overflow-y-auto">
            <table class="table table-fixed table-sm table-striped">
                <thead>
                    <tr class="bg-gray-200">
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>College</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignedUsers as $user)
                        <tr class="hover:bg-gray-100 hover:cursor-pointer" wire:click='showUser({{ $user->id }})'>
                            <td class="truncate">{{ $user->full_name }}</td>
                            <td class="truncate">{{ $user->email }}</td>
                            <td class="truncate">{{ $user->department->name }}</td>
                            <td class="truncate">{{ $user->college->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No users assigned to this requirement.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- upload required files modal --}}
    <input type="checkbox" id="upload_required_files_modal" class="modal-toggle" />
    <div class="modal" role="dialog">
        <form wire:submit.prevent='uploadRequiredFiles' class="modal-box flex flex-col gap-2">
            <div class="flex flex-row gap-4 w-full justify-between">
                <h3 class="text-lg font-bold">Upload Required Files</h3>
                <label for="upload_required_files_modal" class="btn btn-ghost btn-default btn-sm btn-circle"><i
                        class="fa-solid fa-xmark"></i></label>
            </div>
            <x-file-fieldset name="required_files" wire:model="required_files" multiple />
            <button type="submit" class="btn btn-success btn-sm w-full">Submit</button>
        </form>
        <label class="modal-backdrop" for="upload_required_files_modal"></label>
    </div>
</div>
