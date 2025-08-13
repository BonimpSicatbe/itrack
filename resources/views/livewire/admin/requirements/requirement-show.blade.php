<div class="flex flex-col gap-6 w-full">
    {{-- requirement details --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold">Requirement Details</h2>
            <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement->id]) }}" 
            class="btn btn-sm btn-primary">
                <i class="fa-solid fa-pencil mr-1"></i> Edit
            </a>
        </div>
        <div class="grid grid-cols-2 gap-2">
            {{-- requirement name --}}
            <div class="col-span-2">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Name</span>
                    <span class="text-base font-medium text-gray-900">{{ $requirement->name ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="col-span-2">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Description</span>
                    <span class="text-base text-gray-700">{{ $requirement->description ?? 'N/A' }}</span>
                </div>
            </div>

            {{-- requirement due date --}}
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold text-gray-400 uppercase">Due Date & Time</span>
                <span class="text-base text-gray-900">{{ $requirement->due->format('M d, Y h:i A') ?? 'N/A' }}</span>
            </div>

            {{-- requirement priority --}}
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold text-gray-400 uppercase">Priority</span>
                <span class="text-base text-gray-900">
                    <span class="inline-block px-2 py-1 rounded text-xs font-bold
                        @if ($requirement->priority === 'low') bg-blue-100 text-blue-800
                        @elseif($requirement->priority === 'normal') bg-green-100 text-green-800
                        @elseif($requirement->priority === 'high') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($requirement->priority) ?? 'N/A' }}
                    </span>
                </span>
            </div>

            {{-- requirement sector --}}
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold text-gray-400 uppercase">Sector</span>
                <span class="text-base text-gray-900">
                    {{ $requirement->college ? 'College' : ($requirement->department ? 'Department' : 'N/A') }}
                </span>
            </div>

            {{-- requirement assigned to --}}
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold text-gray-400 uppercase">Assigned To</span>
                <span class="text-base text-gray-900">{{ $requirement->assigned_to ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    {{-- requirement required files --}}
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <h2 class="text-xl font-bold">Requirement Required Files</h2>
        <div class="max-h-[500px] overflow-y-auto">
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
                                <a href="{{ route('guide.download', ['media' => $file->id]) }}" 
                                    class="text-blue-500 hover:text-blue-700" 
                                    title="Download">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                @if($this->isPreviewable($file->mime_type))
                                <a href="{{ route('guide.preview', ['media' => $file->id]) }}" 
                                    target="_blank"
                                    class="text-green-500 hover:text-green-700" 
                                    title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @endif
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
        <div class="max-h-[500px] overflow-y-auto">
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
                            <td class="truncate">{{ $user->department->name ?? 'N/A' }}</td>
                            <td class="truncate">{{ $user->college->name ?? 'N/A' }}</td>
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
</div>