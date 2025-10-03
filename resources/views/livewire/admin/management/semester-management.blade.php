<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-4 px-6 pt-6">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-xl font-semibold text-green-700">Semester Management</h3>
                <p class="text-sm text-gray-600">| Manage academic semesters and set active semester.</p>
            </div>
        </div>
        <label for="create_semester_modal" class="btn btn-md btn-success rounded-full text-gray-50"><i
                class="fa-solid fa-plus min-w-[20px] text-center"></i> Add Semester</label>

    </div>

    <!-- Divider -->
    <div class="border-b border-gray-200 mb-4"></div>

    <!-- Search and Total Semesters -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4 px-6">

        <!-- Total Semesters Badge -->
        <div class="flex items-center gap-2 bg-green-50 border border-green-600 px-4 py-2 rounded-xl shadow-sm">
            <i class="fa-solid fa-calendar-check text-green-700"></i>
            <span class="text-sm font-semibold text-green-700">
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
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                    placeholder="Search by name or acronym">
            </div>
        </div>
    </div>

    <!-- Semesters Table -->
    <div class="max-h-[500px] overflow-x-auto border border-gray-200 shadow-sm">
        <table class="table table-auto table-striped table-pin-rows table-sm w-full rounded-xl">
            <thead>
                <tr class="bg-base-300 font-bold uppercase">
                    <th class="cursor-pointer hover:bg-green-800 p-4 text-left bg-green-700" wire:click="sortBy('name')"
                        style="color: white; width: 25%;">
                        <div class="flex items-center pt-2 pb-2">
                            Semester Name
                            <div class="ml-1">
                                @if ($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort opacity-30"></i>
                                @endif
                            </div>
                        </div>
                    </th>
                    <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4 text-left"
                        wire:click="sortBy('start_date')" style="color: white; width: 20%;">
                        <div class="flex items-center pt-2 pb-2">
                            Start Date
                            <div class="ml-1">
                                @if ($sortField === 'start_date')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort opacity-30"></i>
                                @endif
                            </div>
                        </div>
                    </th>
                    <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4 text-left"
                        wire:click="sortBy('end_date')" style="color: white; width: 20%;">
                        <div class="flex items-center pt-2 pb-2">
                            End Date
                            <div class="ml-1">
                                @if ($sortField === 'end_date')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort opacity-30"></i>
                                @endif
                            </div>
                        </div>
                    </th>
                    <th class="p-4 text-left bg-green-700" style="color: white; width: 15%;">Status</th>
                    <th class="p-4 text-center bg-green-700" style="color: white; width: 20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semesters as $semester)
                    <tr class="hover:bg-green-50">
                        <td class="whitespace-nowrap p-4">
                            <div class="text-sm font-semibold text-gray-900 pl-4">
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
                            @if ($semester->is_active)
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="flex justify-center space-x-2 text-base">
                                {{-- @if ($semester->is_active)
                                    <!-- Archive button (deactivate) for active semester -->
                                    <button
                                        class="text-orange-600 hover:bg-orange-100 rounded-xl p-2 tooltip cursor-pointer"
                                        data-tip="Archive Semester" wire:click="setInactive({{ $semester->id }})">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                @else
                                    <!-- Activate button for inactive semester -->
                                    <button
                                        class="text-green-600 hover:bg-green-100 rounded-xl p-2 tooltip cursor-pointer"
                                        data-tip="Activate Semester" wire:click="setActive({{ $semester->id }})">
                                        <i class="fa-solid fa-square-check"></i>
                                    </button>
                                @endif --}}

                                <!-- Edit button (always enabled) -->
                                <button class="text-amber-500 hover:bg-blue-100 rounded-xl p-2 tooltip cursor-pointer"
                                    data-tip="Edit" wire:click="openEditModal({{ $semester->id }})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- Delete button (disabled for active semester) -->
                                @if (!$semester->is_active)
                                    <a href="{{ route('admin.semesters.download', $semester) }}"
                                        class="text-blue-600 hover:bg-blue-100 rounded-xl p-2 tooltip cursor-pointer"
                                        data-tip="Archive Semester" wire:click="setInactive({{ $semester->id }})">

                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                @else
                                    <a href="{{ route('admin.semesters.download', $semester) }}"
                                        class="text-blue-600 hover:bg-blue-100 rounded-xl p-2 tooltip cursor-pointer"
                                        data-tip="Archive Semester" wire:click="setInactive({{ $semester->id }})"
                                        disabled>

                                        <i class="fa-solid fa-download"></i>
                                    </a>
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
    <input type="checkbox" id="create_semester_modal" class="modal-toggle" checked />
    <div class="modal" role="dialog">
        <div class="modal-box p-0 m-0">
            {{-- header --}}
            <div class="text-white px-6 py-4 flex items-center space-x-3"
                style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-calendar-plus text-lg"></i>
                <h3 class="text-xl font-semibold">Add New Semester</h3>
            </div>

            {{-- body --}}
            <div class="flex flex-col p-4">
                <x-select-fieldset label="semester" name="semester" wire:model="semester">
                    <option value="first">First Semester</option>
                    <option value="second">Second Semester</option>
                    <option value="midyear">Midyear</option>
                </x-select-fieldset>

                <x-text-fieldset type="date" name="start_date" wire:model="start_date" label="Starting Date" :min="now()->format('Y-m-d')" required />
                <x-text-fieldset type="date" name="end_date" wire:model="end_date" label="Ending Date" :min="now()->format('Y-m-d')" required />

                <!-- Footer -->
                <div class="flex flex-row items-center gap-4 justify-end w-full">
                    <label for="create_semester_modal" class="btn btn-md btn-default rounded-full">Cancel</label>
                    <button wire:click='createSemester' type="button"
                        class="btn btn-md bg-green-600 hover:bg-green-700 text-white rounded-full">
                        <span wire:loading.remove wire:target="createSemester">Create Semester</span>
                        <span wire:loading wire:target="createSemester"><i
                                class="fa-solid fa-spinner fa-spin mr-2"></i> Creating...</span>
                    </button>
                </div>
            </div>
        </div>
        <label class="modal-backdrop" for="create_semester_modal">Close</label>
    </div>

    {{-- js for closing of modal --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('closeModal', ({
                modalId
            }) => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.checked = false; // closes the daisyUI modal
                }
            });
        });
    </script>

    <!-- Edit Semester Modal -->
    @if ($showEditModal && $editingSemester)
        <x-modal name="edit-semester-modal" :show="$showEditModal" maxWidth="md">
            <!-- Header -->
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3"
                style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-calendar text-lg"></i>
                <h3 class="text-xl font-semibold">Edit Semester</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Semester Name *</label>
                        <input type="text" wire:model="name"
                            class="mt-1 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                        @error('name')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Start Date *</label>
                        <input type="date" wire:model="start_date"
                            class="mt-1 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                        @error('start_date')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">End Date *</label>
                        <input type="date" wire:model="end_date"
                            class="mt-1 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                        @error('end_date')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
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
                        class="px-5 py-2 rounded-full border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="updateSemester" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-full bg-green-600 text-white font-semibold text-sm shadow hover:bg-1B512D cursor-pointer">
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
    @if ($showDeleteConfirmationModal && $semesterToDelete)
        <x-modal name="delete-semester-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete the semester
                        <span class="font-semibold text-red-600">"{{ $semesterToDelete->name }}"</span>?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. All data will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeDeleteConfirmationModal"
                        class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteSemester"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteSemester">
                            <i class="fa-solid fa-trash mr-2"></i> Delete
                        </span>
                        <span wire:loading wire:target="deleteSemester">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>
