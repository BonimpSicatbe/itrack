<div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-5">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 sticky top-0 z-10 bg-gradient-to-r from-green-800 to-green-600">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="text-2xl fa-solid fa-circle-info"></i> Requirement Details
        </h2>
        <a href="{{ route('admin.requirements.edit', $requirement->id) }}"
           class="bg-white text-green-700 px-4 py-1.5 rounded-full shadow font-semibold text-sm transition-all duration-200 flex items-center gap-2 hover:bg-gray-50">
            <i class="fa-solid fa-pencil text-green-700"></i> Edit
        </a>
    </div>

    <!-- Main Content - 3 Section Layout -->
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Section 1: Details with Files -->
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-info-circle text-green-600"></i> Requirement Details
                </h3>
                
                <!-- Basic Info -->
                <div class="space-y-4 mb-6">
                    <!-- Name -->
                    <div>
                        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1">Name</p>
                        <div class="bg-white p-3 rounded-lg border border-gray-300 text-gray-900 font-medium">
                            {{ $requirement->name }}
                        </div>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1">Due Date</p>
                        <div class="bg-white p-3 rounded-lg border border-gray-300 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-regular fa-calendar text-gray-500"></i>
                                <span class="text-gray-900 font-medium">{{ $requirement->due->format('M d, Y g:i A') }}</span>
                            </div>
                            @if($requirement->due->isPast())
                                <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">Overdue</span>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1">Description</p>
                        <div class="bg-white p-3 rounded-lg border border-gray-300">
                            <p class="text-gray-700 leading-relaxed">{{ $requirement->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Required Files -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-folder-open text-green-600"></i> Required Files
                    </h4>
                    <div class="space-y-2">
                        @forelse ($requiredFiles as $file)
                            <div class="bg-white rounded-lg p-3 border border-gray-300 hover:bg-gray-50 transition duration-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-file text-green-600"></i>
                                        <span class="font-medium text-gray-900">{{ $file->file_name }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('guide.download', $file->id) }}" 
                                           class="text-blue-500 hover:text-blue-700 transition duration-200"
                                           title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        @if($this->isPreviewable($file->mime_type))
                                        <a href="{{ route('guide.preview', $file->id) }}" 
                                           target="_blank" 
                                           class="text-green-500 hover:text-green-700 transition duration-200"
                                           title="Preview">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white rounded-lg p-3 border border-gray-300 text-center">
                                <p class="text-gray-500 text-sm">No required files attached.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Section 2: Assigned To (College & Department Hierarchy) -->
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-users text-green-600"></i> Assigned To
                </h3>

                <div class="space-y-4">
                    @if($assignedColleges->isNotEmpty() || $assignedDepartments->isNotEmpty())
                        <!-- Group departments by college -->
                        @php
                            $collegeDepartments = [];
                            foreach ($assignedDepartments as $dept) {
                                $collegeName = $dept->college->name ?? 'No College';
                                if (!isset($collegeDepartments[$collegeName])) {
                                    $collegeDepartments[$collegeName] = [];
                                }
                                $collegeDepartments[$collegeName][] = $dept->name;
                            }
                            
                            // Add colleges without specific departments
                            foreach ($assignedColleges as $college) {
                                if (!isset($collegeDepartments[$college->name])) {
                                    $collegeDepartments[$college->name] = [];
                                }
                            }
                        @endphp

                        @foreach($collegeDepartments as $collegeName => $departments)
                            <div class="bg-white rounded-lg border border-gray-300 overflow-hidden">
                                <!-- College Header -->
                                <div class="bg-green-100 px-4 py-3 border-b border-green-200">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-building-columns text-green-700"></i>
                                        <h4 class="font-semibold text-green-800">{{ $collegeName }}</h4>
                                    </div>
                                </div>
                                
                                <!-- Departments List -->
                                <div class="p-4">
                                    @if(!empty($departments))
                                        <div class="space-y-2">
                                            @foreach($departments as $department)
                                                <div class="flex items-center gap-3 py-2 px-3 bg-gray-50 rounded-lg">
                                                    <i class="fa-solid fa-building text-gray-500 text-sm"></i>
                                                    <span class="text-gray-700 font-medium">{{ $department }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <p class="text-gray-500 text-sm">All departments in this college</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="bg-white rounded-lg p-4 border border-gray-300 text-center">
                            <p class="text-gray-500">No colleges or departments assigned.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section 3: Assigned Users Table -->
        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user-check text-green-600"></i> 
                Assigned Users ({{ $assignedUsers->count() }})
            </h3>
            
            <div class="bg-white rounded-lg border border-gray-300 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-green-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">College</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Email</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-300">
                            @forelse ($assignedUsers as $user)
                                <tr class="hover:bg-green-50 transition duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center bg-gradient-to-br from-green-400 to-green-600 text-white font-bold shadow">
                                                {{ strtoupper(substr($user->full_name ?? $user->name ?? $user->email, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-gray-900 truncate">
                                                    {{ $user->full_name ?? $user->name ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $user->college->name ?? $user->department->college->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $user->department->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $user->email }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No users assigned to this requirement.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>