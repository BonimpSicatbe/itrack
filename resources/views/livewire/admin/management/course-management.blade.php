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
                class="px-5 py-2 bg-green-600 text-white font-semibold rounded-xl text-sm cursor-pointer"
            >
                <i class="fa-solid fa-plus mr-2"></i>Add Course
            </button>
        </div>

        <div class="border-b border-gray-200 mb-4"></div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4 px-6">
            <div class="w-full sm:w-1/2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        class="pl-10 block w-sm rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm" 
                        placeholder="Search college code, name, faculty, or program"
                    >
                </div>
            </div>
            <div class="flex items-center gap-2 bg-green-50 border border-green-600 px-4 py-2 rounded-xl shadow-sm">
                <i class="fa-solid fa-book text-green-700"></i>
                <span class="text-sm font-semibold text-green-700">
                    Total Courses: {{ $courses->count() }}
                </span>
            </div>
        </div>

        <div class=" border border-gray-200 shadow-sm">
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
                        <th class="p-4 text-center bg-green-700" style="color: white;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr class="hover:bg-green-50 cursor-pointer" wire:click="openCourseDetailsModal({{ $course->id }})">
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $course->course_code }}</div>
                            </td>
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $course->course_name }}</div>
                            </td>
                            <td class="whitespace-nowrap p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $course->program->program_code ?? 'N/A' }}</div>
                            </td>
                            <td class="whitespace-nowrap text-center text-sm font-medium p-4">
                                <div class="flex justify-center space-x-2 text-base">
                                    <button class="text-amber-500 hover:bg-amber-100 rounded-xl p-2 tooltip cursor-pointer" 
                                            data-tip="Edit" 
                                            wire:click.stop="openEditCourseModal({{ $course->id }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-4">No courses found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showAddCourseModal)
        <x-modal name="add-course-modal" :show="$showAddCourseModal" maxWidth="2xl">
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3 " style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-book text-lg"></i>
                <h3 class="text-xl font-semibold">Add New Course</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Course Code</label>
                        <input type="text" wire:model="newCourse.course_code"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter course code (e.g., CS101)">
                        @error('newCourse.course_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Course Name</label>
                        <input type="text" wire:model="newCourse.course_name"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter course name">
                        @error('newCourse.course_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Description</label>
                        <textarea wire:model="newCourse.description"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter course description"
                            rows="3"></textarea>
                        @error('newCourse.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Program</label>
                        <select wire:model="newCourse.program_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500">
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->program_name }} ({{ $program->program_code }})</option>
                            @endforeach
                        </select>
                        @error('newCourse.program_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Course Type</label>
                        <select wire:model="newCourse.course_type_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500">
                            <option value="">Select Course Type</option>
                            @foreach($courseTypes as $courseType)
                                <option value="{{ $courseType->id }}">{{ $courseType->name }}</option>
                            @endforeach
                        </select>
                        @error('newCourse.course_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" wire:click="closeAddCourseModal"
                        class="px-5 py-2 rounded-xl border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="addCourse" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm shadow cursor-pointer">
                        <span wire:loading.remove wire:target="addCourse">Add Course</span>
                        <span wire:loading wire:target="addCourse">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Adding...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    @if($showCourseDetailsModal && $selectedCourse)
        <x-modal name="course-details-modal" :show="!!$selectedCourse" maxWidth="4xl">
            <!-- Header -->
            <div class="bg-green-700 text-white rounded-t-xl px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-book text-lg"></i>
                    <h3 class="text-xl font-semibold">Course Details</h3>
                </div>
                <button wire:click="closeCourseDetailsModal"
                    class="text-white hover:text-gray-200 focus:outline-none cursor-pointer">
                    <i class="fa-solid fa-xmark h-5 w-5"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <!-- Course header -->
                <div class="mb-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="h-16 w-16 flex-shrink-0">
                                <div class="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center text-xl font-medium text-green-800">
                                    <i class="fa-solid fa-book"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ $selectedCourse->course_code }} - {{ $selectedCourse->course_name }}
                                </h3>
                                <p class="text-sm text-gray-500">{{ $selectedCourse->description ?? 'No description provided' }}</p>
                            </div>
                        </div>

                        <!-- Course Type badge positioned on the far right -->
                        <div class="flex-shrink-0">
                            @if ($selectedCourse->courseType)
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $selectedCourse->courseType->name }}
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    No Type
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main content with two columns -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left column: Course details -->
                    <div class="space-y-6">
                        <!-- Course Information -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Course Information</h4>
                            <dl class="space-y-2">
                                <div class="sm:flex sm:items-center">
                                    <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Course Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $selectedCourse->course_code }}
                                    </dd>
                                </div>
                                <div class="sm:flex sm:items-center">
                                    <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Course Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $selectedCourse->course_name }}
                                    </dd>
                                </div>
                                <div class="sm:flex sm:items-center">
                                    <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $selectedCourse->description ?? 'No description provided' }}
                                    </dd>
                                </div>
                                <div class="sm:flex sm:items-center">
                                    <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Course Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $selectedCourse->courseType->name ?? 'N/A' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Program Information -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Program Information</h4>
                            <dl class="space-y-2">
                                <div class="sm:flex sm:items-center">
                                    <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">College</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $selectedCourse->program->college->name ?? 'N/A' }}
                                        @if($selectedCourse->program->college->acronym ?? false)
                                            ({{ $selectedCourse->program->college->acronym }})
                                        @endif
                                    </dd>
                                </div>
                                <div class="sm:flex sm:items-center">
                                    <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Program</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $selectedCourse->program->program_name ?? 'N/A' }} ({{ $selectedCourse->program->program_code ?? 'N/A' }})
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Right column: Faculty Assignments -->
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Faculty Assignments</h4>
                            
                            @if($selectedCourse->assignments && $selectedCourse->assignments->count() > 0)
                                @php
                                    // Group course assignments by semester
                                    $groupedAssignments = $selectedCourse->assignments->groupBy(function($assignment) {
                                        return $assignment->semester->id ?? 'no-semester';
                                    });
                                @endphp
                                
                                <div class="space-y-4 max-h-80 overflow-y-auto">
                                    @foreach($groupedAssignments as $semesterId => $assignments)
                                        @php
                                            $semester = $assignments->first()->semester ?? null;
                                        @endphp
                                        
                                        <div class="border border-gray-200 rounded-xl bg-white shadow-sm">
                                            <!-- Semester Header -->
                                            @if($semester)
                                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 rounded-t-xl">
                                                    <div class="flex items-center justify-between">
                                                        <h5 class="font-semibold text-gray-900 text-xs">
                                                            {{ $semester->name }}
                                                        </h5>
                                                        @if($semester->is_active)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Active
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                                    <h5 class="font-semibold text-gray-900 text-sm">No Semester Assigned</h5>
                                                </div>
                                            @endif

                                            <!-- Faculty List -->
                                            <div class="p-3 space-y-2">
                                                @foreach($assignments as $assignment)
                                                    <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                                        <div class="flex items-center space-x-3 flex-1">
                                                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                                                <i class="fa-solid fa-user text-white text-xs"></i>
                                                            </div>
                                                            <div class="flex-1">
                                                                <span class="font-medium text-xs text-gray-900">
                                                                    {{ $assignment->professor->firstname }} {{ $assignment->professor->lastname }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            Assigned: {{ $assignment->assignment_date ? \Carbon\Carbon::parse($assignment->assignment_date)->format('M d, Y') : 'N/A' }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-xl">
                                    <i class="fa-solid fa-user-slash text-gray-400 text-3xl mb-2"></i>
                                    <p class="text-gray-500 text-sm">No faculty assigned to this course</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeCourseDetailsModal" class="bg-white py-2 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
                        Close
                    </button>
                    <button type="button" wire:click="openEditCourseModal({{ $selectedCourse->id }})" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Edit Course
                    </button>
                </div>
            </div>
        </x-modal>
    @endif  

    @if($showEditCourseModal)
        <x-modal name="edit-course-modal" :show="$showEditCourseModal" maxWidth="4xl">
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3 " style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-book text-lg"></i>
                <h3 class="text-xl font-semibold">Edit Course & Assign Faculty</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-700 border-b pb-2">Course Information</h4>
                        
                        <div>
                            <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Course Code</label>
                            <input type="text" wire:model="editingCourse.course_code"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500"
                                placeholder="Enter course code">
                            @error('editingCourse.course_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Course Name</label>
                            <input type="text" wire:model="editingCourse.course_name"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500"
                                placeholder="Enter course name">
                            @error('editingCourse.course_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Description</label>
                            <textarea wire:model="editingCourse.description"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500"
                                placeholder="Enter course description"
                                rows="3"></textarea>
                            @error('editingCourse.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Program</label>
                            <select wire:model="editingCourse.program_id"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500">
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->program_name }} ({{ $program->program_code }})</option>
                                @endforeach
                            </select>
                            @error('editingCourse.program_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Course Type</label>
                            <select wire:model="editingCourse.course_type_id"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500">
                                <option value="">Select Course Type</option>
                                @foreach($courseTypes as $courseType)
                                    <option value="{{ $courseType->id }}">{{ $courseType->name }}</option>
                                @endforeach
                            </select>
                            @error('editingCourse.course_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-700 border-b pb-2">Assign Faculty</h4>

                        <div>
                            <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700 mb-2">Current Assignments</label>
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
                                <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Select Faculty</label>
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
                                                <div class="px-3 py-2 text-sm text-gray-500 text-center">
                                                    @if($currentCourseAssignments->count() > 0)
                                                        <i class="fa-solid fa-user-check mr-1"></i> All available faculty are already assigned to this course
                                                    @else
                                                        No faculty members found
                                                    @endif
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                @error('assignmentData.professor_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Select Semester</label>
                                <select wire:model="assignmentData.semester_id"
                                    class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm text-gray-500">
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

                            <!-- 🔥 SINGLE Assign Faculty Button placed here -->
                            <div class="flex justify-end">
                                <button type="button" 
                                    wire:click="assignProfessor" 
                                    wire:loading.attr="disabled"
                                    class="w-full px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold text-sm shadow cursor-pointer hover:bg-blue-700 transition-colors"
                                    {{ empty($assignmentData['professor_id']) || empty($assignmentData['semester_id']) ? 'disabled' : '' }}>
                                    <span wire:loading.remove wire:target="assignProfessor">
                                        <i class="fa-solid fa-user-plus mr-2"></i>Assign Faculty
                                    </span>
                                    <span wire:loading wire:target="assignProfessor">
                                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Assigning...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🔥 UPDATED: Footer buttons - Removed duplicate Assign Faculty button -->
                <div class="mt-8 flex justify-end space-x-3 border-t pt-4">
                    <button type="button" 
                        wire:click="closeEditCourseModal"
                        class="px-5 py-2 rounded-xl border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="button" 
                        wire:click="updateCourse" 
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm shadow cursor-pointer hover:bg-green-700 transition-colors">
                        <span wire:loading.remove wire:target="updateCourse">Update Course</span>
                        <span wire:loading wire:target="updateCourse">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Updating...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
        @endif

    @if($showRemoveAssignmentModal)
        <x-modal name="remove-assignment-modal" :show="$showRemoveAssignmentModal" maxWidth="md">
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3 bg-red-600">
                <i class="fa-solid fa-user-xmark text-lg"></i>
                <h3 class="text-xl font-semibold">Remove Faculty Assignment</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to remove 
                        <span class="font-semibold">{{ $assignmentToRemove->professor->firstname ?? '' }} {{ $assignmentToRemove->professor->lastname ?? '' }}</span> 
                        from teaching 
                        <span class="font-semibold">{{ $assignmentToRemove->course->course_name ?? '' }}</span> 
                        for <span class="font-semibold">{{ $assignmentToRemove->semester->name ?? 'the selected semester' }}</span>?
                    </p>
                    <p class="text-sm text-gray-600">This action cannot be undone.</p>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" wire:click="closeRemoveAssignmentModal"
                        class="px-5 py-2 rounded-xl border border-gray-300 text-gray-500 bg-white font-semibold text-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="removeAssignment" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-red-600 text-white font-semibold text-sm shadow cursor-pointer">
                        <span wire:loading.remove wire:target="removeAssignment">Remove Assignment</span>
                        <span wire:loading wire:target="removeAssignment">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Removing...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    

    
    <style>
        .table {
            border-spacing: 0;
        }
        .table tr:last-child td:first-child {
            border-bottom-left-radius: 0.75rem;
        }
        .table tr:last-child td:last-child {
            border-bottom-right-radius: 0.75rem;
        }
    </style>
</div>

@script
<script>
    // Listen for notifications from Livewire
    $wire.on('showNotification', (event) => {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-lg text-white font-semibold transition-all duration-300 transform translate-x-full ${getNotificationClass(event.type)}`;
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="${getNotificationIcon(event.type)}"></i>
                <span>${event.content}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    });

    function getNotificationClass(type) {
        switch (type) {
            case 'success': return 'bg-green-600';
            case 'warning': return 'bg-amber-500';
            case 'error': return 'bg-red-600';
            default: return 'bg-gray-600';
        }
    }

    function getNotificationIcon(type) {
        switch (type) {
            case 'success': return 'fa-solid fa-circle-check';
            case 'warning': return 'fa-solid fa-triangle-exclamation';
            case 'error': return 'fa-solid fa-circle-exclamation';
            default: return 'fa-solid fa-info-circle';
        }
    }
</script>
@endscript

