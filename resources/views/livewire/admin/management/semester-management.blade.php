<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-xl font-semibold text-1B512D">Semester Management</h3>
                <p class="text-sm text-gray-600">| Manage academic semesters and set active semester.</p>
            </div>
        </div>
        <button wire:click="openCreateModal"
            class="px-5 py-2 bg-1C7C54 text-white font-semibold rounded-full hover:bg-1B512D focus:outline-none focus:ring-2 focus:ring-73E2A7 focus:ring-offset-2 transition text-sm cursor-pointer">
            <i class="fa-solid fa-plus mr-2"></i>Add Semester
        </button>
    </div>

    <!-- Divider -->
    <div class="border-b border-gray-200 mb-4"></div>

    <!-- Search and Total Semesters -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4">
        
        <!-- Total Semesters Badge -->
        <div class="flex items-center gap-2 bg-1C7C54/10 border border-1C7C54/30 px-4 py-2 rounded-xl shadow-sm">
            <i class="fa-solid fa-calendar-check text-1C7C54"></i>
            <span class="text-sm font-semibold text-1C7C54">
                Total Semesters: {{ $semesters->count() }}
            </span>
        </div>

        <!-- Search Box -->
        <div class="w-full sm:w-1/2">
            <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">Search Semesters</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm" 
                    placeholder="Search by name or acronym"
                >
            </div>
        </div>
    </div>




    <!-- Semesters Table -->
    <div class="max-h-[500px] overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="table table-auto table-striped table-pin-rows table-sm w-full rounded-lg">
            <thead>
                <tr class="bg-base-300 font-bold uppercase">
                    <th class="cursor-pointer hover:bg-blue-50 p-4 text-left" wire:click="sortBy('name')" style="background-color: #1C7C54; color: white; width: 25%;">
                        <div class="flex items-center pt-2 pb-2">
                            Semester Name
                            <div class="ml-1">
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort opacity-30"></i>
                                @endif
                            </div>
                        </div>
                    </th>
                    <th class="cursor-pointer hover:bg-blue-50 p-4 text-left" wire:click="sortBy('start_date')" style="background-color: #1C7C54; color: white; width: 20%;">
                        <div class="flex items-center pt-2 pb-2">
                            Start Date
                            <div class="ml-1">
                                @if($sortField === 'start_date')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort opacity-30"></i>
                                @endif
                            </div>
                        </div>
                    </th>
                    <th class="cursor-pointer hover:bg-blue-50 p-4 text-left" wire:click="sortBy('end_date')" style="background-color: #1C7C54; color: white; width: 20%;">
                        <div class="flex items-center pt-2 pb-2">
                            End Date
                            <div class="ml-1">
                                @if($sortField === 'end_date')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort opacity-30"></i>
                                @endif
                            </div>
                        </div>
                    </th>
                    <th class="p-4 text-left" style="background-color: #1C7C54; color: white; width: 15%;">Status</th>
                    <th class="p-4 text-center" style="background-color: #1C7C54; color: white; width: 20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semesters as $semester)
                    <tr class="hover:bg-blue-50">
                        <td class="whitespace-nowrap p-4">
                            <div class="text-sm font-medium text-gray-900 pl-4">
                                {{ $semester->name }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="text-sm text-gray-500">
                                {{ $semester->start_date->format('M d, Y') }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="text-sm text-gray-500">
                                {{ $semester->end_date->format('M d, Y') }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap p-4">
                            @if($semester->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="flex justify-center space-x-2 text-base">
                                @if($semester->is_active)
                                    <!-- Archive button (deactivate) for active semester -->
                                    <button class="text-orange-600 hover:bg-orange-100 rounded-lg p-2 tooltip cursor-pointer" 
                                            data-tip="Archive Semester" 
                                            wire:click="setInactive({{ $semester->id }})">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                @else
                                    <!-- Activate button for inactive semester -->
                                    <button class="text-green-600 hover:bg-green-100 rounded-lg p-2 tooltip cursor-pointer" 
                                            data-tip="Activate Semester" 
                                            wire:click="setActive({{ $semester->id }})">
                                        <i class="fa-solid fa-square-check"></i>
                                    </button>
                                @endif
                                
                                <!-- Edit button (always enabled) -->
                                <button class="text-amber-500 hover:bg-blue-100 rounded-lg p-2 tooltip cursor-pointer" 
                                        data-tip="Edit" 
                                        wire:click="openEditModal({{ $semester->id }})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                
                                <!-- Delete button (disabled for active semester) -->
                                @if(!$semester->is_active)
                                    <button class="text-red-600 hover:bg-red-100 rounded-lg p-2 tooltip cursor-pointer"
                                            data-tip="Delete" 
                                            wire:click="openDeleteConfirmationModal({{ $semester->id }})">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @else
                                    <button class="text-gray-400 rounded-lg p-2 cursor-default" disabled>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-4">No semesters found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create Semester Modal -->
    @if($showCreateModal)
        <x-modal name="create-semester-modal" :show="$showCreateModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-1C7C54 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-calendar-plus text-lg"></i>
                <h3 class="text-xl font-semibold">Add New Semester</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Semester Name *</label>
                        <input type="text" wire:model="name" 
                            class="mt-1 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                            placeholder="e.g., 1st Semester 2023-2024">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Start Date *</label>
                        <input type="date" wire:model="start_date" 
                            class="mt-1 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                        @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">End Date *</label>
                        <input type="date" wire:model="end_date" 
                            class="mt-1 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                        @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" wire:model="isActive" id="isActive" 
                            class="rounded border-gray-300 text-1C7C54 focus:ring-1C7C54">
                        <label for="isActive" class="ml-2 text-sm text-gray-700">Set as active semester</label>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" wire:click="closeCreateModal"
                            class="px-5 py-2 rounded-full border border-1C7C54 text-1C7C54 bg-white hover:bg-73E2A7 hover:text-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="createSemester" wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-full bg-1C7C54 text-white font-semibold text-sm shadow hover:bg-1B512D cursor-pointer">
                        <span wire:loading.remove wire:target="createSemester">Create Semester</span>
                        <span wire:loading wire:target="createSemester">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Creating...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Edit Semester Modal -->
    @if($showEditModal && $editingSemester)
        <x-modal name="edit-semester-modal" :show="$showEditModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-1C7C54 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-calendar-edit text-lg"></i>
                <h3 class="text-xl font-semibold">Edit Semester</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Semester Name *</label>
                        <input type="text" wire:model="name" 
                            class="mt-1 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Start Date *</label>
                        <input type="date" wire:model="start_date" 
                            class="mt-1 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                        @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">End Date *</label>
                        <input type="date" wire:model="end_date" 
                            class="mt-1 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                        @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" wire:model="isActive" id="editIsActive" 
                            class="rounded border-gray-300 text-1C7C54 focus:ring-1C7C54">
                        <label for="editIsActive" class="ml-2 text-sm text-gray-700">Set as active semester</label>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" wire:click="closeEditModal"
                            class="px-5 py-2 rounded-full border border-1C7C54 text-1C7C54 bg-white hover:bg-73E2A7 hover:text-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="updateSemester" wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-full bg-1C7C54 text-white font-semibold text-sm shadow hover:bg-1B512D cursor-pointer">
                        <span wire:loading.remove wire:target="updateSemester">Update Semester</span>
                        <span wire:loading wire:target="updateSemester">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Updating...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirmationModal && $semesterToDelete)
        <x-modal name="delete-semester-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-red-600 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Delete Semester</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="text-center">
                    <p class="text-sm text-gray-700">
                        Are you sure you want to delete  
                        <span class="font-semibold text-gray-900">
                            {{ $semesterToDelete->name }}
                        </span>?  
                        <br>This action cannot be undone.
                    </p>
                </div>

                <!-- Footer -->
                <div class="mt-8 flex justify-center space-x-3">
                    <button 
                        type="button" 
                        wire:click="closeDeleteConfirmationModal" 
                        class="px-5 py-2 rounded-full border border-1C7C54 text-1C7C54 bg-white hover:bg-73E2A7 hover:text-white font-semibold text-sm cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="deleteSemester" 
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-full bg-red-600 text-white font-semibold text-sm shadow hover:bg-red-700 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="deleteSemester">Delete</span>
                        <span wire:loading wire:target="deleteSemester">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>