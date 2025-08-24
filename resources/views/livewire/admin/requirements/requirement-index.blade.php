<div class="flex flex-col gap-6 w-[92%] mx-auto" wire:poll.visible>
    <!-- Add wire:poll.visible to refresh when the tab is visible -->
    <div class="w-full bg-white shadow-lg rounded-xl p-4 md:p-6 space-y-4">
        {{-- requirements table list --}}
        <div class="flex flex-col gap-4 w-full h-full">
            {{-- header / actions --}}
            <div class="flex flex-col sm:flex-row flex-wrap items-center justify-between gap-4 w-full">
                <div class="flex items-center gap-3">
                    <div class="pl-3 bg-1C7C54/10 rounded-xl">
                        <i class="fa-solid fa-clipboard-list text-1C7C54 text-2xl"></i>
                    </div>
                    <h2 class="text-xl md:text-xl font-semibold">Requirements List</h2>
                </div>
                
                @if($activeSemester)
                    <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3 w-full sm:w-auto">
                        <div class="relative w-full sm:w-auto">
                            <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" wire:model.live="search" id="search" 
                                   class="pl-9 input input-bordered input-sm w-full sm:w-48 rounded-xl focus:ring-1B512D focus:border-1B512D border-gray-300"
                                placeholder="Search requirements...">
                        </div>
                        
                        {{-- Completion Filter Buttons --}}
                        <div class="join rounded-xl overflow-hidden shadow-sm">
                            <button 
                                class="btn btn-sm join-item rounded-bl-xl rounded-tl-xl {{ $completionFilter === 'all' ? 'btn-active bg-1C7C54 text-white border-1C7C54' : 'bg-white text-gray-700 border-gray-200 hover:bg-DEF4C6' }}"
                                wire:click="$set('completionFilter', 'all')">
                                All
                            </button>
                            <button 
                                class="btn btn-sm join-item {{ $completionFilter === 'pending' ? 'btn-active bg-B1CF5F text-white border-B1CF5F' : 'bg-white text-gray-700 border-gray-200 hover:bg-DEF4C6' }}"
                                wire:click="$set('completionFilter', 'pending')">
                                Pending
                            </button>
                            <button 
                                class="btn btn-sm join-item rounded-tr-xl rounded-br-xl {{ $completionFilter === 'completed' ? 'btn-active bg-1B512D text-white border-1B512D' : 'bg-white text-gray-700 border-gray-200 hover:bg-DEF4C6' }}"
                                wire:click="$set('completionFilter', 'completed')">
                                Completed
                            </button>
                        </div>
                        
                        <label for="createRequirement" class="btn btn-sm bg-1C7C54 text-white hover:bg-1B512D flex items-center gap-2 border-0 rounded-full shadow-md px-4">
                            <i class="fa-solid fa-plus"></i>
                            <span class="hidden sm:inline">Create</span>
                        </label>
                    </div>
                @endif
            </div>

            {{-- body / table --}}
            @if($activeSemester)
                <div class="rounded-xl border border-gray-200 overflow-hidden shadow-sm"> <!-- Added border -->
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
                                                <p class="text-sm mt-2">Try adjusting your search term</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- Loading State --}}
                @if($requirements->isEmpty() && !$search)
                    <div class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-1C7C54"></div>
                    </div>
                @endif
                
                {{-- Pagination --}}
                @if($requirements->hasPages())
                <div class="mt-4">
                    {{ $requirements->links() }}
                </div>
                @endif
            @else
                <div class="alert bg-DEF4C6 text-1B512D border border-B1CF5F rounded-xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-1C7C54 text-xl"></i>
                        <span>No active semester. Please activate a semester to view and manage requirements.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-xl border border-1C7C54">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-red-100 rounded-xl">
                        <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-1B512D">Confirm Deletion</h3>
                </div>
                <p class="mb-6 text-gray-700 ml-2">Are you sure you want to delete this requirement? This action cannot be undone.</p>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)" 
                            class="btn bg-gray-200 text-gray-800 hover:bg-gray-300 border-0 rounded-full">
                        Cancel
                    </button>
                    <button wire:click="deleteRequirement" 
                            class="btn bg-red-600 text-white hover:bg-red-700 border-0 rounded-full"
                            wire:loading.attr="disabled"
                            wire:target="deleteRequirement">
                        <span wire:loading.remove wire:target="deleteRequirement">Delete</span>
                        <span wire:loading wire:target="deleteRequirement">
                            <i class="fa-solid fa-spinner fa-spin"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
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