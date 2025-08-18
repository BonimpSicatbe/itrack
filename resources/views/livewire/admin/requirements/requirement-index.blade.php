<div class="flex flex-col gap-6 w-full" wire:poll.visible>
    <!-- Add wire:poll.visible to refresh when the tab is visible -->
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        {{-- requirements table list --}}
        <div class="flex flex-col gap-4 w-full h-full">
            {{-- header / actions --}}
            <div class="flex flex-col sm:flex-row flex-wrap items-center justify-between gap-4 w-full">
                <h2 class="text-lg font-semibold w-full sm:w-auto sm:text-left">Requirements List</h2>
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
            </div>

            {{-- body / table --}}
            <div class="max-h-[500px] overflow-x-auto">
                <table class="table table-auto table-striped table-pin-rows table-sm min-w-[600px]">
                    <thead>
                        <tr class="bg-base-300 font-bold uppercase">
                            <th>Name</th>
                            <th class="cursor-pointer hover:bg-gray-100" wire:click="setSort('due')">
                                <div class="flex items-center">
                                    Due Date
                                    <div class="ml-1">
                                        @if($sortBy === 'due')
                                            <i class="fas fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort opacity-30"></i>
                                        @endif
                                    </div>
                                </div>
                            </th>
                            <th>Assigned To</th>
                            <th>Users Assigned</th>
                            <th>Status</th>
                            <th class="cursor-pointer hover:bg-gray-100" wire:click="setSort('created_at')">
                                <div class="flex items-center">
                                    Created At
                                    <div class="ml-1">
                                        @if($sortBy === 'created_at')
                                            <i class="fas fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort opacity-30"></i>
                                        @endif
                                    </div>
                                </div>
                            </th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($requirements as $requirement)
                            <tr>
                                <td class="truncate max-w-[250px]">{{ $requirement->name }}</td>
                                <td class="truncate">{{ \Carbon\Carbon::parse($requirement->due)->format('m/d/Y h:i a') }}</td>
                                <td class="truncate">{{ $requirement->assigned_to }}</td>
                                <td class="truncate">{{ $requirement->assigned_users_count }}</td>
                                <td class="truncate">{{ $requirement->status }}</td>
                                <td class="truncate">
                                    {{ \Carbon\Carbon::parse($requirement->created_at)->format('m/d/Y h:i a') }}
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="{{ route('admin.requirements.show', ['requirement' => $requirement]) }}"
                                        class="btn btn-xs btn-ghost btn-success tooltip"
                                        data-tip="View Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.submitted-requirements.requirement', ['requirement_id' => $requirement->id]) }}"
                                        class="btn btn-xs btn-ghost btn-primary tooltip"
                                        data-tip="View Submissions">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </a>
                                    <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement]) }}"
                                        class="btn btn-xs btn-ghost btn-info tooltip"
                                        data-tip="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <button wire:click="confirmDelete({{ $requirement->id }})" 
                                            class="btn btn-xs btn-ghost btn-error tooltip"
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
    @livewire('admin.requirement-create-modal')

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

