<div class="flex flex-col gap-6 w-full">
    {{-- requirement details --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <h2 class="text-xl font-bold">Requirement Details</h2>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-8 divide-gray-500 divide-2">
            <div class="py-2 flex flex-col md:flex-row justify-between col-span-1 md:col-span-3">
                <dt class="font-bold uppercase">Name</dt>
                <dd>{{ $requirement->name }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between col-span-1 md:col-span-3">
                <dt class="font-bold uppercase">Description</dt>
                <dd>{{ $requirement->description ?? '-' }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Due Date</dt>
                <dd>{{ $requirement->due->format('d/m/Y h:i a') }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Assigned To</dt>
                <dd>{{ $requirement->assigned_to }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Status</dt>
                <dd>{{ $requirement->status }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Priority</dt>
                <dd>{{ $requirement->priority }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Created By</dt>
                <dd>{{ $requirement->createdBy->full_name }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Updated By</dt>
                <dd>{{ $requirement->updatedBy->full_name ?? '-' }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Archived By</dt>
                <dd>{{ $requirement->archived_by ?? '-' }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Created At</dt>
                <dd>{{ $requirement->created_at->format('d/m/Y h:i a') }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Updated At</dt>
                <dd>{{ $requirement->updated_at->format('d/m/Y h:i a') }}</dd>
            </div>
        </dl>
    </div>

    {{-- requirement required files --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <div class="flex flex-col md:flex-row items-center justify-between w-full gap-4">
            <h2 class="text-xl font-bold">Requirement Required Files</h2>
            <label for="upload_required_files_modal" class="btn btn-sm btn-ghost btn-default">
                <i class="fa-solid fa-upload"></i>
                <span>Upload</span>
            </label>
        </div>
        <div class="max-h-[500px] overflow-x-auto">
            <table class="table table-fixed table-sm table-striped min-w-[500px]">
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
        <div class="max-h-[500px] overflow-x-auto">
            <table class="table table-fixed table-sm table-striped min-w-[600px]">
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

    {{-- requirement submissions --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <h2 class="text-xl font-bold">Requirement Submissions</h2>
        <div class="max-h-[500px] overflow-x-auto">
            <table class="table table-fixed table-sm table-striped min-w-[700px]">
                <thead>
                    <tr class="bg-gray-200">
                        <th>File Name</th>
                        <th>File Type</th>
                        <th>File Size</th>
                        <th>Date Modified</th>
                        <th>Submitted By</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @forelse ($collection as $item) --}}
                    {{-- @empty --}}
                    <tr>
                        <td colspan="6" class="text-center">No files submitted to this requirement.</td>
                    </tr>
                    {{-- @endforelse --}}
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

    {{-- view file modal --}}
</div>
