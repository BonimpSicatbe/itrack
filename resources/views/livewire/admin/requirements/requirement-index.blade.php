<div class="flex flex-col gap-6 w-full" wire:poll.visible>
    <!-- Add wire:poll.visible to refresh when the tab is visible -->
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        {{-- requirements table list --}}
        <div class="flex flex-col gap-4 w-full h-full">
            {{-- header / actions --}}
            <div class="flex flex-col sm:flex-row flex-wrap items-center justify-between gap-4 w-full">
                <h2 class="text-xl font-semibold w-full sm:w-auto sm:text-left">Requirements List</h2>
                
                @if($activeSemester)
                    <div class="flex flex-row gap-4 w-full sm:w-auto justify-center sm:justify-end">
                        <input type="text" wire:model.live="search" id="search" class="input input-bordered input-sm w-full sm:w-sm"
                            placeholder="Search requirements...">
                        
                        {{-- Completion Filter Buttons --}}
                        <div class="join">
                            <button 
                                class="btn btn-sm join-item {{ $completionFilter === 'all' ? 'btn-active' : '' }}"
                                wire:click="$set('completionFilter', 'all')">
                                All
                            </button>
                            <button 
                                class="btn btn-sm join-item {{ $completionFilter === 'pending' ? 'btn-active' : '' }}"
                                wire:click="$set('completionFilter', 'pending')">
                                Pending
                            </button>
                            <button 
                                class="btn btn-sm join-item {{ $completionFilter === 'completed' ? 'btn-active' : '' }}"
                                wire:click="$set('completionFilter', 'completed')">
                                Completed
                            </button>
                        </div>
                        
                        <label for="createRequirement" class="btn btn-sm btn-success flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i>
                            <span class="hidden sm:inline">Create Requirement</span>
                        </label>
                    </div>
                @endif
            </div>

            {{-- body / table --}}
            @if($activeSemester)
                <div class="max-h-[500px] overflow-x-auto rounded-lg"> <!-- Added rounded-lg here -->
                    <table class="table table-auto table-striped table-pin-rows table-sm min-w-[600px] rounded-lg"> 
                        <thead>
                            <tr class="bg-base-300 font-bold uppercase">
                                <th class=" cursor-pointer hover:bg-gray-100 p-4" wire:click="sortBy('name')" style="background-color: #6a994e; color: white;">
                                    <div class="flex items-center pt-2 pb-2">
                                        Name
                                        <div class="ml-1">
                                            @if($sortField === 'name')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort opacity-30"></i>
                                            @endif
                                        </div>
                                    </div>
                                </th>
                                <th class="cursor-pointer hover:bg-gray-100 p-4" wire:click="sortBy('due')" style="background-color: #6a994e; color: white;">
                                    <div class="flex items-center">
                                        Due Date
                                        <div class="ml-1">
                                            @if($sortField === 'due')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort opacity-30"></i>
                                            @endif
                                        </div>
                                    </div>
                                </th>
                                <th class="cursor-pointer hover:bg-gray-100 p-4" wire:click="sortBy('assigned_to')" style="background-color: #6a994e; color: white;">
                                    <div class="flex items-center">
                                        Assigned To
                                        <div class="ml-1">
                                            @if($sortField === 'assigned_to')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort opacity-30"></i>
                                            @endif
                                        </div>
                                    </div>
                                </th>
                                <th class="p-4" style="background-color: #6a994e; color: white;">Users Assigned</th>
                                <th class="cursor-pointer hover:bg-gray-100 p-4" wire:click="sortBy('created_at')" style="background-color: #6a994e; color: white;">
                                    <div class="flex items-center">
                                        Created At
                                        <div class="ml-1">
                                            @if($sortField === 'created_at')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort opacity-30"></i>
                                            @endif
                                        </div>
                                    </div>
                                </th>
                                <th class="text-center p-4" style="background-color: #6a994e; color: white;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($requirements as $requirement)
                                <tr class="hover:bg-blue-50 transition-colors duration-150 cursor-pointer"
                                    onclick="window.location.href='{{ route('admin.requirements.show', ['requirement' => $requirement]) }}'">
                                    <td class="font-medium">{{ $requirement->name }}</td>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            @if(\Carbon\Carbon::parse($requirement->due)->isPast())
                                                <i class="fa-solid fa-circle-exclamation text-red-500 text-xs"></i>
                                            @endif
                                            <span class="{{ \Carbon\Carbon::parse($requirement->due)->isPast() ? 'text-red-600 font-medium' : '' }}">
                                                {{ \Carbon\Carbon::parse($requirement->due)->format('m/d/Y h:i a') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-normal min-w-[150px] max-w-[300px]">
                                        {{ $requirement->assigned_to }}
                                    </td>
                                    <td>{{ $requirement->assigned_users_count }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($requirement->created_at)->format('m/d/Y h:i a') }}
                                    </td>
                                    <td class="flex justify-center gap-2 relative z-10" onclick="event.stopPropagation()">
                                        <a href="{{ route('admin.submitted-requirements.requirement', ['requirement_id' => $requirement->id]) }}"
                                            class="btn btn-xs btn-ghost btn-primary tooltip tooltip-primary"
                                            data-tip="View Submissions">
                                            <i class="fa-solid fa-file-lines"></i>
                                        </a>
                                        <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement]) }}"
                                            class="btn btn-xs btn-ghost btn-info tooltip tooltip-info"
                                            data-tip="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <button wire:click="confirmDelete({{ $requirement->id }})" 
                                                class="btn btn-xs btn-ghost btn-error tooltip tooltip-error"
                                                data-tip="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No requirements found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>No active semester. Please activate a semester to view and manage requirements.</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50"> <!-- Changed bg-opacity-50 to bg-black/30 -->
            <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl"> <!-- Added shadow -->
                <h3 class="text-lg font-bold mb-4">Confirm Deletion</h3>
                <p class="mb-6">Are you sure you want to delete this requirement? This action cannot be undone.</p>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)" 
                            class="btn btn-ghost hover:bg-gray-100"> <!-- Added hover effect -->
                        Cancel
                    </button>
                    <button wire:click="deleteRequirement" 
                            class="btn btn-error"
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

    @push('scripts')
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipElements = document.querySelectorAll('.tooltip');
            tooltipElements.forEach(el => {
                new bootstrap.Tooltip(el);
            });
        });
    </script>
    @endpush
</div>