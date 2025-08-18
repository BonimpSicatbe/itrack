<div class="flex flex-col gap-4 w-full">
    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mx-auto mb-4">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-center mb-4">Edit Semester</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="modal-name" class="block text-sm font-medium text-gray-700 mb-1">Semester Name</label>
                        <input type="text" wire:model="name" id="modal-name" class="input input-bordered w-full">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="modal-start-date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" wire:model="start_date" id="modal-start-date" class="input input-bordered w-full">
                            @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="modal-end-date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" wire:model="end_date" id="modal-end-date" class="input input-bordered w-full">
                            @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="is_active" id="modal-is-active" class="checkbox">
                        <label for="modal-is-active" class="ml-2 block text-sm text-gray-700">Set as active semester</label>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="closeModal" 
                            class="btn btn-ghost hover:bg-gray-100">
                        Cancel
                    </button>
                    <button wire:click="save" 
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="save">
                        <span wire:loading.remove wire:target="save">Update</span>
                        <span wire:loading wire:target="save">
                            <i class="fa-solid fa-spinner fa-spin"></i> Updating...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
                <h3 class="text-lg font-bold mb-4">Confirm Deletion</h3>
                <p class="mb-6">Are you sure you want to delete this semester? This action cannot be undone.</p>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="closeModal" 
                            class="btn btn-ghost hover:bg-gray-100">
                        Cancel
                    </button>
                    <button wire:click="deleteSemester" 
                            class="btn btn-error"
                            wire:loading.attr="disabled"
                            wire:target="deleteSemester">
                        <span wire:loading.remove wire:target="deleteSemester">Delete</span>
                        <span wire:loading wire:target="deleteSemester">
                            <i class="fa-solid fa-spinner fa-spin"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="flex flex-col gap-4 w-full bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800">Academic Semester Management</h2>

        <!-- Semester Form -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Semester Name</label>
                        <input type="text" wire:model="name" id="name" class="input input-bordered w-full">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" wire:model="start_date" id="start_date" class="input input-bordered w-full">
                        @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" wire:model="end_date" id="end_date" class="input input-bordered w-full">
                        @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="is_active" id="is_active" class="checkbox">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">Set as active semester</label>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" wire:click="resetForm" class="btn btn-ghost">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $editMode ? 'Update Semester' : 'Create Semester' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Semesters List -->
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="table table-auto w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($semesters as $semester)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $semester->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $semester->start_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $semester->end_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($semester->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <button wire:click="setActive({{ $semester->id }})" class="badge badge-ghost hover:bg-gray-200" title="Set as active">
                                        Inactive
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    <button wire:click="edit({{ $semester->id }})" 
                                            class="btn btn-xs btn-ghost btn-info tooltip" data-tip="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $semester->id }})" 
                                            class="btn btn-xs btn-ghost btn-error tooltip" data-tip="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No semesters found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

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