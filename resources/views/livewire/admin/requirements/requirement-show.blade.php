<div class="flex flex-col lg:flex-row gap-6 w-[92%] mx-auto">
    <div class="w-full bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">

        <!-- Header (Fixed) -->
        <div class="flex items-center justify-between px-6 py-4 sticky top-0 z-10"
             style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="text-2xl fa-solid fa-circle-info"></i> Requirement Details
            </h2>
            <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement->id]) }}"
               class="bg-white text-1C7C54 px-4 py-1.5 rounded-full shadow hover:bg-73E2A7 hover:text-1B512D font-semibold text-sm transition-all duration-200 flex items-center gap-2">
                <i class="fa-solid fa-pencil"></i> Edit
            </a>
        </div>

        <!-- Body (Scrollable) -->
        <div class="border-b border-gray-100 max-h-[625px] overflow-y-auto">

            <!-- Details -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <span class="block text-sm font-semibold uppercase tracking-wide text-gray-800">Name</span>
                    <p class="text-base text-gray-500">{{ $requirement->name }}</p>
                </div>
                <div>
                    <span class="block text-sm font-semibold uppercase tracking-wide text-gray-800">Due Date</span>
                    <p class="text-sm text-gray-500">{{ $requirement->due->format('M d, Y h:i A') }}</p>
                </div>
                <div class="md:col-span-2">
                    <span class="block text-sm font-semibold uppercase tracking-wide text-gray-800">Description</span>
                    <p class="text-sm text-gray-500">{{ $requirement->description ?? 'No description provided.' }}</p>
                </div>
                <div>
                    <span class="block text-sm font-semibold uppercase tracking-wide text-gray-800">Assigned To</span>
                    <p class="text-sm text-gray-500">{{ $requirement->assigned_to }}</p>
                </div>
                <div>
                    <span class="block text-sm font-semibold uppercase tracking-wide text-gray-800">Priority</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold shadow-sm
                        @if ($requirement->priority === 'low') bg-B1CF5F text-1B512D
                        @elseif($requirement->priority === 'normal') bg-73E2A7 text-1C7C54
                        @elseif($requirement->priority === 'high') bg-red-100 text-red-800
                        @else bg-gray-200 text-gray-800 @endif">
                        {{ ucfirst($requirement->priority) }}
                    </span>
                </div>
            </div>

            <!-- Required Files -->
            <div class="px-6 py-3 bg-gray-100 border-t border-gray-100 flex items-center gap-2">
                <i class="text-xl fa-solid fa-folder-open text-1C7C54"></i>
                <h3 class="text-lg font-semibold text-gray-800">Required Files</h3>
            </div>

            <div class="p-4">
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-800 uppercase">File Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-800 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-800 uppercase">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-800 uppercase">Modified</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-800 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($requiredFiles as $file)
                                <tr class="hover:bg-73E2A7/10 transition duration-200">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 truncate">{{ $file->file_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ strtoupper($file->extension) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $file->humanReadableSize }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $file->updated_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                        <a href="{{ route('guide.download', ['media' => $file->id]) }}" class="text-1C7C54 hover:text-1B512D transition">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        @if($this->isPreviewable($file->mime_type))
                                        <a href="{{ route('guide.preview', ['media' => $file->id]) }}" target="_blank" class="text-B1CF5F hover:text-1B512D transition">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-xs text-gray-500">No required files attached.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Assigned Users -->
            <div class="px-6 py-3 bg-gray-100 border-t border-gray-100 flex items-center gap-2">
                <i class="fa-solid text-xl fa-users text-1C7C54"></i>
                <h3 class="font-semibold text-lg text-gray-800">Assigned Users</h3>
            </div>
            
            <div class="p-4">
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-100">
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($assignedUsers as $user)
                                <tr class="hover:bg-73E2A7/10 transition cursor-pointer" wire:click='showUser({{ $user->id }})'>
                                    <td class="px-6 py-4 flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center bg-73E2A7 text-1B512D font-bold shadow">
                                            {{ substr($user->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->department->name ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $user->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-center text-xs text-gray-500">No users assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
