<div class="w-full flex">
    <div class="w-full transition-all duration-300 ease-in-out">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-4 px-6 pt-6">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-xl font-semibold text-green-700">Course Management</h3>
                    <p class="text-sm text-gray-600">| Manage courses and their information.</p>
                </div>
            </div>
            <button 
                wire:click="openAddCourseModal" 
                class="px-5 py-2 bg-green-600 text-white font-semibold rounded-full text-sm cursor-pointer"
            >
                <i class="fa-solid fa-plus mr-2"></i>Add Course
            </button>
        </div>

        <div class="border-b border-gray-200 mb-4"></div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4 px-6">
            
            <div class="flex items-center gap-2 bg-green-50 border border-green-600 px-4 py-2 rounded-xl shadow-sm">
                <i class="fa-solid fa-book text-green-700"></i>
                <span class="text-sm font-semibold text-green-700">
                    Total Courses: {{ $courses->count() }}
                </span>
            </div>

            <div class="w-full sm:w-1/2">
                <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">Search Courses</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm" 
                        placeholder="Search by code, name, faculty, or program"
                    >
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 shadow-sm">
            <table class="table table-auto table-striped table-pin-rows table-sm min-w-[1000px] rounded-xl">
                <thead>
                    <tr class="bg-base-300 font-bold uppercase">
                        <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4" wire:click="sortBy('course_code')" style="color: white;">
                            <div class="flex items-center pt-2 pb-2">
                                Course Code
                                <div class="ml-1">
                                    @if($sortField === 'course_code')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4" wire:click="sortBy('course_name')" style="color: white;">
                            <div class="flex items-center pt-2 pb-2">
                                Course Name
                                <div class="ml-1">
                                    @if($sortField === 'course_name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="p-4 bg-green-700" style="color: white;">Program</th>
                        <th class="p-4 bg-green-700" style="color: white;">Assigned Faculty</th>
                        <th class="p-4 text-right bg-green-700" style="color: white;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr class="hover:bg-green-50">
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $course->course_code }}</div>
                            </td>
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $course->course_name }}</div>
                            </td>
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $course->program->program_code ?? 'N/A' }}</div>
                            </td>
                            <td class="p-4">
                                @if($course->assignments->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($course->assignments as $assignment)
                                            <div class="text-sm text-gray-700">
                                                {{ $assignment->professor->firstname }} {{ $assignment->professor->lastname }}
                                                <span class="text-xs text-gray-500 ml-1">
                                                    ({{ $assignment->semester->name ?? 'N/A' }})
                                                </span>
                                                <button 
                                                    wire:click="openRemoveAssignmentModal({{ $assignment->assignment_id }})"
                                                    class="text-red-500 hover:text-red-700 ml-1 cursor-pointer text-xs"
                                                    title="Remove Assignment"
                                                >
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500 italic">No faculty assigned</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-right text-sm font-medium p-4">
                                <div class="flex justify-end space-x-2 text-base">
                                    <button class="text-amber-500 hover:bg-amber-100 rounded-xl p-2 tooltip cursor-pointer" 
                                            data-tip="Edit" 
                                            wire:click="openEditCourseModal({{ $course->id }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-4">No courses found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showAddCourseModal)
        <x-modal name="add-course-modal" :show="$showAddCourseModal" maxWidth="2xl">
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-book text-lg"></i>
                <h3 class="text-xl font-semibold">Add New Course</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Course Code *</label>
                        <input type="text" wire:model="newCourse.course_code"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter course code (e.g., CS101)">
                        @error('newCourse.course_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Course Name *</label>
                        <input type="text" wire:model="newCourse.course_name"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter course name">
                        @error('newCourse.course_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Program *</label>
                        <select wire:model="newCourse.program_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm">
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->program_name }} ({{ $program->program_code }})</option>
                            @endforeach
                        </select>
                        @error('newCourse.program_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" wire:click="closeAddCourseModal"
                        class="px-5 py-2 rounded-full border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="addCourse" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-full bg-green-600 text-white font-semibold text-sm shadow cursor-pointer">
                        <span wire:loading.remove wire:target="addCourse">Add Course</span>
                        <span wire:loading wire:target="addCourse">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Adding...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    @if($showEditCourseModal)
    <x-modal name="edit-course-modal" :show="$showEditCourseModal" maxWidth="3xl">
        <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <i class="fa-solid fa-book text-lg"></i>
            <h3 class="text-xl font-semibold">Edit Course & Assign Faculty</h3>
        </div>

        <div class="bg-white px-6 py-6 rounded-b-xl">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold text-gray-700 border-b pb-2">Course Information</h4>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Course Code *</label>
                        <input type="text" wire:model="editingCourse.course_code"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter course code">
                        @error('editingCourse.course_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Course Name *</label>
                        <input type="text" wire:model="editingCourse.course_name"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter course name">
                        @error('editingCourse.course_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Program *</label>
                        <select wire:model="editingCourse.program_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm">
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->program_name }} ({{ $program->program_code }})</option>
                            @endforeach
                        </select>
                        @error('editingCourse.program_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="space-y-6">
                    <h4 class="text-lg font-semibold text-gray-700 border-b pb-2">Assign Faculty</h4>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Current Assignments</label>
                        @if($currentCourseAssignments->count() > 0)
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                @foreach($currentCourseAssignments as $assignment)
                                    <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $assignment->professor->firstname }} {{ $assignment->professor->lastname }}
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                {{ $assignment->semester->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <button 
                                            wire:click="openRemoveAssignmentModal({{ $assignment->assignment_id }})"
                                            class="text-red-500 hover:text-red-700 ml-2 cursor-pointer"
                                            title="Remove Assignment"
                                        >
                                            <i class="fa-solid fa-times text-xs"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic">No faculty assigned</p>
                        @endif
                    </div>

                    <div class="border-t pt-4">
                        <h5 class="text-md font-semibold text-gray-700 mb-3">Assign New Faculty</h5>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700">Select Faculty</label>
                            <div class="relative mt-2" 
                                x-data="{
                                    open: @entangle('showFacultyDropdown'),
                                    searchText: @entangle('facultySearch'),
                                    updateInput() {
                                        // Force update the input value when faculty is selected
                                        this.$refs.facultyInput.value = this.searchText;
                                    }
                                }"
                                x-init="
                                    $watch('searchText', value => {
                                        updateInput();
                                    });
                                    $wire.on('faculty-selected', (event) => {
                                        searchText = event.facultyName;
                                        updateInput();
                                    });
                                "
                                x-on:click.away="open = false; $wire.closeFacultyDropdown()">
                                
                                <div class="relative">
                                    <input 
                                        type="text"
                                        x-ref="facultyInput"
                                        x-model="searchText"
                                        x-on:input="$wire.set('facultySearch', $event.target.value); open = true;"
                                        x-on:click="open = true"
                                        wire:keydown.escape="clearFacultySelection"
                                        class="block w-full rounded-xl border-gray-300 sm:text-sm pr-10 cursor-text"
                                        placeholder="Type to search faculty..."
                                        autocomplete="off"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                        @if($assignmentData['professor_id'])
                                            <button 
                                                type="button"
                                                wire:click="clearFacultySelection"
                                                class="text-gray-400 hover:text-gray-600 p-1"
                                                title="Clear selection"
                                            >
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        @endif
                                        <button 
                                            type="button"
                                            wire:click="toggleFacultyDropdown"
                                            class="text-gray-400 hover:text-gray-600 p-1"
                                            title="Toggle dropdown"
                                        >
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-xl shadow-lg max-h-60 overflow-auto">
                                    <div class="max-h-60 overflow-y-auto">
                                        @forelse($this->filteredProfessors as $professor)
                                            <button 
                                                type="button"
                                                wire:click="selectFaculty('{{ $professor['id'] }}', '{{ $professor['name'] }}')"
                                                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-900 rounded-lg flex items-center justify-between {{ $assignmentData['professor_id'] == $professor['id'] ? 'bg-green-50 text-green-900' : '' }}"
                                                x-on:click.stop
                                            >
                                                <span>{{ $professor['name'] }}</span>
                                                @if($assignmentData['professor_id'] == $professor['id'])
                                                    <i class="fa-solid fa-check text-green-600 ml-2"></i>
                                                @endif
                                            </button>
                                        @empty
                                            <div class="px-3 py-2 text-sm text-gray-500 text-center">No faculty members found</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @error('assignmentData.professor_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700">Select Semester</label>
                            <select wire:model="assignmentData.semester_id"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm">
                                <option value="">Select an Academic Semester</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">
                                        {{ $semester->name }}
                                        @if($semester->is_active)
                                            (Active)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('assignmentData.semester_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3 border-t pt-4">
                <button type="button" wire:click="closeEditCourseModal"
                    class="px-5 py-2 rounded-full border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                    Cancel
                </button>
                <button type="button" wire:click="updateCourse" wire:loading.attr="disabled"
                    class="px-5 py-2 rounded-full bg-green-600 text-white font-semibold text-sm shadow cursor-pointer">
                    <span wire:loading.remove wire:target="updateCourse">Update Course</span>
                    <span wire:loading wire:target="updateCourse">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Updating...
                    </span>
                </button>
            </div>
        </div>
    </x-modal>
@endif

    @if($showRemoveAssignmentModal && $assignmentToRemove)
        <x-modal name="remove-assignment-modal" :show="$showRemoveAssignmentModal" maxWidth="md">
            <div class="bg-orange-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Remove Assignment</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to remove 
                        <span class="font-semibold text-orange-600">
                            {{ $assignmentToRemove->professor->firstname }} {{ $assignmentToRemove->professor->lastname }}
                        </span> 
                        from teaching 
                        <span class="font-semibold text-orange-600">"{{ $assignmentToRemove->course->course_name }}"</span>?
                    </p>
                    <p class="text-sm text-gray-600">
                        This will remove the assignment for <span class="font-semibold text-orange-600">{{ $assignmentToRemove->semester->name ?? 'the selected semester' }}</span>.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeRemoveAssignmentModal" 
                            class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="removeAssignment" 
                            class="px-4 py-2 bg-orange-600 border border-transparent rounded-full text-sm font-medium text-white hover:bg-orange-700 cursor-pointer">
                        Remove Assignment
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>