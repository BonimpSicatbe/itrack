<div class="flex flex-col gap-3 w-[92%] mx-auto" wire:poll.visible>
    <!-- Header -->
    <div class="flex justify-between items-center text-white p-4 rounded-xl shadow-md" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="flex items-center gap-3">
            <div class="pl-3 ">
                <i class="fa-solid fa-clipboard-list text-white text-2xl"></i>
            </div>
            <h2 class="text-xl md:text-xl font-semibold">Requirements List</h2>
        </div>

        <!-- View toggle buttons -->
        <div class="flex items-center gap-1 bg-white/20 p-1 rounded-xl">
            <button 
                wire:click="changeViewMode('list')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-white text-green-600 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                title="List view"
            >
                <i class="fas fa-list"></i>
            </button>
            <button 
                wire:click="changeViewMode('grid')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-white text-green-600 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                title="Grid view"
            >
                <i class="fas fa-th"></i>
            </button>
        </div>
    </div>

    <div class="w-full bg-white shadow-lg rounded-xl p-4 md:p-6 space-y-4">
        <div class="flex flex-col gap-4 w-full h-full">
            @if($activeSemester)
                <div class="p-1 rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Search -->
                    <div class="relative max-w-md w-full md:w-[300px]">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-sm text-gray-500"></i>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            class="pl-10 block w-sm rounded-xl text-gray-500 border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm" 
                            placeholder="Search requirements..."
                        >
                    </div>

                    <!-- Filters and create button -->
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <!-- Completion Filter -->
                        <div class="flex items-center gap-1 bg-white/80 p-1 rounded-xl font-semibold border border-gray-300 shadow-sm">
                            <button 
                                class="px-4 py-1.5 text-sm rounded-lg transition-colors {{ $completionFilter === 'all' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-green-600/20 text-green-600' }}"
                                wire:click="$set('completionFilter', 'all')">
                                All
                            </button>
                            <button 
                                class="px-4 py-1.5 text-sm rounded-lg transition-colors {{ $completionFilter === 'pending' ? 'bg-yellow-500 text-white shadow-sm' : 'hover:bg-yellow-500/20 text-yellow-600' }}"
                                wire:click="$set('completionFilter', 'pending')">
                                Pending
                            </button>
                            <button 
                                class="px-4 py-1.5 text-sm rounded-lg transition-colors {{ $completionFilter === 'completed' ? 'bg-green-800 text-white shadow-sm' : 'hover:bg-green-800/20 text-green-800' }}"
                                wire:click="$set('completionFilter', 'completed')">
                                Completed
                            </button>
                        </div>

                        <!-- Create Button -->
                        <label for="createRequirement" class="btn bg-green-600 hover:bg-green-700 text-white text-sm flex items-center gap-2 border-0 rounded-full shadow-md px-4 py-1.5">
                            <i class="fa-solid fa-plus"></i>
                            <span>Create</span>
                        </label>
                    </div>
                </div>

                <!-- Grid View -->
                @if($viewMode === 'grid')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-3">
                        @forelse ($requirements as $requirement)
                            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-4 flex flex-col gap-2 cursor-pointer group"
                                 onclick="window.location.href='{{ route('admin.requirements.show', $requirement) }}'">
                                <!-- Requirement Name -->
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clipboard-list text-green-600 text-lg"></i>
                                    <h3 class="font-semibold text-green-800 truncate text-sm">{{ $requirement->name }}</h3>
                                </div>
                                
                                <!-- Due Date -->
                                <div class="flex items-center gap-1 text-xs">
                                    @if($requirement->due->isPast() && !$requirement->is_completed)
                                        <i class="fa-solid fa-circle-exclamation text-red-500 text-xs"></i>
                                    @endif
                                    <span class="{{ $requirement->due->isPast() && !$requirement->is_completed ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                        Due: {{ $requirement->due->format('m/d/Y h:i a') }}
                                    </span>
                                </div>
                                
                                <!-- Created At -->
                                <div class="text-xs text-gray-500">
                                    Created: {{ $requirement->created_at->format('m/d/Y') }}
                                </div>
                                
                                <!-- Assigned To -->
                                <p class="text-xs text-gray-600 truncate">
                                    To: {{ $requirement->assigned_to }}
                                </p>
                                
                                <!-- Users Count -->
                                <div class="flex items-center justify-between mt-2">
                                    <span class="inline-flex items-center justify-center gap-1 px-3 py-1 rounded-full bg-gray-100 text-gray-800 font-semibold text-xs">
                                        {{ $requirement->assigned_users_count }} users
                                    </span>

                                    <!-- Quick Actions -->
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                                        <a href="{{ route('admin.submitted-requirements.requirement', $requirement->id) }}"
                                            class="p-1.5 hover:bg-blue-100 rounded-lg text-blue-500 text-sm"
                                            title="View Submissions">
                                            <i class="fa-solid fa-file-lines"></i>
                                        </a>
                                        <a href="{{ route('admin.requirements.edit', $requirement) }}"
                                            class="p-1.5 hover:bg-amber-100 rounded-lg text-amber-500 text-sm"
                                            title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <button wire:click="confirmDelete({{ $requirement->id }})"
                                            class="p-1.5 hover:bg-red-100 rounded-lg text-red-600 text-sm"
                                            title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center text-gray-500 col-span-full py-8">
                                <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-2"></i>
                                <p class="text-sm">No requirements found.</p>
                                @if($search)
                                    <p class="text-xs text-amber-500 mt-1">Try adjusting your search term</p>
                                @endif
                            </div>
                        @endforelse
                    </div>
                @else
                    <!-- List View -->
                    <div class="rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-green-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:bg-green-800 transition-colors"
                                            wire:click="sortBy('name')">
                                            <div class="flex items-center">
                                                <span>Name</span>
                                                @if($sortField === 'name')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-green-300"></i>
                                                @else
                                                    <i class="fas fa-sort ml-1 opacity-70"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:bg-green-800 transition-colors"
                                            wire:click="sortBy('due')">
                                            <div class="flex items-center">
                                                <span>Due Date</span>
                                                @if($sortField === 'due')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-green-300"></i>
                                                @else
                                                    <i class="fas fa-sort ml-1 opacity-70"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:bg-green-800 transition-colors"
                                            wire:click="sortBy('assigned_to')">
                                            <div class="flex items-center">
                                                <span>Assigned To</span>
                                                @if($sortField === 'assigned_to')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-green-300"></i>
                                                @else
                                                    <i class="fas fa-sort ml-1 opacity-70"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                            Users
                                        </th>
                                        <!-- Created At Column -->
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:bg-green-800 transition-colors"
                                            wire:click="sortBy('created_at')">
                                            <div class="flex items-center">
                                                <span>Created At</span>
                                                @if($sortField === 'created_at')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-green-300"></i>
                                                @else
                                                    <i class="fas fa-sort ml-1 opacity-70"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($requirements as $requirement)
                                        <tr class="hover:bg-green-50 transition-colors cursor-pointer"
                                            onclick="window.location.href='{{ route('admin.requirements.show', $requirement) }}'">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-clipboard-list text-green-600 text-sm"></i>
                                                    <span class="text-sm font-medium text-gray-900 truncate max-w-xs">
                                                        {{ $requirement->name }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center gap-1 text-sm">
                                                    @if($requirement->due->isPast() && !$requirement->is_completed)
                                                        <i class="fa-solid fa-circle-exclamation text-red-500 text-xs"></i>
                                                    @endif
                                                    <span class="{{ $requirement->due->isPast() && !$requirement->is_completed ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                                        {{ $requirement->due->format('m/d/Y h:i a') }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-sm text-gray-600 truncate max-w-xs">
                                                    {{ $requirement->assigned_to }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-800 font-semibold text-sm">
                                                    {{ $requirement->assigned_users_count }}
                                                </span>
                                            </td>
                                            <!-- Created At Data -->
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-sm text-gray-600">
                                                    {{ $requirement->created_at->format('m/d/Y h:i a') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center" onclick="event.stopPropagation()">
                                                <div class="flex justify-center gap-1">
                                                    <a href="{{ route('admin.submitted-requirements.requirement', $requirement->id) }}"
                                                       class="p-2 hover:bg-blue-100 rounded-lg text-blue-500 text-sm"
                                                       title="View Submissions">
                                                        <i class="fa-solid fa-file-lines"></i>
                                                    </a>
                                                    <a href="{{ route('admin.requirements.edit', $requirement) }}"
                                                       class="p-2 hover:bg-amber-100 rounded-lg text-amber-500 text-sm"
                                                       title="Edit">
                                                        <i class="fa-solid fa-edit"></i>
                                                    </a>
                                                    <button wire:click="confirmDelete({{ $requirement->id }})"
                                                            class="p-2 hover:bg-red-100 rounded-lg text-red-600 text-sm"
                                                            title="Delete">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                                <i class="fa-solid fa-inbox text-3xl text-gray-300 mb-2"></i>
                                                <p class="text-sm">No requirements found.</p>
                                                @if($search)
                                                    <p class="text-xs text-amber-500 mt-1">Try adjusting your search term</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                
                <!-- Pagination -->
                @if($requirements->hasPages())
                    <div class="mt-4">
                        {{ $requirements->onEachSide(1)->links() }}
                    </div>
                @endif
            @else
                <div class="alert bg-green-100 text-green-800 rounded-lg p-4">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <div>
                        <h3 class="font-bold">No Active Semester</h3>
                        <div class="text-sm">Please activate a semester to view requirements.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <x-modal name="delete-requirement-confirmation-modal" :show="$showDeleteModal" maxWidth="md">
            <div class="bg-red-600 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete the requirement 
                        <span class="font-semibold text-red-600">"{{ \App\Models\Requirement::find($requirementToDelete)->name ?? 'this requirement' }}"</span>?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. All data will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)" 
                            class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteRequirement" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteRequirement">
                            <i class="fa-solid fa-trash mr-2"></i> Delete
                        </span>
                        <span wire:loading wire:target="deleteRequirement">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Create Modal -->
    @if($activeSemester)
        @livewire('admin.requirement-create-modal')
    @endif

    <style>
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }
        }
    </style>
</div>