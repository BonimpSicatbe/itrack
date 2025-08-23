<div class="flex flex-col lg:flex-row gap-6 w-full">
    {{-- Left Column --}}
    <div class="w-full lg:w-2/3 space-y-6">
        {{-- Requirement Details Card --}}
        <div class="w-full bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 flex justify-between items-center bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">Requirement Details</h2>
                <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement->id]) }}" 
                   class="btn btn-sm btn-primary inline-flex items-center">
                    <i class="fa-solid fa-pencil mr-2"></i> Edit
                </a>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- requirement name --}}
                    <div class="col-span-2">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Name</span>
                            <span class="text-base font-medium text-gray-800">{{ $requirement->name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Description</span>
                            <span class="text-base text-gray-700">{{ $requirement->description ?? 'N/A' }}</span>
                        </div>
                    </div>

                    {{-- requirement due date --}}
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date & Time</span>
                        <span class="text-base text-gray-800">{{ $requirement->due->format('M d, Y h:i A') ?? 'N/A' }}</span>
                    </div>

                    {{-- requirement priority --}}
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</span>
                        <span class="text-base text-gray-800">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
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
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sector</span>
                        <span class="text-base text-gray-800">
                            {{ $requirement->college ? 'College' : ($requirement->department ? 'Department' : 'N/A') }}
                        </span>
                    </div>

                    {{-- requirement assigned to --}}
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</span>
                        <span class="text-base text-gray-800">{{ $requirement->assigned_to ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Required Files Card --}}
        <div class="w-full bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">Required Files</h2>
            </div>
            <div class="p-6">
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modified</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($requiredFiles as $file)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 truncate max-w-xs">{{ $file->file_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ strtoupper($file->extension) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->humanReadableSize }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->updated_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('guide.download', ['media' => $file->id]) }}" 
                                           class="text-blue-600 hover:text-blue-900 inline-flex items-center" 
                                           title="Download">
                                            <i class="fa-solid fa-download mr-1"></i>
                                        </a>
                                        @if($this->isPreviewable($file->mime_type))
                                        <a href="{{ route('guide.preview', ['media' => $file->id]) }}" 
                                           target="_blank"
                                           class="text-green-600 hover:text-green-900 inline-flex items-center" 
                                           title="View">
                                            <i class="fa-solid fa-eye mr-1"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No required files attached to this requirement.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div class="w-full lg:w-1/3">
        {{-- Assigned Users Card --}}
        <div class="w-full bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden h-full">
            <div class="border-b border-gray-100 px-6 py-4 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">Assigned Users</h2>
            </div>
            <div class="p-6">
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($assignedUsers as $user)
                                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" wire:click='showUser({{ $user->id }})'>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600">
                                                {{ substr($user->full_name, 0, 1) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->department->name ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No users assigned to this requirement.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>