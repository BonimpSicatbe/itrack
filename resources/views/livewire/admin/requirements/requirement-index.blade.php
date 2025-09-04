<div class="flex flex-col gap-3 w-[92%] mx-auto" wire:poll.visible>
    <!-- Add wire:poll.visible to refresh when the tab is visible -->
    
    <!-- New Header (copied from submitted-requirements-index) -->
    <div class="flex justify-between items-center text-white p-4 rounded-xl shadow-md" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="flex items-center gap-3">
            <div class="pl-3 bg-1C7C54/10 rounded-xl">
                <i class="fa-solid fa-clipboard-list text-white text-2xl"></i>
            </div>
            <h2 class="text-xl md:text-xl font-semibold">Requirements List</h2>
        </div>

        <!-- Always show view toggle buttons -->
        <div class="flex items-center gap-1 bg-white/20 p-1 rounded-xl">
            <!-- List Toggle -->
            <button 
                wire:click="changeViewMode('list')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }} cursor-pointer"
                title="List view"
            >
                <i class="fas fa-list"></i>
            </button>
            <!-- Grid Toggle -->
            <button 
                wire:click="changeViewMode('grid')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }} cursor-pointer"
                title="Grid view"
            >
                <i class="fas fa-th"></i>
            </button>
        </div>
    </div>

    <div class="w-full bg-white shadow-lg rounded-xl p-4 md:p-6 space-y-4">
        {{-- requirements table list --}}
        <div class="flex flex-col gap-4 w-full h-full">
            {{-- header / actions --}}
            @if($activeSemester)
                <div class="p-1 rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Search -->
                    <div class="relative max-w-md w-full md:w-[300px]">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-sm text-gray-500"></i>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live="search"
                            class="pl-10 block w-sm rounded-xl text-gray-500 border-gray-300 shadow-sm focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm" 
                            placeholder="Search requirements..."
                        >
                    </div>

                    <!-- Right side container with filters and create button -->
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <!-- Completion Filter Buttons -->
                        <div class="flex items-center gap-1 bg-white/80 p-1 rounded-xl font-semibold border border-gray-300 shadow-sm">
                            <button 
                                class="px-4 py-1.5 text-sm rounded-lg transition-colors {{ $completionFilter === 'all' ? 'bg-1C7C54 text-white shadow-sm' : 'hover:bg-1C7C54/20 text-1C7C54' }} cursor-pointer"
                                wire:click="$set('completionFilter', 'all')">
                                All
                            </button>
                            <button 
                                class="px-4 py-1.5 text-sm rounded-lg transition-colors {{ $completionFilter === 'pending' ? 'bg-B1CF5F text-white shadow-sm' : 'hover:bg-B1CF5F/20 text-B1CF5F' }} cursor-pointer"
                                wire:click="$set('completionFilter', 'pending')">
                                Pending
                            </button>
                            <button 
                                class="px-4 py-1.5 text-sm rounded-lg transition-colors {{ $completionFilter === 'completed' ? 'bg-1B512D text-white shadow-sm' : 'hover:bg-1B512D/20 text-1B512D' }} cursor-pointer"
                                wire:click="$set('completionFilter', 'completed')">
                                Completed
                            </button>
                        </div>

                        <!-- Create Button -->
                        <label for="createRequirement" class="btn bg-1C7C54 text-white text-sm hover:bg-1B512D flex items-center gap-2 border-0 rounded-full shadow-md px-4 py-1.5">
                            <i class="fa-solid fa-plus"></i>
                            <span>Create Requirement</span>
                        </label>
                    </div>
                </div>
            @endif

            {{-- body / table --}}
            @if($activeSemester)
                <!-- Show different views based on viewMode -->
                @if(($viewMode ?? 'list') === 'grid')
                    <!-- Grid View -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-3">
                        @forelse ($requirements as $requirement)
                            <div class="bg-white rounded-2xl shadow-md hover:shadow-lg transition p-6 flex flex-col gap-3 cursor-pointer"
                                 onclick="window.location.href='{{ route('admin.requirements.show', ['requirement' => $requirement]) }}'">
                                <!-- Requirement Name -->
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-clipboard-list text-1C7C54 text-xl"></i>
                                    <h3 class="font-semibold text-1B512D truncate">{{ $requirement->name }}</h3>
                                </div>
                                
                                <!-- Due Date -->
                                <div class="flex items-center gap-1 text-sm">
                                    @if(\Carbon\Carbon::parse($requirement->due)->isPast() && !$requirement->is_completed)
                                        <i class="fa-solid fa-circle-exclamation text-red-500 text-xs"></i>
                                    @endif
                                    <span class="{{ \Carbon\Carbon::parse($requirement->due)->isPast() && !$requirement->is_completed ? 'text-red-600 font-medium' : '' }}">
                                        Due: {{ \Carbon\Carbon::parse($requirement->due)->format('m/d/Y h:i a') }}
                                    </span>
                                </div>
                                
                                <!-- Assigned To -->
                                <p class="text-sm text-gray-600 line-clamp-1">
                                    Assigned to: {{ $requirement->assigned_to }}
                                </p>
                                
                                <!-- Users Count -->
                                <div class="flex items-center justify-between mt-2">
                                    <span class="flex items-center justify-center gap-1 w-auto p-3 h-7 rounded-full bg-gray-100 text-gray-800 font-semibold text-sm">
                                        <span>{{ $requirement->assigned_users_count }}</span>
                                        <span>users</span>
                                    </span>

                                    
                                    <!-- Quick Actions -->
                                    <div class="flex gap-2" onclick="event.stopPropagation()">
                                        <!-- View Submissions -->
                                        <a href="{{ route('admin.submitted-requirements.requirement', ['requirement_id' => $requirement->id]) }}"
                                            class="text-lg hover:bg-blue-100 rounded-lg p-2 tooltip"
                                            data-tip="View Submissions">
                                            <i class="fa-solid fa-file-lines text-blue-500"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement]) }}"
                                            class="text-lg hover:bg-amber-100 rounded-lg p-2 tooltip"
                                            data-tip="Edit">
                                            <i class="fa-solid fa-edit text-amber-500"></i>
                                        </a>

                                        <!-- Delete -->
                                        <button wire:click="confirmDelete({{ $requirement->id }})"
                                            class="text-lg hover:bg-red-100 rounded-lg p-2 tooltip"
                                            data-tip="Delete">
                                            <i class="fa-solid fa-trash text-red-600"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center text-gray-500 col-span-4">
                                <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-2"></i>
                                <p class="text-xs">No requirements found.</p>
                                @if($search)
                                    <p class="text-sm text-amber-500 mt-2 font-semibold">Try adjusting your search term</p>
                                @endif
                            </div>
                        @endforelse
                    </div>
                @else
                    <!-- List View (Default) -->
                    <div class="rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="table table-auto table-pin-rows table-sm w-full"> 
                                <thead>
                                    <tr class="bg-1C7C54 font-bold uppercase text-white">
                                        <th class="p-3 md:p-4 cursor-pointer hover:bg-1B512D/90 transition-colors first:rounded-tl-xl" wire:click="sortBy('name')">
                                            <div class="flex items-center">
                                                <span>Name</span>
                                                <div class="ml-1">
                                                    @if($sortField === 'name')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-73E2A7"></i>
                                                    @else
                                                        <i class="fas fa-sort opacity-70"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </th>
                                        <th class="p-3 md:p-4 cursor-pointer hover:bg-1B512D/90 transition-colors" wire:click="sortBy('due')">
                                            <div class="flex items-center">
                                                <span class="hidden sm:inline">Due Date</span>
                                                <span class="sm:hidden">Due</span>
                                                <div class="ml-1">
                                                    @if($sortField === 'due')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-73E2A7"></i>
                                                    @else
                                                        <i class="fas fa-sort opacity-70"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </th>
                                        <th class="p-3 md:p-4 cursor-pointer hover:bg-1B512D/90 transition-colors" wire:click="sortBy('assigned_to')">
                                            <div class="flex items-center">
                                                <span class="hidden md:inline">Assigned To</span>
                                                <span class="md:hidden">Assigned</span>
                                                <div class="ml-1">
                                                    @if($sortField === 'assigned_to')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-73E2A7"></i>
                                                    @else
                                                        <i class="fas fa-sort opacity-70"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </th>
                                        <th class="p-3 md:p-4 text-center">
                                            <span>Users</span>
                                        </th>
                                        <th class="p-3 md:p-4 cursor-pointer hover:bg-1B512D/90 transition-colors hidden md:table-cell" wire:click="sortBy('created_at')">
                                            <div class="flex items-center">
                                                <span>Created</span>
                                                <div class="ml-1">
                                                    @if($sortField === 'created_at')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-73E2A7"></i>
                                                    @else
                                                        <i class="fas fa-sort opacity-70"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </th>
                                        <th class="text-center p-3 md:p-4 last:rounded-tr-xl">
                                            <span>Action</span>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($requirements as $requirement)
                                        <tr class="hover:bg-DEF4C6/50 transition-colors duration-150 cursor-pointer even:bg-gray-50"
                                            onclick="window.location.href='{{ route('admin.requirements.show', ['requirement' => $requirement]) }}'">
                                            <td class="font-medium p-3 md:p-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="truncate max-w-[120px] md:max-w-none">{{ $requirement->name }}</span>
                                                </div>
                                            </td>
                                            <td class="p-3 md:p-4">
                                                <div class="flex items-center gap-1">
                                                    @if(\Carbon\Carbon::parse($requirement->due)->isPast() && !$requirement->is_completed)
                                                        <i class="fa-solid fa-circle-exclamation text-red-500 text-xs"></i>
                                                    @endif
                                                    <span class="{{ \Carbon\Carbon::parse($requirement->due)->isPast() && !$requirement->is_completed ? 'text-red-600 font-medium' : '' }} text-nowrap">
                                                        {{ \Carbon\Carbon::parse($requirement->due)->format('m/d/Y') }}
                                                        <span class="hidden md:inline">{{ \Carbon\Carbon::parse($requirement->due)->format('h:i a') }}</span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="whitespace-normal min-w-[100px] max-w-[200px] p-3 md:p-4">
                                                <span class="line-clamp-1">{{ $requirement->assigned_to }}</span>
                                            </td>
                                            <td class="p-3 md:p-4 text-center">
                                                <span class="inline-flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full bg-1C7C54/10 text-1C7C54 font-semibold">
                                                    {{ $requirement->assigned_users_count }}
                                                </span>
                                            </td>
                                            <td class="p-3 md:p-4 hidden md:table-cell">
                                                {{ \Carbon\Carbon::parse($requirement->created_at)->format('m/d/Y h:i a') }}
                                            </td>
                                            <td class="flex justify-center p-3 relative z-10" onclick="event.stopPropagation()">
                                                <!-- View -->
                                                <a href="{{ route('admin.submitted-requirements.requirement', ['requirement_id' => $requirement->id]) }}"
                                                    class="text-lg hover:bg-blue-100 rounded-lg p-2 tooltip"
                                                    data-tip="View Submissions">
                                                    <i class="fa-solid fa-file-lines text-blue-500"></i>
                                                </a>

                                                <!-- Edit -->
                                                <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement]) }}"
                                                    class="text-lg hover:bg-amber-100 rounded-lg p-2 tooltip"
                                                    data-tip="Edit">
                                                    <i class="fa-solid fa-edit text-amber-500"></i>
                                                </a>

                                                <!-- Delete -->
                                                <button wire:click="confirmDelete({{ $requirement->id }})"
                                                    class="text-lg hover:bg-red-100 rounded-lg p-2 tooltip"
                                                    data-tip="Delete">
                                                    <i class="fa-solid fa-trash text-red-600"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center p-8 text-gray-500">
                                                <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-2"></i>
                                                <p>No requirements found.</p>
                                                @if($search)
                                                    <p class="text-sm mt-2 text-amber-500 font-semibold">Try adjusting your search term</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                
                {{-- Pagination --}}
                @if($requirements->hasPages())
                <div class="mt-4">
                    {{ $requirements->links() }}
                </div>
                @endif
            @else
                <div class="alert bg-[#DEF4C6] text-[#1B512D] rounded-lg shadow-lg">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <div>
                        <h3 class="font-bold">No Active Semester</h3>
                        <div class="text-xs">Please activate a semester to view requirements.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Confirmation Modal using the modal component --}}
    @if($showDeleteModal)
        <x-modal name="delete-requirement-confirmation-modal" :show="$showDeleteModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-red-600 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete the requirement 
                        @if($requirementToDelete)
                            <span class="font-semibold text-red-600">"{{ \App\Models\Requirement::find($requirementToDelete)->name ?? 'this requirement' }}"</span>?
                        @else
                            <span class="font-semibold text-red-600">this requirement</span>?
                        @endif
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. All data associated with this requirement will be permanently removed.
                    </p>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="bg-white py-2 px-4 border border-gray-300 rounded-full shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteRequirement" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 cursor-pointer"
                            wire:loading.attr="disabled"
                            wire:target="deleteRequirement">
                        <span wire:loading.remove wire:target="deleteRequirement">
                            <i class="fa-solid fa-trash mr-2"></i> Delete Requirement
                        </span>
                        <span wire:loading wire:target="deleteRequirement">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    {{-- Include the create modal component --}}
    @if($activeSemester)
        @livewire('admin.requirement-create-modal')
    @endif

    <style>
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        @media (max-width: 768px) {
            .table th, .table td {
                padding: 0.5rem;
            }
        }
    </style>

    @push('scripts')
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipElements = document.querySelectorAll('.tooltip');
            tooltipElements.forEach(el => {
                new bootstrap.Tooltip(el);
            });
        });
        
        // Add responsive behavior
        function handleResize() {
            const tables = document.querySelectorAll('table');
            tables.forEach(table => {
                if (window.innerWidth < 768) {
                    table.classList.add('text-sm');
                } else {
                    table.classList.remove('text-sm');
                }
            });
        }
        
        // Initial call and event listener
        handleResize();
        window.addEventListener('resize', handleResize);
    </script>
    @endpush
</div>