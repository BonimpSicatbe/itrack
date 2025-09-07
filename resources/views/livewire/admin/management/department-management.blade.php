<!-- department-management.blade.php -->
<div class="w-full flex">
    <!-- Main Content Area -->
    <div class="w-full transition-all duration-300 ease-in-out">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-xl font-semibold text-green-700">Department Management</h3>
                    <p class="text-sm text-gray-600">| Manage departments and their information.</p>
                </div>
            </div>
            <button 
                wire:click="openAddDepartmentModal" 
                class="px-5 py-2 bg-green-600 text-white font-semibold rounded-full text-sm cursor-pointer"
            >
                <i class="fa-solid fa-plus mr-2"></i>Add Department
            </button>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-200 mb-4"></div>

        <!-- Search and Total Departments -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4">
            
            <!-- Total Departments Badge -->
            <div class="flex items-center gap-2 bg-green-50 border border-green-600 px-4 py-2 rounded-xl shadow-sm">
                <i class="fa-solid fa-building text-green-700"></i>
                <span class="text-sm font-semibold text-green-700">
                    Total Departments: {{ $departments->count() }}
                </span>
            </div>

            <!-- Search Box -->
            <div class="w-full sm:w-1/2">
                <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">Search Departments</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm" 
                        placeholder="Search by name or college"
                    >
                </div>
            </div>
        </div>

        <!-- Departments Table -->
        <div class="max-h-[500px] overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
            <table class="table table-auto table-striped table-pin-rows table-sm min-w-[800px] rounded-xl">
                <thead>
                    <tr class="bg-base-300 font-bold uppercase">
                        <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4" wire:click="sortBy('name')" style="color: white;">
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
                        <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4" wire:click="sortBy('college_id')" style="color: white;">
                            <div class="flex items-center pt-2 pb-2">
                                College
                                <div class="ml-1">
                                    @if($sortField === 'college_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="p-4 text-right bg-green-700" style="color: white;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr class="hover:bg-green-50">
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $department->name }}</div>
                            </td>
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $department->college->name }} ({{ $department->college->acronym }})</div>
                            </td>
                            <td class="whitespace-nowrap text-right text-sm font-medium p-4">
                                <div class="flex justify-end space-x-2 text-base">
                                    <button class="text-amber-500 hover:bg-amber-100 rounded-xl p-2 tooltip cursor-pointer" data-tip="Edit" wire:click="openEditDepartmentModal({{ $department->id }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="text-red-600 hover:bg-red-100 rounded-xl p-2 tooltip cursor-pointer"
                                                data-tip="Delete" wire:click="openDeleteConfirmationModal({{ $department->id }})">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center p-4">No departments found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Department Modal -->
    @if($showAddDepartmentModal)
        <x-modal name="add-department-modal" :show="$showAddDepartmentModal" maxWidth="2xl">
            <!-- Header -->
            <div class="bg-1C7C54 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-building text-lg"></i>
                <h3 class="text-xl font-semibold">Add New Department</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-6">
                    <!-- Department Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Department Name *</label>
                        <input type="text" wire:model="newDepartment.name"
                            class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                            placeholder="Enter department name">
                        @error('newDepartment.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- College Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">College *</label>
                        <select wire:model="newDepartment.college_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                            <option value="">Select a College</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }} ({{ $college->acronym }})</option>
                            @endforeach
                        </select>
                        @error('newDepartment.college_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" wire:click="closeAddDepartmentModal"
                        class="px-5 py-2 rounded-full border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="addDepartment" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-full bg-1C7C54 text-white font-semibold text-sm shadow hover:bg-1B512D cursor-pointer">
                        <span wire:loading.remove wire:target="addDepartment">Add Department</span>
                        <span wire:loading wire:target="addDepartment">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Adding...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Edit Department Modal -->
    @if($showEditDepartmentModal)
        <x-modal name="edit-department-modal" :show="$showEditDepartmentModal" maxWidth="2xl">
            <!-- Header -->
            <div class="bg-1C7C54 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-building text-lg"></i>
                <h3 class="text-xl font-semibold">Edit Department</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-6">
                    <!-- Department Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Department Name *</label>
                        <input type="text" wire:model="editingDepartment.name"
                            class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                            placeholder="Enter department name">
                        @error('editingDepartment.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- College Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">College *</label>
                        <select wire:model="editingDepartment.college_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                            <option value="">Select a College</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }} ({{ $college->acronym }})</option>
                            @endforeach
                        </select>
                        @error('editingDepartment.college_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" wire:click="closeEditDepartmentModal"
                        class="px-5 py-2 rounded-full border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="updateDepartment" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-full bg-1C7C54 text-white font-semibold text-sm shadow hover:bg-1B512D cursor-pointer">
                        <span wire:loading.remove wire:target="updateDepartment">Update Department</span>
                        <span wire:loading wire:target="updateDepartment">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Updating...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirmationModal && $departmentToDelete)
        <x-modal name="delete-department-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete the department 
                        <span class="font-semibold text-red-600">"{{ $departmentToDelete->name }}"</span>?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. All data will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeDeleteConfirmationModal" 
                            class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteDepartment" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteDepartment">
                            <i class="fa-solid fa-trash mr-2"></i> Delete
                        </span>
                        <span wire:loading wire:target="deleteDepartment">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

</div>