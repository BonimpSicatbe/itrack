<!-- college-management.blade.php -->
<div class="w-full flex">
    <!-- Main Content Area -->
    <div class="w-full transition-all duration-300 ease-in-out">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-4 px-6 pt-6">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-xl font-semibold text-green-700">College Management</h3>
                    <p class="text-sm text-gray-600">| Manage colleges and their information.</p>
                </div>
            </div>
            <button 
                wire:click="openAddCollegeModal" 
                class="px-5 py-2 bg-green-600 text-white font-semibold rounded-xl text-sm cursor-pointer"
            >
                <i class="fa-solid fa-plus mr-2"></i>Add College
            </button>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-200 mb-4"></div>

        <!-- Search and Total Colleges -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4 px-6">
            <!-- Search Box -->
            <div class="w-full sm:w-1/2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        class="pl-10 block w-sm rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm" 
                        placeholder="Search by college name or acronym..."
                    >
                </div>
            </div>
            
            <!-- Total Colleges Badge -->
            <div class="flex items-center gap-2 bg-green-50 border border-green-600 px-4 py-2 rounded-xl shadow-sm">
                <i class="fa-solid fa-building-columns text-green-700"></i>
                <span class="text-sm font-semibold text-green-700">
                    Total Colleges: {{ $colleges->count() }}
                </span>
            </div>     
        </div>

        <!-- Colleges Table -->
        <div class=" border border-gray-200 shadow-sm">
            <table class="table table-auto table-striped table-pin-rows table-sm w-full rounded-lg">
                <thead>
                    <tr class="bg-base-300 font-bold uppercase">
                        <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4 text-left" wire:click="sortBy('name')" style="color: white; width: 70%;">
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
                        <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4 text-left" wire:click="sortBy('acronym')" style="color: white; width: 20%;">
                            <div class="flex items-center pt-2 pb-2">
                                Acronym
                                <div class="ml-1">
                                    @if($sortField === 'acronym')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="p-4 text-center bg-green-700" style="color: white; width: 10%;">Actions</th>                    
                    </tr>
                </thead>
                <tbody>
                    @forelse($colleges as $college)
                        <tr class="hover:bg-green-50" wire:key="college-{{ $college->id }}">
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $college->name }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $college->acronym }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap p-4">
                                <div class="flex justify-center space-x-2 text-base">
                                    <button class="text-amber-500 hover:bg-amber-100 rounded-lg p-2 tooltip cursor-pointer" 
                                            data-tip="Edit" 
                                            wire:click="openEditCollegeModal({{ $college->id }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center p-4">No colleges found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add College Modal -->
        @if($showAddCollegeModal)
            <x-modal name="add-college-modal" :show="$showAddCollegeModal" maxWidth="2xl">
                <!-- Header -->
                <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                    <i class="fa-solid fa-building-columns text-lg"></i>
                    <h3 class="text-xl font-semibold">Add New College</h3>
                </div>

                <!-- Body -->
                <div class="bg-white px-6 py-6 rounded-b-xl">
                    <div class="space-y-6">
                        <!-- College Name -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 tracking-wide uppercase">College Name</label>
                            <input type="text" wire:model="newCollege.name"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Enter college name">
                            @error('newCollege.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- College Acronym -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 tracking-wide uppercase">College Acronym</label>
                            <input type="text" wire:model="newCollege.acronym"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Enter college acronym">
                            @error('newCollege.acronym') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <button type="button" wire:click="closeAddCollegeModal"
                            class="px-5 py-2 rounded-xl border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" wire:click="addCollege" wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm shadow cursor-pointer">
                            <span wire:loading.remove wire:target="addCollege">Add College</span>
                            <span wire:loading wire:target="addCollege">
                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Adding...
                            </span>
                        </button>
                    </div>
                </div>
            </x-modal>
        @endif

        <!-- Edit College Modal -->
        @if($showEditCollegeModal)
            <x-modal name="edit-college-modal" :show="$showEditCollegeModal" maxWidth="2xl">
                <!-- Header -->
                <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3 " style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                    <i class="fa-solid fa-building-columns text-lg"></i>
                    <h3 class="text-xl font-semibold">Edit College</h3>
                </div>

                <!-- Body -->
                <div class="bg-white px-6 py-6 rounded-b-xl">
                    <div class="space-y-6">
                        <!-- College Name -->
                        <div>
                            <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">College Name</label>
                            <input type="text" wire:model="editingCollege.name"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500"
                                placeholder="Enter college name">
                            @error('editingCollege.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- College Acronym -->
                        <div>
                            <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">College Acronym</label>
                            <input type="text" wire:model="editingCollege.acronym"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500"
                                placeholder="Enter college acronym">
                            @error('editingCollege.acronym') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <button type="button" wire:click="closeEditCollegeModal"
                            class="px-5 py-2 rounded-xl border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" wire:click="updateCollege" wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm shadow cursor-pointer">
                            <span wire:loading.remove wire:target="updateCollege">Update College</span>
                            <span wire:loading wire:target="updateCollege">
                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Updating...
                            </span>
                        </button>
                    </div>
                </div>
            </x-modal>
        @endif

        <!-- Delete Confirmation Modal -->
        @if($showDeleteConfirmationModal && $collegeToDelete)
            <x-modal name="delete-college-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
                <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <h3 class="text-xl font-semibold">Confirm Deletion</h3>
                </div>

                <div class="bg-white px-6 py-6 rounded-b-xl">
                    <div class="space-y-4">
                        <p class="text-gray-700">
                            Are you sure you want to delete the college 
                            <span class="font-semibold text-red-600">"{{ $collegeToDelete->name }} ({{ $collegeToDelete->acronym }})"</span>?
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
                        <button type="button" wire:click="deleteCollege" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="deleteCollege">
                                <i class="fa-solid fa-trash mr-2"></i> Delete
                            </span>
                            <span wire:loading wire:target="deleteCollege">
                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                            </span>
                        </button>
                    </div>
                </div>
            </x-modal>
        @endif
    </div>
</div>