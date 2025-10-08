<div class="flex flex-col gap-2 mb-6">
    <!-- Header / Toolbar -->
    <div class="flex justify-between items-center text-white p-4 rounded-xl shadow-md" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="flex items-center gap-3">
            <div class="pl-3 bg-1C7C54/10 rounded-xl">
                <i class="fa-solid fa-folder-tree text-white text-2xl"></i>
            </div>
            <h2 class="text-xl md:text-xl font-semibold">Submitted Requirements</h2>
        </div>

        <!-- View toggle buttons -->
        <div class="flex items-center gap-1 bg-white/20 p-1 rounded-xl">
            <button 
                wire:click="switchView('list')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                title="List view"
            >
                <i class="fas fa-bars"></i>
            </button>
            <button 
                wire:click="switchView('grid')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                title="Grid view"
            >
                <i class="fas fa-border-all"></i>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col gap-4 min-h-[calc(100vh_-_190px)]">
        @if($activeSemester)
            <!-- Filter Bar -->
            <div class="rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-col sm:flex-row items-center gap-4 p-2">
                    <div class="relative max-w-md w-full md:w-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-500 text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            class="block w-sm p-2 pl-9 text-sm text-1B512D border border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 rounded-xl" 
                            placeholder="Search @if($category === 'overview') users... @elseif($selectedRequirementId && $selectedUserId) courses... @elseif($selectedRequirementId) users... @else requirements... @endif"
                        >
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-1 border border-gray-300 shadow-sm p-1 rounded-xl bg-white font-semibold ml-auto mr-2">
                    @foreach($categories as $key => $label)
                        <button
                            wire:click="setCategory('{{ $key }}')"
                            class="px-4 py-2 text-sm rounded-lg font-medium transition-colors 
                                {{ $category === $key ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-green-600/20 hover:text-1B512D' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Breadcrumb Navigation for Requirement Category -->
            @if($category === 'requirement')
                <div class="flex items-center gap-2 text-sm text-gray-600 mb-4 p-3 bg-gray-50 rounded-lg border">
                    <button 
                        wire:click="goBack('requirement')" 
                        class="flex items-center gap-2 px-3 py-1 rounded-lg bg-white border hover:bg-green-50 hover:border-green-200 transition-colors"
                    >
                        <i class="fas fa-home text-green-600"></i>
                        <span class="font-medium text-green-700">Requirements</span>
                    </button>
                    
                    @foreach($breadcrumb as $index => $crumb)
                        <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                        @if($index === count($breadcrumb) - 1)
                            <div class="flex items-center gap-2 px-3 py-1 rounded-lg bg-green-100 border border-green-200">
                                @switch($crumb['type'])
                                    @case('requirement')
                                        <i class="fas fa-folder text-green-600"></i>
                                    @break
                                    @case('user')
                                        <i class="fas fa-user text-blue-600"></i>
                                    @break
                                    @case('course')
                                        <i class="fas fa-book text-purple-600"></i>
                                    @break
                                @endswitch
                                <span class="font-semibold text-green-800">{{ $crumb['name'] }}</span>
                            </div>
                        @else
                            <button 
                                wire:click="goBack('{{ $crumb['type'] }}')" 
                                class="flex items-center gap-2 px-3 py-1 rounded-lg bg-white border hover:bg-green-50 hover:border-green-200 transition-colors"
                            >
                                @switch($crumb['type'])
                                    @case('requirement')
                                        <i class="fas fa-folder text-green-600"></i>
                                    @break
                                    @case('user')
                                        <i class="fas fa-user text-blue-600"></i>
                                    @break
                                    @case('course')
                                        <i class="fas fa-book text-purple-600"></i>
                                    @break
                                @endswitch
                                <span class="font-medium text-green-700">{{ $crumb['name'] }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif

            <!-- Content -->
            <div>
                @if($category === 'overview')
                    <!-- Include Overview Component -->
                    <livewire:admin.submitted-requirements.submitted-requirements-overview :search="$search" />
                @else
                    <!-- Requirement Category with File Manager Style -->

                    <!-- LEVEL 1: Requirements List -->
                    @if(!$selectedRequirementId)
                        @if($viewMode === 'list')
                            <!-- List View for Requirements -->
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-8 flex items-center gap-2">
                                        <i class="fas fa-folder text-green-600"></i>
                                        <span>Requirement Name</span>
                                    </div>
                                    <div class="col-span-2 text-center">Submissions</div>
                                    <div class="col-span-2 text-center">Actions</div>
                                </div>
                                
                                @forelse ($requirements as $requirement)
                                    <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 hover:bg-green-50 transition-colors">
                                        <div class="col-span-8 flex items-center gap-3">
                                            <i class="fas fa-folder text-green-600 text-xl"></i>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-gray-800">{{ $requirement['name'] }}</span>
                                                <span class="text-xs text-gray-500">{{ $requirement['submission_count'] }} {{ $requirement['submission_count'] == 1 ? 'submission' : 'submissions' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <span class="px-3 py-1 text-sm bg-green-100 text-green-800 font-semibold rounded-full">
                                                {{ $requirement['submission_count'] }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <button 
                                                wire:click="selectRequirement({{ $requirement['id'] }})"
                                                class="flex items-center gap-2 px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                Open
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-semibold text-gray-500">No requirements found</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <!-- Grid View for Requirements -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                @forelse ($requirements as $requirement)
                                    <div class="bg-white border-2 border-gray-200 rounded-xl p-5 hover:border-green-500 hover:shadow-md transition-all duration-200 group">
                                        <div class="flex flex-col h-full">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex items-center gap-3">
                                                    <i class="fa-solid fa-folder text-green-600 text-3xl group-hover:scale-110 transition-transform"></i>
                                                </div>
                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 font-semibold rounded-full">
                                                    {{ $requirement['submission_count'] }}
                                                </span>
                                            </div>
                                            
                                            <div class="flex-1 mb-4">
                                                <h3 class="font-semibold text-gray-800 text-lg mb-1 line-clamp-2" title="{{ $requirement['name'] }}">
                                                    {{ $requirement['name'] }}
                                                </h3>
                                                <p class="text-sm text-gray-500">
                                                    {{ $requirement['submission_count'] }} {{ $requirement['submission_count'] == 1 ? 'submission' : 'submissions' }}
                                                </p>
                                            </div>
                                            
                                            <button 
                                                wire:click="selectRequirement({{ $requirement['id'] }})"
                                                class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors group-hover:shadow-sm"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                Open Folder
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                        <i class="fas fa-folder-open text-8xl text-gray-300 mb-6"></i>
                                        <p class="text-xl font-semibold text-gray-500 mb-2">No requirements found</p>
                                        @if($search)
                                            <p class="text-amber-500">Try adjusting your search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                    <!-- LEVEL 2: Users List for Selected Requirement -->
                    @elseif($selectedRequirementId && !$selectedUserId)
                        @if($viewMode === 'list')
                            <!-- List View for Users -->
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-6 flex items-center gap-2">
                                        <i class="fas fa-user text-blue-600"></i>
                                        <span>User Information</span>
                                    </div>
                                    <div class="col-span-2 text-center">Courses</div>
                                    <div class="col-span-2 text-center">Submissions</div>
                                    <div class="col-span-2 text-center">Actions</div>
                                </div>
                                
                                @forelse ($usersForRequirement as $userData)
                                    @php $user = $userData['user']; @endphp
                                    <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 hover:bg-blue-50 transition-colors">
                                        <div class="col-span-6 flex items-center gap-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="font-semibold text-gray-800 truncate">{{ $user->full_name }}</span>
                                                <span class="text-sm text-gray-500 truncate">{{ $user->email }}</span>
                                                @if($user->college)
                                                    <span class="text-xs text-gray-400">{{ $user->college->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 font-semibold rounded-full">
                                                {{ $userData['course_count'] }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <span class="px-3 py-1 text-sm bg-green-100 text-green-800 font-semibold rounded-full">
                                                {{ $userData['submission_count'] }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <button 
                                                wire:click="selectUser({{ $user->id }})"
                                                class="flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                View Courses
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-users text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-semibold text-gray-500">No users found for this requirement</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <!-- Grid View for Users -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                @forelse ($usersForRequirement as $userData)
                                    @php $user = $userData['user']; @endphp
                                    <div class="bg-white border-2 border-gray-200 rounded-xl p-5 hover:border-blue-500 hover:shadow-md transition-all duration-200 group">
                                        <div class="flex flex-col h-full">
                                            <div class="flex items-start justify-between mb-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-user text-blue-600 text-xl"></i>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col items-end gap-1">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 font-semibold rounded-full">
                                                        {{ $userData['course_count'] }} courses
                                                    </span>
                                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 font-semibold rounded-full">
                                                        {{ $userData['submission_count'] }} submissions
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-1 mb-4">
                                                <h3 class="font-semibold text-gray-800 text-lg mb-1">{{ $user->full_name }}</h3>
                                                <p class="text-sm text-gray-500 truncate mb-1">{{ $user->email }}</p>
                                                @if($user->college)
                                                    <p class="text-xs text-gray-400">{{ $user->college->name }}</p>
                                                @endif
                                            </div>
                                            
                                            <button 
                                                wire:click="selectUser({{ $user->id }})"
                                                class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors group-hover:shadow-sm"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                View Courses
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                        <i class="fas fa-users text-8xl text-gray-300 mb-6"></i>
                                        <p class="text-xl font-semibold text-gray-500 mb-2">No users found</p>
                                        @if($search)
                                            <p class="text-amber-500">Try adjusting your search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                    <!-- LEVEL 3: Courses List for Selected User and Requirement -->
                    @elseif($selectedRequirementId && $selectedUserId && !$selectedCourseId)
                        @if($viewMode === 'list')
                            <!-- List View for Courses -->
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-8 flex items-center gap-2">
                                        <i class="fas fa-book text-purple-600"></i>
                                        <span>Course Information</span>
                                    </div>
                                    <div class="col-span-2 text-center">Status</div>
                                    <div class="col-span-2 text-center">Actions</div>
                                </div>
                                
                                @forelse ($coursesForUserRequirement as $courseData)
                                    @php $course = $courseData['course']; @endphp
                                    <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 hover:bg-purple-50 transition-colors">
                                        <div class="col-span-8 flex items-center gap-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-book text-purple-600"></i>
                                                </div>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="font-semibold text-gray-800">{{ $course->course_code }}</span>
                                                <span class="text-sm text-gray-500 truncate">{{ $course->course_name }}</span>
                                            </div>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            @php
                                                $badgeClass = match($courseData['status']) {
                                                    'submitted' => 'bg-green-100 text-green-800',
                                                    'under_review' => 'bg-yellow-100 text-yellow-800',
                                                    'revision_needed' => 'bg-orange-100 text-orange-800',
                                                    'rejected' => 'bg-red-100 text-red-800',
                                                    'approved' => 'bg-blue-100 text-blue-800',
                                                    default => 'bg-gray-100 text-gray-500'
                                                };
                                                $statusText = match($courseData['status']) {
                                                    'submitted' => 'Submitted',
                                                    'under_review' => 'Under Review',
                                                    'revision_needed' => 'Revision Needed',
                                                    'rejected' => 'Rejected',
                                                    'approved' => 'Approved',
                                                    default => 'Not Submitted'
                                                };
                                            @endphp
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <button 
                                                wire:click="selectCourse({{ $course->id }})"
                                                class="flex items-center gap-2 px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                                            >
                                                <i class="fas fa-eye"></i>
                                                View Files
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-book text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-semibold text-gray-500">No courses found for this user</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <!-- Grid View for Courses -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                @forelse ($coursesForUserRequirement as $courseData)
                                    @php $course = $courseData['course']; @endphp
                                    <div class="bg-white border-2 border-gray-200 rounded-xl p-5 hover:border-purple-500 hover:shadow-md transition-all duration-200 group">
                                        <div class="flex flex-col h-full">
                                            <div class="flex items-start justify-between mb-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-book text-purple-600 text-xl"></i>
                                                    </div>
                                                </div>
                                                @php
                                                    $badgeClass = match($courseData['status']) {
                                                        'submitted' => 'bg-green-100 text-green-800',
                                                        'under_review' => 'bg-yellow-100 text-yellow-800',
                                                        'revision_needed' => 'bg-orange-100 text-orange-800',
                                                        'rejected' => 'bg-red-100 text-red-800',
                                                        'approved' => 'bg-blue-100 text-blue-800',
                                                        default => 'bg-gray-100 text-gray-500'
                                                    };
                                                    $statusText = match($courseData['status']) {
                                                        'submitted' => 'Submitted',
                                                        'under_review' => 'Under Review',
                                                        'revision_needed' => 'Revision Needed',
                                                        'rejected' => 'Rejected',
                                                        'approved' => 'Approved',
                                                        default => 'Not Submitted'
                                                    };
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                                    {{ $statusText }}
                                                </span>
                                            </div>
                                            
                                            <div class="flex-1 mb-4">
                                                <h3 class="font-semibold text-gray-800 text-lg mb-1">{{ $course->course_code }}</h3>
                                                <p class="text-sm text-gray-500 line-clamp-2">{{ $course->course_name }}</p>
                                            </div>
                                            
                                            <button 
                                                wire:click="selectCourse({{ $course->id }})"
                                                class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors group-hover:shadow-sm"
                                            >
                                                <i class="fas fa-eye"></i>
                                                View Submission
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                        <i class="fas fa-book text-8xl text-gray-300 mb-6"></i>
                                        <p class="text-xl font-semibold text-gray-500 mb-2">No courses found</p>
                                        @if($search)
                                            <p class="text-amber-500">Try adjusting your search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        @else
            <div class="flex items-center p-4 bg-[#DEF4C6] text-[#1B512D] rounded-lg shadow-lg">
                <i class="fa-solid fa-triangle-exclamation text-lg mr-3"></i>
                <div>
                    <h3 class="font-bold">No Active Semester</h3>
                    <div class="text-xs">Please activate a semester to view submitted requirements.</div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .group:hover .folder-icon {
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            transform: translateY(-2px);
        }
        
        /* Smooth transitions for all interactive elements */
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
    </style>
</div>