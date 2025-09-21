<div class="flex flex-col lg:flex-row gap-6">
    <div class="w-full bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">

        <!-- Header (Fixed) -->
        <div class="flex items-center justify-between px-6 py-4 sticky top-0 z-10"
             style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="text-2xl fa-solid fa-circle-info"></i> Requirement Details
            </h2>
            <a href="{{ route('admin.requirements.edit', $requirement->id) }}"
               class="bg-white text-green-700 px-4 py-1.5 rounded-full shadow font-semibold text-sm transition-all duration-200 flex items-center gap-2">
                <i class="fa-solid fa-pencil text-green-700"></i> Edit
            </a>
        </div>

        <!-- Body (Scrollable) -->
        <div class="border-b border-gray-100 max-h-[625px] overflow-y-auto">

            <!-- Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 pb-9 w-[90%] mx-auto">
                <!-- Name -->
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Name</p>
                    <div class="bg-gray-50 p-3 rounded-xl text-sm font-semibold text-gray-500 shadow-inner">
                        {{ $requirement->name }}
                    </div>
                </div>

                <!-- Due Date -->
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Due Date</p>
                    <div class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-500 shadow-inner font-semibold">
                        <i class="fa-regular fa-calendar mr-2 text-gray-500"></i>
                        {{ $requirement->due->format('M d, Y g:i A') }}
                        @if($requirement->due->isPast())
                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                Overdue
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-2 md:col-span-2">
                    <p class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Description</p>
                    <div class="bg-gray-50 p-3 rounded-xl text-sm text-gray-500 leading-relaxed shadow-inner font-semibold">
                        {{ $requirement->description ?? 'No description provided.' }}
                    </div>
                </div>

                <!-- Assigned To -->
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Assigned To</p>
                    <div class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-500 shadow-inner font-semibold">
                        <i class="fa-regular fa-user mr-2 text-gray-500"></i>
                        {{ $requirement->assigned_to }}
                    </div>
                </div>

                <!-- Priority -->
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Priority</p>
                    <div class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-500 font-semibold capitalize shadow-inner">
                        <i class="fa-solid fa-flag mr-2 text-gray-500"></i>
                        {{ ucfirst($requirement->priority) }}
                    </div>
                </div>
            </div>

            <!-- Required Files -->
            <div class="px-6 py-3 bg-gray-100 border-t border-gray-100 flex items-center gap-2">
                <i class="text-xl fa-solid fa-folder-open text-green-700"></i>
                <h3 class="text-lg font-semibold text-gray-800">Required Files</h3>
            </div>

            <div class="pb-10">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-green-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">File Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Modified</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($requiredFiles as $file)
                                <tr class="hover:bg-73E2A7/10 transition duration-200">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 truncate max-w-xs border-b border-gray-300">{{ $file->file_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border-b border-gray-300">{{ strtoupper($file->extension) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border-b border-gray-300">{{ $file->humanReadableSize }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border-b border-gray-300">{{ $file->updated_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium space-x-3 border-b border-gray-300">
                                        <a href="{{ route('guide.download', $file->id) }}" class="text-blue-500">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        @if($this->isPreviewable($file->mime_type))
                                        <a href="{{ route('guide.preview', $file->id) }}" target="_blank" class="text-green-500 hover:text-1B512D transition">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-xs text-gray-500 border-b border-gray-300">No required files attached.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Assigned Users -->
            <div class="px-6 py-3 bg-gray-100 border-t border-gray-100 flex items-center gap-2">
                <i class="fa-solid text-xl fa-users text-green-700"></i>
                <h3 class="font-semibold text-lg text-gray-800">Assigned Users</h3>
            </div>
            
            <div class="pb-10">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-green-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Email</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($assignedUsers as $user)
                                <tr wire:click="showUser({{ $user->id }})">
                                    <td class="px-6 py-3 flex items-center gap-3 border-b border-gray-300">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center bg-gradient-to-br from-green-400 to-green-600 text-white font-bold shadow">
                                            {{ substr($user->full_name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500 truncate">{{ $user->department->name ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-700 truncate border-b border-gray-300">{{ $user->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-3 text-center text-xs text-gray-500">No users assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>