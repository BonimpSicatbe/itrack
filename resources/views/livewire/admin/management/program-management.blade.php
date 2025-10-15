{{-- resources/views/livewire/admin/management/program-management.blade.php --}}

<div class="w-full flex">
    <div class="w-full transition-all duration-300 ease-in-out">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-4 px-6 pt-6">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-xl font-semibold text-green-700">Program Management</h3>
                    <p class="text-sm text-gray-600">| Manage academic programs and their information.</p>
                </div>
            </div>
            <button 
                wire:click="openAddProgramModal" 
                class="px-5 py-2 bg-green-600 text-white font-semibold rounded-xl text-sm cursor-pointer"
            >
                <i class="fa-solid fa-plus mr-2"></i>Add Program
            </button>
        </div>

        <div class="border-b border-gray-200 mb-4"></div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4 px-6">
            
            <div class="flex items-center gap-2 bg-green-50 border border-green-600 px-4 py-2 rounded-xl shadow-sm">
                <i class="fa-solid fa-graduation-cap text-green-700"></i>
                <span class="text-sm font-semibold text-green-700">
                    Total Programs: {{ $programs->count() }}
                </span>
            </div>

            <div class="w-full sm:w-1/2">
                <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">Search Programs</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm" 
                        placeholder="Search by code, name, description, or college"
                    >
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 shadow-sm">
            <table class="table table-auto table-striped table-pin-rows table-sm min-w-[1000px] rounded-xl">
                <thead>
                    <tr class="bg-base-300 font-bold uppercase">
                        <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4" wire:click="sortBy('program_code')" style="color: white;">
                            <div class="flex items-center pt-2 pb-2">
                                Program Code
                                <div class="ml-1">
                                    @if($sortField === 'program_code')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4" wire:click="sortBy('program_name')" style="color: white;">
                            <div class="flex items-center pt-2 pb-2">
                                Program Name
                                <div class="ml-1">
                                    @if($sortField === 'program_name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="p-4 bg-green-700" style="color: white;">Description</th>
                        <th class="p-4 bg-green-700" style="color: white;">College</th>
                        <th class="p-4 text-right bg-green-700" style="color: white;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programs as $program)
                        <tr class="hover:bg-green-50">
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $program->program_code }}</div>
                            </td>
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $program->program_name }}</div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate">{{ $program->description ?? 'No description' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $program->college->name ?? 'N/A' }}</div>
                            </td>
                            <td class="whitespace-nowrap text-right text-sm font-medium p-4">
                                <div class="flex justify-end space-x-2 text-base">
                                    <button class="text-amber-500 hover:bg-amber-100 rounded-xl p-2 tooltip cursor-pointer" 
                                            data-tip="Edit" 
                                            wire:click="openEditProgramModal({{ $program->id }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="text-red-500 hover:bg-red-100 rounded-xl p-2 tooltip cursor-pointer" 
                                            data-tip="Delete" 
                                            wire:click="openDeleteConfirmationModal({{ $program->id }})">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-4">No programs found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showAddProgramModal)
        <x-modal name="add-program-modal" :show="$showAddProgramModal" maxWidth="2xl">
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-graduation-cap text-lg"></i>
                <h3 class="text-xl font-semibold">Add New Program</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Program Code *</label>
                        <input type="text" wire:model="newProgram.program_code"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter program code (e.g., BSIT)">
                        @error('newProgram.program_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Program Name *</label>
                        <input type="text" wire:model="newProgram.program_name"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter program name">
                        @error('newProgram.program_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">College *</label>
                        <select wire:model="newProgram.college_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm">
                            <option value="">Select a College</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                        @error('newProgram.college_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Description</label>
                        <textarea wire:model="newProgram.description"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter program description"
                            rows="3"></textarea>
                        @error('newProgram.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" wire:click="closeAddProgramModal"
                        class="px-5 py-2 rounded-xl border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="addProgram" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm shadow cursor-pointer">
                        <span wire:loading.remove wire:target="addProgram">Add Program</span>
                        <span wire:loading wire:target="addProgram">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Adding...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    @if($showEditProgramModal)
        <x-modal name="edit-program-modal" :show="$showEditProgramModal" maxWidth="2xl">
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-graduation-cap text-lg"></i>
                <h3 class="text-xl font-semibold">Edit Program</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Program Code *</label>
                        <input type="text" wire:model="editingProgram.program_code"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter program code">
                        @error('editingProgram.program_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Program Name *</label>
                        <input type="text" wire:model="editingProgram.program_name"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter program name">
                        @error('editingProgram.program_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">College *</label>
                        <select wire:model="editingProgram.college_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm">
                            <option value="">Select a College</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                        @error('editingProgram.college_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Description</label>
                        <textarea wire:model="editingProgram.description"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter program description"
                            rows="3"></textarea>
                        @error('editingProgram.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" wire:click="closeEditProgramModal"
                        class="px-5 py-2 rounded-xl border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="updateProgram" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm shadow cursor-pointer">
                        <span wire:loading.remove wire:target="updateProgram">Update Program</span>
                        <span wire:loading wire:target="updateProgram">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Updating...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    @if($showDeleteConfirmationModal && $programToDelete)
        <x-modal name="delete-program-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete the program 
                        <span class="font-semibold text-red-600">"{{ $programToDelete->program_name }}" ({{ $programToDelete->program_code }})</span>?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. All data associated with this program will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeDeleteConfirmationModal" 
                            class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteProgram" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteProgram">
                            <i class="fa-solid fa-trash mr-2"></i> Delete
                        </span>
                        <span wire:loading wire:target="deleteProgram">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>