<div>
    @php
        use App\Models\SubmittedRequirement;
        use App\Models\RequirementSubmissionIndicator;
    @endphp

    <!-- Two Column Layout -->
    <div class="flex flex-col lg:flex-row gap-3 w-full">
        <!-- Left: File Manager -->
        <div class="{{ (!$selectedFile && $showSemesterPanel) || $selectedFile ? 'lg:w-3/4' : 'w-full' }} h-[calc(100vh-6rem)] overflow-y-auto min-h-[calc(100vh-6rem)]" style="padding-right: 10px;">
            <!-- HEADER -->
            <div class="flex justify-between items-center text-white p-4 rounded-xl shadow-md mb-2" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <div class="flex items-center gap-3">
                    <div class="pl-3 bg-1C7C54/10 rounded-xl">
                        <i class="fa-solid fa-file text-white text-2xl"></i>
                    </div>
                    <h2 class="text-xl md:text-xl font-semibold">File Manager</h2>

                    <!-- Current Status -->
                    @if($activeSemester)
                        <span class="ml-3 px-4 py-1.5 rounded-full text-sm font-semibold bg-white/20 text-white">
                            {{ $selectedSemester ? 'Viewing: ' : 'Current Semester: ' }}{{ $activeSemester->name }}
                        </span>
                    @else
                        <span class="ml-3 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            No Active Semester
                        </span>
                    @endif
                </div>

                <!-- Right Controls: View toggle + Semester toggle -->
                <div class="flex items-center gap-2">
                    <!-- View toggle buttons -->
                    <div class="flex items-center gap-1 bg-white/20 p-1 rounded-xl">
                        <!-- List Toggle -->
                        <button 
                            wire:click="setViewMode('list')" 
                            class="p-2 rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                            title="List view"
                        >
                            <i class="fas fa-bars"></i>
                        </button>
                        <!-- Grid Toggle -->
                        <button 
                            wire:click="setViewMode('grid')" 
                            class="p-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                            title="Grid view"
                        >
                            <i class="fas fa-border-all"></i>
                        </button>
                    </div>

                    <!-- Semester Toggle -->
                    <button 
                        type="button" 
                        class="px-3 py-2 rounded-xl bg-white text-1C7C54 font-medium text-sm shadow-sm hover:bg-73E2A7 transition flex items-center gap-2"
                        wire:click="togglePanel"
                        title="{{ $showSemesterPanel ? 'Hide Semester Panel' : 'Show Semester Panel' }}">
                        <span>Semester</span>
                        <i class="fas fa-chevron-{{ $showSemesterPanel ? 'left' : 'right' }} text-xs"></i>
                    </button>
                </div>
            </div>
            <!-- END HEADER -->

            <div class="w-full bg-white shadow-md rounded-xl p-6 min-h-[calc(100vh-10rem)]">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4  pb-4">
                    <!-- Left: Search -->
                    <div class="flex items-center w-full md:w-auto">
                        <div class="relative max-w-md w-full md:w-sm">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-gray-500 text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                class="block w-sm p-2 pl-9 text-sm text-1B512D border border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 rounded-xl" 
                                placeholder="
                                    @if($category === 'requirement')
                                        @if(!$selectedRequirementId)
                                            Search requirements...
                                        @elseif($selectedRequirementId && !$selectedUserId)
                                            Search users...
                                        @elseif($selectedRequirementId && $selectedUserId && !$selectedCourseId)
                                            Search courses...
                                        @else
                                            Search files...
                                        @endif
                                    @elseif($category === 'user')
                                        @if(!$selectedUserId)
                                            Search users...
                                        @elseif($selectedUserId && !$selectedCourseId)
                                            Search courses...
                                        @elseif($selectedUserId && $selectedCourseId && !$selectedRequirementId)
                                            Search requirements...
                                        @else
                                            Search files...
                                        @endif
                                    @else
                                        Search files or users...
                                    @endif
                                "
                            >
                        </div>
                    </div>

                    <!-- Right: Category Buttons -->
                    <div class="ml-auto border border-gray-300 shadow-sm rounded-xl bg-white font-semibold p-1">
                        <!-- Inner container with vertical dividers -->
                        <div class="flex flex-wrap items-center divide-x divide-gray-300 overflow-hidden rounded-lg">
                            <button
                                wire:click="setCategory('requirement')"
                                class="px-4 py-2 text-sm font-semibold transition-colors 
                                    {{ $category === 'requirement' ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-green-600/20 hover:text-1B512D' }}"
                            >
                                <i class="fa-solid fa-folder mr-2"></i>
                                Requirement
                            </button>
                            <button
                                wire:click="setCategory('user')"
                                class="px-4 py-2 text-sm font-semibold transition-colors 
                                    {{ $category === 'user' ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-green-600/20 hover:text-1B512D' }}"
                            >
                                <i class="fa-solid fa-user mr-2"></i>
                                User
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Breadcrumb Navigation --}}
                @if(count($breadcrumbs) > 0)
                <div class="flex items-center text-sm text-green-600 bg-green-50 border border-green-600 rounded-xl p-4 overflow-x-auto whitespace-nowrap scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent mb-4">
                    @foreach($breadcrumbs as $index => $crumb)
                        @if(!$loop->last)
                            <button 
                                wire:click="goBack('{{ $crumb['type'] }}')"
                                class="font-semibold hover:text-amber-500 hover:underline hover:underline-offset-4 {{ $crumb['type'] === 'file' ? 'cursor-default' : '' }} max-w-[200px] truncate"
                                title="{{ $crumb['name'] }}"
                                {{ $crumb['type'] === 'file' ? 'disabled' : '' }}
                            >
                                {{ $crumb['name'] }}
                            </button>
                            <span class="mx-2">
                                <i class="fa-regular fa-chevron-right text-xs text-gray-300 mb-2"></i>
                            </span>
                        @else
                            <span 
                                class="text-green-600 font-semibold max-w-[250px] truncate hover:text-amber-500 hover:underline hover:underline-offset-4" 
                                title="{{ $crumb['name'] }}">
                                {{ $crumb['name'] }}
                            </span>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- REQUIREMENTS CATEGORY HIERARCHY --}}
                @if($category === 'requirement' || !$category)
                    <!-- LEVEL 1: Requirements List -->
                    @if(!$selectedRequirementId)
                        @if($viewMode === 'list')
                            <!-- List View for Requirements -->
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-8 flex items-center gap-2">
                                        <i class="fas fa-folder text-green-600"></i>
                                        <span>Requirement Name</span>
                                    </div>
                                    <div class="col-span-2 text-center">Submitted Files</div>
                                    <div class="col-span-2 text-center">Actions</div>
                                </div>
                                
                                @forelse ($requirements as $requirement)
                                    <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 hover:bg-green-50 transition-colors">
                                        <div class="col-span-8 flex items-center gap-3">
                                            <i class="fas fa-folder text-green-600 text-xl"></i>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-gray-800">{{ $requirement['name'] }}</span>
                                                <span class="text-xs text-gray-500">{{ $requirement['file_count'] }} submitted {{ $requirement['file_count'] == 1 ? 'file' : 'files' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <span class="px-3 py-1 text-sm bg-green-100 text-green-800 font-semibold rounded-full">
                                                {{ $requirement['file_count'] }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <button 
                                                wire:click="selectRequirement({{ $requirement['id'] }})"
                                                class="flex items-center gap-2 px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                Open
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-semibold text-gray-500">No submitted requirements found</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @else
                                            <p class="text-sm text-gray-500 mt-2">No requirements have been marked as submitted yet</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <!-- Grid View for Requirements -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @forelse ($requirements as $requirement)
                                    <div 
                                        wire:click="selectRequirement({{ $requirement['id'] }})"
                                        wire:key="requirement-{{ $requirement['id'] }}"
                                        class="cursor-pointer group"
                                    >
                                        <div class="bg-white border-2 border-gray-200 rounded-xl p-3 transition-all duration-200 group-hover:shadow-md group-hover:border-green-600 group-hover:border-2 group-hover:translate-y-[-2px] h-auto flex flex-col">
                                            
                                            <div class="flex items-start justify-between flex-1">
                                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                                    <div class="flex-shrink-0">
                                                        <i class="fa-solid fa-folder text-green-700 text-4xl"></i>
                                                    </div>
                                                    <div>
                                                        <h3 class="font-semibold text-gray-800 text-sm truncate">
                                                            {{ $requirement['name'] }}
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                        <i class="fas fa-folder-open text-8xl text-gray-300 mb-6"></i>
                                        <p class="text-xl font-semibold text-gray-500 mb-2">No submitted requirements found</p>
                                        @if($search)
                                            <p class="text-amber-500">Try adjusting your search term</p>
                                        @else
                                            <p class="text-gray-500">No requirements have been marked as submitted yet</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                    <!-- LEVEL 2: Users List for Selected Requirement -->
                    @elseif($selectedRequirementId && !$selectedUserId)
                        @if($viewMode === 'list')
                            <!-- List View for Users -->
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-6 flex items-center gap-2">
                                        <i class="fas fa-user text-blue-600"></i>
                                        <span>User Information</span>
                                    </div>
                                    <div class="col-span-2 text-center">Courses</div>
                                    <div class="col-span-2 text-center">Submitted Files</div>
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
                                            </div>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 font-semibold rounded-full">
                                                {{ $userData['course_count'] }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <span class="px-3 py-1 text-sm bg-green-100 text-green-800 font-semibold rounded-full">
                                                {{ $userData['file_count'] }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <button 
                                                wire:click="selectUser({{ $user->id }})"
                                                class="flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                View Courses
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-users text-3xl text-gray-300 mb-4"></i>
                                        <p class="text-sm font-semibold text-gray-500">No users have submitted this requirement yet</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <!-- Grid View for Users -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @forelse ($usersForRequirement as $userData)
                                    @php $user = $userData['user']; @endphp
                                    <div 
                                        wire:click="selectUser({{ $user->id }})"
                                        wire:key="user-{{ $user->id }}-req-{{ $selectedRequirementId }}"
                                        class="cursor-pointer group"
                                    >
                                        <div class="bg-white border-2 border-gray-200 rounded-xl p-3 transition-all duration-200 group-hover:shadow-md group-hover:border-blue-600 group-hover:border-2 group-hover:translate-y-[-2px] h-auto flex flex-col">
                                            
                                            <!-- Logo + Text in flex -->
                                            <div class="flex items-start justify-between flex-1">
                                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-user text-blue-600 text-xl"></i>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <h3 class="font-semibold text-gray-800 text-md truncate">
                                                            {{ $user->full_name }}
                                                        </h3>
                                                        <p class="text-xs text-gray-500 mt-1 truncate">
                                                            {{ $user->email }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col items-end gap-1 flex-shrink-0 ml-2">
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                                        {{ $userData['course_count'] }} courses
                                                    </span>
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                        {{ $userData['file_count'] }} files
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                        <i class="fas fa-users text-8xl text-gray-300 mb-6"></i>
                                        <p class="text-xl font-semibold text-gray-500 mb-2">No users found</p>
                                        @if($search)
                                            <p class="text-amber-500">Try adjusting your search term</p>
                                        @else
                                            <p class="text-gray-500">No users have submitted this requirement yet</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                    <!-- LEVEL 3: Courses List for Selected User and Requirement -->
                    @elseif($selectedRequirementId && $selectedUserId && !$selectedCourseId)
                        @if($viewMode === 'list')
                            <!-- List View for Courses -->
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-10 flex items-center gap-2">
                                        <i class="fas fa-book text-purple-600"></i>
                                        <span>Course Information</span>
                                    </div>
                                    <div class="col-span-2 text-center">Actions</div>
                                </div>
                                
                                @forelse ($coursesForUserRequirement as $courseData)
                                    @php $course = $courseData['course']; @endphp
                                    <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 hover:bg-purple-50 transition-colors">
                                        <div class="col-span-10 flex items-center gap-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-book text-purple-600"></i>
                                                </div>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="font-semibold text-gray-800">{{ $course->course_code }}</span>
                                                <span class="text-sm text-gray-500 truncate">{{ $course->course_name }}</span>
                                                <span class="text-xs text-gray-400 mt-1">{{ $courseData['file_count'] }} submitted files</span>
                                            </div>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <button 
                                                wire:click="selectCourse({{ $course->id }})"
                                                class="flex items-center gap-2 px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors"
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
                                        @else
                                            <p class="text-sm text-gray-500 mt-2">This user hasn't submitted this requirement for any courses</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <!-- Grid View for Courses -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @forelse ($coursesForUserRequirement as $courseData)
                                    @php $course = $courseData['course']; @endphp
                                    <div 
                                        wire:click="selectCourse({{ $course->id }})"
                                        wire:key="course-{{ $course->id }}-user-{{ $selectedUserId }}-req-{{ $selectedRequirementId }}"
                                        class="cursor-pointer group"
                                    >
                                        <div class="bg-white border-2 border-gray-200 rounded-xl p-3 transition-all duration-200 group-hover:shadow-md group-hover:border-purple-600 group-hover:border-2 group-hover:translate-y-[-2px] h-auto flex flex-col">
                                            
                                            <!-- Logo + Text in flex -->
                                            <div class="flex items-start justify-between flex-1">
                                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-book text-purple-600 text-xl"></i>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <h3 class="font-semibold text-gray-800 text-md truncate">
                                                            {{ $course->course_code }}
                                                        </h3>
                                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                            {{ $course->course_name }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex-shrink-0 ml-2">
                                                    {{ $courseData['file_count'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                        <i class="fas fa-book text-8xl text-gray-300 mb-6"></i>
                                        <p class="text-xl font-semibold text-gray-500 mb-2">No courses found</p>
                                        @if($search)
                                            <p class="text-amber-500">Try adjusting your search term</p>
                                        @else
                                            <p class="text-gray-500">This user hasn't submitted this requirement for any courses</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                    <!-- LEVEL 4: Files for Selected Course, User and Requirement -->
                    @elseif($selectedRequirementId && $selectedUserId && $selectedCourseId)
                        <!-- Files Display -->
                        {{-- GRID VIEW FOR FILES --}}
                        @if ($viewMode === 'grid')
                            <div class="grid gap-6 mt-6"
                                style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
                                @forelse ($files as $media)
                                    @php
                                        $submittedRequirement = $media->model;
                                        $extension     = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                                        $fileIcon      = SubmittedRequirement::FILE_ICONS[$extension]['icon'] ?? SubmittedRequirement::FILE_ICONS['default']['icon'];
                                        $fileColor     = SubmittedRequirement::FILE_ICONS[$extension]['color'] ?? SubmittedRequirement::FILE_ICONS['default']['color'];

                                        $isImage       = str_starts_with($media->mime_type, 'image/');
                                        $isPdf         = $media->mime_type === 'application/pdf';
                                        $isOfficeDoc   = in_array($extension, ['doc','docx','xls','xlsx','ppt','pptx']);
                                        $isPreviewable = $isImage || $isPdf || $isOfficeDoc;

                                        $fileUrl       = route('file.preview', [
                                            'submission' => $media->model_id,
                                            'file'       => $media->id
                                        ]);
                                    @endphp

                                    <!-- File Card -->
                                    <div 
                                        class="file-card group relative flex flex-col bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all cursor-pointer"
                                        wire:click="selectFile('{{ $media->id }}')"
                                        ondblclick="window.open('{{ $fileUrl }}', '_blank')"
                                    >
                                        <!-- File Preview -->
                                        <div class="h-40 w-full bg-gray-50 flex items-center justify-center overflow-hidden rounded-t-2xl relative">
                                            @if ($isImage)
                                                <img src="{{ $fileUrl }}" alt="{{ $media->file_name }}"
                                                    class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105">
                                            @elseif ($isPdf)
                                                <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=0"
                                                        class="w-full h-full transition-transform duration-200 group-hover:scale-105 rounded-t-2xl"
                                                        frameborder="0"></iframe>
                                            @elseif ($isOfficeDoc)
                                                <iframe src="https://docs.google.com/gview?url={{ urlencode($media->getUrl()) }}&embedded=true"
                                                        class="w-full h-full rounded-t-2xl"
                                                        frameborder="0"></iframe>
                                            @else
                                                <div class="flex flex-col items-center justify-center text-center p-4">
                                                    <i class="fa-solid {{ $fileIcon }} {{ $fileColor }} text-4xl mb-2"></i>
                                                    <p class="text-xs text-gray-600 font-semibold">{{ strtoupper($extension) }} File</p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- File Info -->
                                        <div class="p-4 flex-1 flex flex-col">
                                            <div class="truncate text-sm font-semibold text-gray-900">{{ $media->file_name }}</div>
                                            <div class="text-xs text-gray-500 font-medium">
                                                @if ($media->model && $media->model->user)
                                                    {{ $media->model->user->full_name }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 mt-auto">
                                                @if ($media->size >= 1048576)
                                                    {{ number_format($media->size / 1048576, 1) }} MB
                                                @elseif ($media->size >= 1024)
                                                    {{ number_format($media->size / 1024, 1) }} KB
                                                @else
                                                    {{ $media->size }} B
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <!-- Empty State -->
                                    <div class="col-span-full text-center text-gray-500 py-10">
                                        <i class="fa-solid fa-folder-open text-3xl text-gray-300 mb-2"></i>
                                        <p class="text-sm font-semibold text-gray-500">No files found.</p>
                                        @if($search)
                                            <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                        <!-- LIST VIEW FOR FILES -->
                        @if($viewMode === 'list')
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-5 flex items-center gap-2">
                                        <i class="fas fa-file text-green-600"></i>
                                        <span>File Information</span>
                                    </div>
                                    <div class="col-span-4 text-center">Uploaded By</div>
                                    <div class="col-span-2 text-center">Date</div>
                                    <div class="col-span-1 text-center">Size</div>
                                </div>
                                
                                @forelse ($files as $media)
                                    @php
                                        $submittedRequirement = $media->model;
                                        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                                        $extension = strtolower($extension);
                                        $fileIcon = SubmittedRequirement::FILE_ICONS[$extension]['icon'] ?? SubmittedRequirement::FILE_ICONS['default']['icon'];
                                        $fileColor = SubmittedRequirement::FILE_ICONS[$extension]['color'] ?? SubmittedRequirement::FILE_ICONS['default']['color'];
                                        
                                        $fileUrl = route('file.preview', [
                                            'submission' => $media->model_id,
                                            'file' => $media->id
                                        ]);
                                    @endphp
                                    <div 
                                        wire:click="selectFile('{{ $media->id }}')"
                                        ondblclick="window.open('{{ $fileUrl }}', '_blank')"
                                        class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 hover:bg-green-50 transition-colors cursor-pointer items-center">
                                        
                                        <!-- File Icon & Name -->
                                        <div class="col-span-5 flex items-center gap-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <i class="fas {{ $fileIcon }} {{ $fileColor }}"></i>
                                                </div>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="font-semibold text-gray-800 truncate" title="{{ $media->file_name }}">
                                                    {{ $media->file_name }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ strtoupper($extension) }} • 
                                                    @if($media->size >= 1048576)
                                                        {{ number_format($media->size / 1048576, 1) }} MB
                                                    @elseif($media->size >= 1024)
                                                        {{ number_format($media->size / 1024, 1) }} KB
                                                    @else
                                                        {{ $media->size }} B
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Uploaded By -->
                                        <div class="col-span-4 text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="text-sm text-gray-800 font-medium">
                                                    @if($media->model && $media->model->user)
                                                        {{ $media->model->user->full_name }}
                                                    @else
                                                        Unknown
                                                    @endif
                                                </span>
                                                @if($media->model && $media->model->user && $media->model->user->college)
                                                    <span class="text-xs text-gray-500 mt-1">
                                                        {{ $media->model->user->college->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Date -->
                                        <div class="col-span-2 text-center text-sm text-gray-600">
                                            {{ $media->created_at->format('M d, Y') }}
                                        </div>
                                        
                                        <!-- Size -->
                                        <div class="col-span-1 text-center">
                                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 font-medium rounded-full">
                                                @if($media->size >= 1048576)
                                                    {{ number_format($media->size / 1048576, 1) }}MB
                                                @elseif($media->size >= 1024)
                                                    {{ number_format($media->size / 1024, 1) }}KB
                                                @else
                                                    {{ $media->size }}B
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-semibold text-gray-500">No files found</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @else
                                            <p class="text-sm text-gray-500 mt-2">No files match the current filters</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                        {{-- Pagination --}}
                        @if($files->hasPages())
                        <div class="mt-4">
                           {{ $files->links('livewire.pagination') }}
                        </div>
                        @endif
                    @endif

                {{-- USERS CATEGORY HIERARCHY --}}
                @elseif($category === 'user')
                    <!-- LEVEL 1: Users List -->
                    @if(!$selectedUserId)
                        @if($viewMode === 'list')
                            <!-- List View for Users -->
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-6 flex items-center gap-2">
                                        <i class="fas fa-user text-blue-600"></i>
                                        <span>User Information</span>
                                    </div>
                                    <div class="col-span-2 text-center">Courses</div>
                                    <div class="col-span-2 text-center">Submitted Files</div>
                                    <div class="col-span-2 text-center">Actions</div>
                                </div>
                                
                                @forelse ($users as $userData)
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
                                            </div>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 font-semibold rounded-full">
                                                {{ $userData['course_count'] }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <span class="px-3 py-1 text-sm bg-green-100 text-green-800 font-semibold rounded-full">
                                                {{ $userData['file_count'] }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <button 
                                                wire:click="selectUser({{ $user->id }})"
                                                class="flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                View Courses
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-users text-3xl text-gray-300 mb-4"></i>
                                        <p class="text-sm font-semibold text-gray-500">No users with submitted requirements found</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @else
                                            <p class="text-sm text-gray-500 mt-2">No users have submitted any requirements yet</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <!-- Grid View for Users -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @forelse ($users as $userData)
                                    @php $user = $userData['user']; @endphp
                                    <div 
                                        wire:click="selectUser({{ $user->id }})"
                                        wire:key="user-{{ $user->id }}"
                                        class="cursor-pointer group"
                                    >
                                        <div class="bg-white border-2 border-gray-200 rounded-xl p-3 transition-all duration-200 group-hover:shadow-md group-hover:border-blue-600 group-hover:border-2 group-hover:translate-y-[-2px] h-auto flex flex-col">
                                            
                                            <!-- Logo + Text in flex -->
                                            <div class="flex items-start justify-between flex-1">
                                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-user text-blue-600 text-xl"></i>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <h3 class="font-semibold text-gray-800 text-md truncate">
                                                            {{ $user->full_name }}
                                                        </h3>
                                                        <p class="text-xs text-gray-500 mt-1 truncate">
                                                            {{ $user->email }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col items-end gap-1 flex-shrink-0 ml-2">
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                                        {{ $userData['course_count'] }} courses
                                                    </span>
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                        {{ $userData['file_count'] }} files
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                        <i class="fas fa-users text-8xl text-gray-300 mb-6"></i>
                                        <p class="text-xl font-semibold text-gray-500 mb-2">No users found</p>
                                        @if($search)
                                            <p class="text-amber-500">Try adjusting your search term</p>
                                        @else
                                            <p class="text-gray-500">No users have submitted any requirements yet</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                    <!-- LEVEL 2: Courses List for Selected User -->
                    @elseif($selectedUserId && !$selectedCourseId)
                        @if($viewMode === 'list')
                            <!-- List View for Courses -->
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-8 flex items-center gap-2">
                                        <i class="fas fa-book text-purple-600"></i>
                                        <span>Course Information</span>
                                    </div>
                                    <div class="col-span-2 text-center">Submitted Files</div>
                                    <div class="col-span-2 text-center">Actions</div>
                                </div>
                                
                                @forelse ($coursesForUser as $courseData)
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
                                            <span class="px-3 py-1 text-sm bg-green-100 text-green-800 font-semibold rounded-full">
                                                {{ $courseData['file_count'] }}
                                            </span>
                                        </div>
                                            <button 
                                                wire:click="selectCourse({{ $course->id }})"
                                                class="flex m-1 col-span-2 items-center justify-center gap-2 px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                <span>View Requirements</span>
                                            </button>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-book text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-semibold text-gray-500">No courses found for this user</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @else
                                            <p class="text-sm text-gray-500 mt-2">This user hasn't submitted any requirements for courses</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <!-- Grid View for Courses -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @forelse ($coursesForUser as $courseData)
                                    @php $course = $courseData['course']; @endphp
                                    <div 
                                        wire:click="selectCourse({{ $course->id }})"
                                        wire:key="course-{{ $course->id }}-user-{{ $selectedUserId }}"
                                        class="cursor-pointer group"
                                    >
                                        <div class="bg-white border-2 border-gray-200 rounded-xl p-3 transition-all duration-200 group-hover:shadow-md group-hover:border-purple-600 group-hover:border-2 group-hover:translate-y-[-2px] h-auto flex flex-col">
                                            
                                            <!-- Logo + Text in flex -->
                                            <div class="flex items-start justify-between flex-1">
                                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-book text-purple-600 text-xl"></i>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <h3 class="font-semibold text-gray-800 text-md truncate">
                                                            {{ $course->course_code }}
                                                        </h3>
                                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                            {{ $course->course_name }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex-shrink-0 ml-2">
                                                    {{ $courseData['file_count'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                        <i class="fas fa-book text-8xl text-gray-300 mb-6"></i>
                                        <p class="text-xl font-semibold text-gray-500 mb-2">No courses found</p>
                                        @if($search)
                                            <p class="text-amber-500">Try adjusting your search term</p>
                                        @else
                                            <p class="text-gray-500">This user hasn't submitted any requirements for courses</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                    <!-- LEVEL 3: Requirements List for Selected Course and User -->
                    @elseif($selectedUserId && $selectedCourseId && !$selectedRequirementId)
                        @if($viewMode === 'list')
                            <!-- List View for Requirements -->
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-8 flex items-center gap-2">
                                        <i class="fas fa-folder text-green-600"></i>
                                        <span>Requirement Name</span>
                                    </div>
                                    <div class="col-span-2 text-center">Submitted Files</div>
                                    <div class="col-span-2 text-center">Actions</div>
                                </div>
                                
                                @forelse ($requirementsForUserCourse as $requirementData)
                                    @php $requirement = $requirementData['requirement']; @endphp
                                    <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 hover:bg-green-50 transition-colors">
                                        <div class="col-span-8 flex items-center gap-3">
                                            <i class="fas fa-folder text-green-600 text-xl"></i>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-gray-800">{{ $requirement['name'] }}</span>
                                            </div>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <span class="px-3 py-1 text-sm bg-green-100 text-green-800 font-semibold rounded-full">
                                                {{ $requirementData['file_count'] }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 flex items-center justify-center">
                                            <button 
                                                wire:click="selectRequirement({{ $requirement['id'] }})"
                                                class="flex items-center gap-2 px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors"
                                            >
                                                <i class="fas fa-eye"></i>
                                                View Files
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-semibold text-gray-500">No submitted requirements found</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @else
                                            <p class="text-sm text-gray-500 mt-2">This user hasn't submitted any requirements for this course</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <!-- Grid View for Requirements -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @forelse ($requirementsForUserCourse as $requirementData)
                                    @php $requirement = $requirementData['requirement']; @endphp
                                    <div 
                                        wire:click="selectRequirement({{ $requirement['id'] }})"
                                        wire:key="requirement-{{ $requirement['id'] }}-user-{{ $selectedUserId }}-course-{{ $selectedCourseId }}"
                                        class="cursor-pointer group"
                                    >
                                        <div class="bg-white border-2 border-gray-200 rounded-xl p-3 transition-all duration-200 group-hover:shadow-md group-hover:border-green-600 group-hover:border-2 group-hover:translate-y-[-2px] h-auto flex flex-col">
                                            
                                            <!-- Logo + Text in flex -->
                                            <div class="flex items-start justify-between flex-1">
                                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                                    <div class="flex-shrink-0">
                                                        <i class="fa-solid fa-folder text-green-700 text-4xl"></i>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <h3 class="font-semibold text-gray-800 text-md truncate">
                                                            {{ $requirement['name'] }}
                                                        </h3>
                                                    </div>
                                                </div>
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex-shrink-0 ml-2">
                                                    {{ $requirementData['file_count'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                        <i class="fas fa-folder-open text-8xl text-gray-300 mb-6"></i>
                                        <p class="text-xl font-semibold text-gray-500 mb-2">No requirements found</p>
                                        @if($search)
                                            <p class="text-amber-500">Try adjusting your search term</p>
                                        @else
                                            <p class="text-gray-500">This user hasn't submitted any requirements for this course</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                    <!-- LEVEL 4: Files for Selected Requirement, Course and User -->
                    @elseif($selectedUserId && $selectedCourseId && $selectedRequirementId)
                        <!-- Files Display -->
                        {{-- GRID VIEW FOR FILES --}}
                        @if ($viewMode === 'grid')
                            <div class="grid gap-6 mt-6"
                                style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
                                @forelse ($files as $media)
                                    @php
                                        $submittedRequirement = $media->model;
                                        $extension     = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                                        $fileIcon      = SubmittedRequirement::FILE_ICONS[$extension]['icon'] ?? SubmittedRequirement::FILE_ICONS['default']['icon'];
                                        $fileColor     = SubmittedRequirement::FILE_ICONS[$extension]['color'] ?? SubmittedRequirement::FILE_ICONS['default']['color'];

                                        $isImage       = str_starts_with($media->mime_type, 'image/');
                                        $isPdf         = $media->mime_type === 'application/pdf';
                                        $isOfficeDoc   = in_array($extension, ['doc','docx','xls','xlsx','ppt','pptx']);
                                        $isPreviewable = $isImage || $isPdf || $isOfficeDoc;

                                        $fileUrl       = route('file.preview', [
                                            'submission' => $media->model_id,
                                            'file'       => $media->id
                                        ]);
                                    @endphp

                                    <!-- File Card -->
                                    <div 
                                        class="file-card group relative flex flex-col bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all cursor-pointer"
                                        wire:click="selectFile('{{ $media->id }}')"
                                        ondblclick="window.open('{{ $fileUrl }}', '_blank')"
                                    >
                                        <!-- File Preview -->
                                        <div class="h-40 w-full bg-gray-50 flex items-center justify-center overflow-hidden rounded-t-2xl relative">
                                            @if ($isImage)
                                                <img src="{{ $fileUrl }}" alt="{{ $media->file_name }}"
                                                    class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105">
                                            @elseif ($isPdf)
                                                <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=0"
                                                        class="w-full h-full transition-transform duration-200 group-hover:scale-105 rounded-t-2xl"
                                                        frameborder="0"></iframe>
                                            @elseif ($isOfficeDoc)
                                                <iframe src="https://docs.google.com/gview?url={{ urlencode($media->getUrl()) }}&embedded=true"
                                                        class="w-full h-full rounded-t-2xl"
                                                        frameborder="0"></iframe>
                                            @else
                                                <div class="flex flex-col items-center justify-center text-center p-4">
                                                    <i class="fa-solid {{ $fileIcon }} {{ $fileColor }} text-4xl mb-2"></i>
                                                    <p class="text-xs text-gray-600 font-semibold">{{ strtoupper($extension) }} File</p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- File Info -->
                                        <div class="p-4 flex-1 flex flex-col">
                                            <div class="truncate text-sm font-semibold text-gray-900">{{ $media->file_name }}</div>
                                            <div class="text-xs text-gray-500 font-medium">
                                                @if ($media->model && $media->model->user)
                                                    {{ $media->model->user->full_name }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 mt-auto">
                                                @if ($media->size >= 1048576)
                                                    {{ number_format($media->size / 1048576, 1) }} MB
                                                @elseif ($media->size >= 1024)
                                                    {{ number_format($media->size / 1024, 1) }} KB
                                                @else
                                                    {{ $media->size }} B
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <!-- Empty State -->
                                    <div class="col-span-full text-center text-gray-500 py-10">
                                        <i class="fa-solid fa-folder-open text-3xl text-gray-300 mb-2"></i>
                                        <p class="text-sm font-semibold text-gray-500">No files found.</p>
                                        @if($search)
                                            <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                        <!-- LIST VIEW FOR FILES -->
                        @if($viewMode === 'list')
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                <!-- Table Header -->
                                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">
                                    <div class="col-span-5 flex items-center gap-2">
                                        <i class="fas fa-file text-green-600"></i>
                                        <span>File Information</span>
                                    </div>
                                    <div class="col-span-4 text-center">Uploaded By</div>
                                    <div class="col-span-2 text-center">Date</div>
                                    <div class="col-span-1 text-center">Size</div>
                                </div>
                                
                                @forelse ($files as $media)
                                    @php
                                        $submittedRequirement = $media->model;
                                        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                                        $extension = strtolower($extension);
                                        $fileIcon = SubmittedRequirement::FILE_ICONS[$extension]['icon'] ?? SubmittedRequirement::FILE_ICONS['default']['icon'];
                                        $fileColor = SubmittedRequirement::FILE_ICONS[$extension]['color'] ?? SubmittedRequirement::FILE_ICONS['default']['color'];
                                        
                                        $fileUrl = route('file.preview', [
                                            'submission' => $media->model_id,
                                            'file' => $media->id
                                        ]);
                                    @endphp
                                    <div 
                                        wire:click="selectFile('{{ $media->id }}')"
                                        ondblclick="window.open('{{ $fileUrl }}', '_blank')"
                                        class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 hover:bg-green-50 transition-colors cursor-pointer items-center">
                                        
                                        <!-- File Icon & Name -->
                                        <div class="col-span-5 flex items-center gap-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <i class="fas {{ $fileIcon }} {{ $fileColor }}"></i>
                                                </div>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="font-semibold text-gray-800 truncate" title="{{ $media->file_name }}">
                                                    {{ $media->file_name }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ strtoupper($extension) }} • 
                                                    @if($media->size >= 1048576)
                                                        {{ number_format($media->size / 1048576, 1) }} MB
                                                    @elseif($media->size >= 1024)
                                                        {{ number_format($media->size / 1024, 1) }} KB
                                                    @else
                                                        {{ $media->size }} B
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Uploaded By -->
                                        <div class="col-span-4 text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="text-sm text-gray-800 font-medium">
                                                    @if($media->model && $media->model->user)
                                                        {{ $media->model->user->full_name }}
                                                    @else
                                                        Unknown
                                                    @endif
                                                </span>
                                                @if($media->model && $media->model->user && $media->model->user->college)
                                                    <span class="text-xs text-gray-500 mt-1">
                                                        {{ $media->model->user->college->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Date -->
                                        <div class="col-span-2 text-center text-sm text-gray-600">
                                            {{ $media->created_at->format('M d, Y') }}
                                        </div>
                                        
                                        <!-- Size -->
                                        <div class="col-span-1 text-center">
                                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 font-medium rounded-full">
                                                @if($media->size >= 1048576)
                                                    {{ number_format($media->size / 1048576, 1) }}MB
                                                @elseif($media->size >= 1024)
                                                    {{ number_format($media->size / 1024, 1) }}KB
                                                @else
                                                    {{ $media->size }}B
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-semibold text-gray-500">No files found</p>
                                        @if($search)
                                            <p class="text-sm text-amber-500 mt-2">Try adjusting your search term</p>
                                        @else
                                            <p class="text-sm text-gray-500 mt-2">No files match the current filters</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        @endif

                        {{-- Pagination --}}
                        @if($files->hasPages())
                        <div class="mt-4">
                           {{ $files->links('livewire.pagination') }}
                        </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>

        <!-- Right: Semester Panel OR File Details -->
        @if((!$selectedFile && $showSemesterPanel) || $selectedFile)
        <div class="w-full lg:w-1/4 flex-shrink-0 ">
            <div class="sticky top-4 h-[calc(100vh-6rem)] overflow-y-auto">

                {{-- Semester Panel (when no file selected) --}}
                @if(!$selectedFile && $showSemesterPanel)
                <div class="w-full h-full">
                    @livewire('admin.file-manager.semester-view')
                </div>
                @endif

                {{-- FILE DETAILS --}}
                @if($selectedFile)
                @php
                    $submittedRequirement = $selectedFile->model;
                    $user = $submittedRequirement->user;
                    $requirement = $submittedRequirement->requirement;
                    $course = $submittedRequirement->course;
                @endphp
                <div class="bg-white rounded-xl border border-gray-200 shadow-md p-5 h-full flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-green-800 text-2xl"></i>
                            <h3 class="text-xl font-semibold text-green-800">File Details</h3>
                        </div>
                        <button wire:click="clearSelection" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                            <i class="fa-solid fa-times text-gray-600 text-sm"></i>
                        </button>
                    </div>

                    <div class="flex flex-col gap-4 text-sm">
                        <div>
                            <p class="font-semibold text-gray-800 uppercase text-xs">File Name</p>
                            <p class="text-gray-500 font-semibold break-all">{{ $selectedFile->file_name }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 uppercase text-xs">File Type</p>
                            <p class="text-gray-500 font-semibold">{{ $selectedFile->mime_type }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 uppercase text-xs">Size</p>
                            <p class="text-gray-500 font-semibold">
                                @if($selectedFile->size >= 1073741824)
                                    {{ number_format($selectedFile->size / 1073741824, 2) }} GB
                                @elseif($selectedFile->size >= 1048576)
                                    {{ number_format($selectedFile->size / 1048576, 2) }} MB
                                @elseif($selectedFile->size >= 1024)
                                    {{ number_format($selectedFile->size / 1024, 2) }} KB
                                @else
                                    {{ $selectedFile->size }} bytes
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 uppercase text-xs">Uploaded At</p>
                            <p class="text-gray-500 font-semibold">{{ $selectedFile->created_at->format('M d, Y g:i A') }}</p>
                        </div>

                        {{-- Requirement Info --}}
                        @if($requirement)
                        <div class="pt-3 border-t-2 border-gray-300 text-sm">
                            <p class="font-semibold text-gray-800 uppercase text-xs">Requirement</p>
                            <p class="text-gray-500 font-semibold">{{ $requirement->name }}</p>
                            <div class="grid grid-cols-2 gap-2 mt-4">
                                <div>
                                    <p class="text-gray-800 font-semibold uppercase text-xs">Due Date</p>
                                    <p class="text-gray-500 font-semibold text-sm">{{ $requirement->due->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-800 font-semibold uppercase text-xs">Status</p>
                                    <p class="text-gray-500 capitalize text-sm font-semibold">{{ $submittedRequirement->status }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Uploader Info --}}
                        @if($user)
                        <div class="pt-3 border-t-2 border-gray-300">
                            <p class="font-semibold text-gray-800 uppercase text-xs">Uploaded By</p>
                            <p class="text-gray-500 text-sm font-semibold">{{ $user->full_name }}</p>
                            <div class="grid grid-cols-2 gap-2 mt-4">
                                @if($user->department)
                                <div>
                                    <p class="text-gray-800 font-semibold text-xs uppercase">Department</p>
                                    <p class="text-gray-500 font-semibold text-sm">{{ $user->department->name }}</p>
                                </div>
                                @endif
                                @if($user->college)
                                <div>
                                    <p class="text-gray-800 font-semibold text-xs uppercase">College</p>
                                    <p class="text-gray-500 font-semibold text-sm">{{ $user->college->name }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Course Info --}}
                        @if($course)
                        <div class="pt-3 border-t-2 border-gray-300">
                            <p class="font-semibold text-gray-800 uppercase text-xs">Course</p>
                            <p class="text-gray-500 text-sm font-semibold">{{ $course->course_code }} - {{ $course->course_name }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2 pt-5 border-t-2 border-gray-300 mt-auto">
                        <a href="{{ $submittedRequirement->getFileUrl() }}" target="_blank"
                        class="flex-1 px-4 py-2 bg-1C7C54 text-white rounded-full text-sm font-semibold shadow-sm hover:bg-73E2A7 transition flex items-center justify-center">
                            <i class="fa-solid fa-download mr-2"></i> Download
                        </a>
                        @php
                            $isPreviewable = Str::startsWith($selectedFile->mime_type, 'image/') || 
                                            Str::startsWith($selectedFile->mime_type, 'application/pdf') ||
                                            Str::startsWith($selectedFile->mime_type, 'text/');
                        @endphp
                        @if($isPreviewable)
                        <a href="{{ route('file.preview', ['submission' => $selectedFile->model_id, 'file' => $selectedFile->id]) }}" target="_blank"
                        class="flex-1 px-4 py-2 bg-DEF4C6 text-1B512D rounded-full text-sm font-semibold shadow-sm hover:bg-B1CF5F transition flex items-center justify-center">
                            <i class="fa-solid fa-eye mr-2"></i> Open File
                        </a>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif
    </div>
</div>