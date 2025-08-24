<div class="flex flex-col lg:flex-row gap-6 w-[92%] mx-auto">
    {{-- Left Column --}}
    <div class="w-full lg:w-2/3 space-y-6">
        {{-- Requirement Details Card --}}
        <div class="w-full bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-DEF4C6 border-b border-DEF4C6 px-6 py-4 flex justify-between items-center">
                <h2 class="text-gray-800 text-xl font-bold tracking-wide">Requirement Details</h2>
                <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement->id]) }}" 
                   class="bg-white text-1C7C54 px-4 py-1.5 rounded-full shadow hover:bg-DEF4C6 hover:text-1B512D font-semibold text-sm transition-all duration-200">
                    <i class="text-1C7C54 fa-solid fa-pencil mr-2"></i> EDIT
                </a>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- requirement name --}}
                    <div class="col-span-2">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-1B512D uppercase tracking-wider">Name</span>
                            <span class="text-base font-medium text-gray-900">{{ $requirement->name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    {{-- description --}}
                    <div class="col-span-2">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-1B512D uppercase tracking-wider">Description</span>
                            <span class="text-base text-gray-700 leading-relaxed">{{ $requirement->description ?? 'N/A' }}</span>
                        </div>
                    </div>

                    {{-- due date --}}
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-1B512D uppercase tracking-wider">Due Date & Time</span>
                        <span class="text-sm font-medium text-gray-800">{{ $requirement->due->format('M d, Y h:i A') ?? 'N/A' }}</span>
                    </div>

                    {{-- priority --}}
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-1B512D uppercase tracking-wider">Priority</span>
                        <span class="text-sm">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold shadow-sm
                                @if ($requirement->priority === 'low') bg-B1CF5F text-1B512D
                                @elseif($requirement->priority === 'normal') bg-73E2A7 text-1C7C54
                                @elseif($requirement->priority === 'high') bg-red-100 text-red-800
                                @else bg-gray-200 text-gray-800 @endif">
                                {{ ucfirst($requirement->priority) ?? 'N/A' }}
                            </span>
                        </span>
                    </div>

                    {{-- sector --}}
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-1B512D uppercase tracking-wider">Sector</span>
                        <span class="text-sm text-gray-800">
                            {{ $requirement->college ? 'College' : ($requirement->department ? 'Department' : 'N/A') }}
                        </span>
                    </div>

                    {{-- assigned to --}}
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-1B512D uppercase tracking-wider">Assigned To</span>
                        <span class="text-sm text-gray-800">{{ $requirement->assigned_to ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Required Files Card --}}
        <div class="w-full bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-1C7C54 to-1B512D font-semibold text-xl">Required Files</div>
            <div class="p-2">
                <div class="overflow-hidden rounded-xl border border-DEF4C6">
                    <table class="min-w-full divide-y divide-DEF4C6">
                        <thead class="bg-DEF4C6">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-1B512D uppercase">File Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-1B512D uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-1B512D uppercase">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-1B512D uppercase">Modified</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-1B512D uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($requiredFiles as $file)
                                <tr class="hover:bg-73E2A7/20 transition-colors duration-200">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 truncate max-w-xs">{{ $file->file_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ strtoupper($file->extension) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $file->humanReadableSize }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $file->updated_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('guide.download', ['media' => $file->id]) }}" 
                                           class="text-1C7C54 hover:text-1B512D transition-colors duration-200" title="Download">
                                            <i class="fa-solid fa-download mr-1"></i>
                                        </a>
                                        @if($this->isPreviewable($file->mime_type))
                                        <a href="{{ route('guide.preview', ['media' => $file->id]) }}" 
                                           target="_blank"
                                           class="text-B1CF5F hover:text-1B512D transition-colors duration-200" title="View">
                                            <i class="fa-solid fa-eye mr-1"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No required files attached.</td>
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
        <div class="w-full bg-white rounded-2xl shadow-lg border border-DEF4C6 overflow-hidden h-full">
            <div class="px-6 py-4 bg-gradient-to-r from-1C7C54 to-1B512D font-semibold text-xl">Assigned Users</div>
            <div class="p-2">
                <div class="overflow-hidden rounded-xl border border-DEF4C6">
                    <table class="min-w-full divide-y divide-DEF4C6">
                        <thead class="bg-DEF4C6">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-1B512D uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-1B512D uppercase">Email</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($assignedUsers as $user)
                                <tr class="hover:bg-73E2A7/20 transition cursor-pointer" wire:click='showUser({{ $user->id }})'>
                                    <td class="px-6 py-4 whitespace-nowrap flex items-center">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center bg-73E2A7 text-1B512D font-bold shadow">
                                            {{ substr($user->full_name, 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->department->name ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $user->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No users assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
