<div class="flex flex-col gap-6 w-full">
    {{-- requirement details --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <h2 class="text-xl font-bold">Requirement Details</h2>
        <dl class="grid grid-cols-3 gap-x-8 divide-gray-500 divide-2">
            <div class="py-2 flex justify-between col-span-3">
                <dt class="font-bold uppercase">Name</dt>
                <dd>{{ $requirement->name }}</dd>
            </div>
            <div class="py-2 flex justify-between col-span-3">
                <dt class="font-bold uppercase">Description</dt>
                <dd>{{ $requirement->description ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="font-bold uppercase">Due Date</dt>
                <dd>{{ $requirement->due->format('d/m/Y h:i a') }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="font-bold uppercase">Assigned To</dt>
                <dd>{{ $requirement->assigned_to }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="font-bold uppercase">Status</dt>
                <dd>{{ $requirement->status }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="font-bold uppercase">Priority</dt>
                <dd>{{ $requirement->priority }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="font-bold uppercase">Created By</dt>
                <dd>{{ $requirement->createdBy->full_name }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="font-bold uppercase">Updated By</dt>
                <dd>{{ $requirement->updatedBy->full_name ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="font-bold uppercase">Archived By</dt>
                <dd>{{ $requirement->archived_by ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="font-bold uppercase">Created At</dt>
                <dd>{{ $requirement->created_at->format('d/m/Y h:i a') }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="font-bold uppercase">Updated At</dt>
                <dd>{{ $requirement->updated_at->format('d/m/Y h:i a') }}</dd>
            </div>
        </dl>
    </div>

    {{-- requirement required files --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <div class="flex flex-row items-center justify-between w-full gap-4">
            <h2 class="text-xl font-bold">Requirement Required Files</h2>
            <input type="file" name="" id="" class="file-input file-input-sm">
        </div>
        <table class="table table-fixed table-sm table-striped max-h-[500px] overflow-y-auto">
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
                        <td class="truncate">{{ $file->name }}</td>
                        <td class="truncate">{{ $file->extension }}</td>
                        <td class="truncate">{{ $file->humanReadableSize }}</td>
                        <td class="truncate">{{ $file->updated_at->format('d/m/Y h:i a') }}</td>
                        <td class="space-x-2">
                            <button type="button" class="text-green-500 hover:text-green-700 hover:link">view</button>
                            <button type="button" class="text-red-500 hover:text-red-700 hover:link">remove</button>
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

    {{-- requirement assigned users --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <h2 class="text-xl font-bold">Requirement Assigned Users</h2>
        <table class="table table-fixed table-sm table-striped max-h-[500px] overflow-y-auto">
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

    {{-- requirement submissions --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <h2 class="text-xl font-bold">Requirement Submissions</h2>
        <table class="table table-fixed table-sm table-striped max-h-[500px] overflow-y-auto">
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
