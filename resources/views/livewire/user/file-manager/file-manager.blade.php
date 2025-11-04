<div class="flex flex-col w-full mx-auto min-h-screen">
    <!-- Header Section with Gap -->
    <div class="bg-white rounded-xl shadow-sm mb-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="p-6">
            <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-folder text-white text-2xl"></i>
                <h1 class="text-xl font-bold text-white">Portfolio</h1>
            </div>
            @livewire('user.dashboard.notification')
        </div>
            </div>
    </div>

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col lg:flex-row gap-3" style="max-height: calc(100vh - 140px);">
        <!-- Left Panel - File List -->
        <div class="bg-white rounded-xl overflow-auto {{ $selectedFile ? 'lg:flex-1' : 'flex-1' }}">
            <!-- Breadcrumb Section - Always Visible -->
            <div class="bg-white border-b border-gray-200 px-6 py-3 rounded-t-xl flex items-center justify-between">
                <!-- In the breadcrumb section -->
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-1">
                        @if(count($breadcrumb) > 0)
                            @foreach($breadcrumb as $index => $item)
                                <li class="flex items-center">
                                    @if($index > 0)
                                        <i class="fa-regular fa-chevron-right text-gray-300 text-xs mx-2"></i>
                                    @endif
                                    
                                    @if($index === count($breadcrumb) - 1)
                                        <button 
                                            wire:click="handleNavigation('{{ $item['level'] }}', {{ $item['id'] }})"
                                            class="text-sm text-green-600 hover:text-amber-500 hover:underline hover:underline-offset-4 font-semibold">
                                            {{ $item['name'] }}
                                        </button>
                                    @else
                                        <button 
                                            wire:click="handleNavigation('{{ $item['level'] }}', {{ $item['id'] }})"
                                            class="text-sm text-green-600 hover:text-amber-500 hover:underline hover:underline-offset-4 font-semibold">
                                            {{ $item['name'] }}
                                        </button>
                                    @endif
                                </li>
                            @endforeach
                        @else
                            <li class="flex items-center">
                                <span class="text-sm font-semibold text-green-600">Portfolio</span>
                            </li>
                        @endif
                    </ol>
                </nav>

                <!-- View Toggle - Show for relevant levels regardless of data -->
                @if($currentLevel === 'semesters' || $currentLevel === 'courses' || $currentLevel === 'parent_folders' || $currentLevel === 'parent_folder_contents')
                    <div class="flex gap-1 bg-green-700/15 p-1 rounded-xl">
                        <button
                            wire:click="changeViewMode('list')"
                            class="p-2 rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-white text-green-600' }}"
                            aria-label="List view"
                        >
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <button
                            wire:click="changeViewMode('grid')"
                            class="p-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-white text-green-600' }}"
                            aria-label="Grid view"
                        >
                            <i class="fa-solid fa-border-all"></i>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Search and Filter Section - Show for relevant levels regardless of data -->
            @if($currentLevel === 'semesters' || $currentLevel === 'courses' || $currentLevel === 'parent_folders' || $currentLevel === 'parent_folder_contents')
                <div class="bg-white px-6 py-4">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div class="flex flex-col sm:flex-row gap-4 flex-1 w-full">
                            {{-- Search Bar --}}
                            <div class="flex-1 lg:max-w-full xl:max-w-lg relative">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-500 text-sm"></i>
                                    </div>
                                    <input
                                        id="fileManagerSearch"
                                        type="text"
                                        wire:model.live.debounce.300ms="searchQuery"
                                        wire:keydown.escape="closeSearchResults"
                                        placeholder="Search semesters, courses, folders, or files..."
                                        class="block w-full p-2 pl-9 text-sm text-1B512D border border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 rounded-xl"
                                        aria-label="Search files"
                                        autocomplete="off"
                                    >
                                    <div wire:loading wire:target="searchQuery" class="absolute inset-y-2 right-0 pr-4 flex items-center">
                                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                    </div>
                                </div>

                                {{-- Search Results --}}
                                @if($showSearchResults && count($searchResults) > 0)
                                    <div class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200 max-h-96 overflow-y-auto">
                                        <div class="py-2">
                                            @foreach($searchResults as $result)
                                                <button
                                                    wire:click="selectSearchResult({{ json_encode($result) }})"
                                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 transition-colors flex items-center"
                                                    wire:key="search-result-{{ $result['type'] }}-{{ $result['id'] }}-{{ $result['course_id'] ?? '0' }}"
                                                >
                                                    <div class="flex items-center flex-1 min-w-0">
                                                        <div class="flex-shrink-0 mr-3">
                                                            <i class="fa-solid {{ $result['icon'] }} {{ $result['icon_color'] }} text-lg"></i>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $result['name'] }}</div>
                                                            <div class="text-xs text-gray-500 truncate">{{ $result['description'] }}</div>
                                                        </div>
                                                        <div class="flex-shrink-0 ml-2">
                                                            @if(isset($result['course_code']))
                                                                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full ml-1">
                                                                    {{ $result['course_code'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Clear Filters Button --}}
                            @if($searchQuery)
                                <div class="flex items-center flex-shrink-0">
                                    <button
                                        wire:click="clearFilters"
                                        class="inline-flex items-center px-4 py-2 bg-white border-2 border-green-600 text-sm font-medium rounded-xl text-gray-500 hover:bg-green-50 h-10 shadow-md"
                                    >
                                        <i class="fa-solid fa-xmark text-sm mr-2"></i>
                                        Clear Filters
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Flash Messages -->
            @if(session()->has('error'))
                <div class="px-6 py-2">
                    <div class="flex items-center p-3 bg-red-100 border border-red-200 text-red-700 rounded-lg">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span class="text-sm">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(session()->has('success'))
                <div class="px-6 py-2">
                    <div class="flex items-center p-3 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span class="text-sm">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Content Area -->
            <div class="flex-1">
                <!-- File Manager Content -->
                <div class="w-full">
                    @if($currentLevel === 'semesters')
                        @if(count($allSemesters) > 0)
                            @if($viewMode === 'grid')
                                <!-- Grid View for Semesters -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-6">
                                    @foreach($allSemesters as $semester)
                                        <div 
                                            wire:click="handleNavigation('courses', {{ $semester->id }})"
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
                                                                {{ $semester->name }}
                                                            </h3>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                {{ $semester->start_date->format('M Y') }} - {{ $semester->end_date->format('M Y') }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    @if($semester->is_active)
                                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex-shrink-0 ml-2"> Active </span>
                                                    @else
                                                        <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs font-medium rounded-full flex-shrink-0 ml-2"> Archived </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- List View for Semesters -->
                                <div class="bg-white overflow-hidden">
                                    <table class="w-full border-collapse">
                                        <thead class="bg-green-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider border-b border-green-800">
                                                    Semester
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider border-b border-green-800">
                                                    Duration
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider border-b border-green-800">
                                                    Status
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider border-b border-green-800">
                                                    Courses
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($allSemesters as $semester)
                                                <tr 
                                                    wire:click="handleNavigation('courses', {{ $semester->id }})"
                                                    class="cursor-pointer hover:bg-green-50 transition-colors"
                                                >
                                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 flex items-center justify-center mr-3">
                                                                <i class="fa-solid fa-folder text-2xl text-green-700"></i>
                                                            </div>
                                                            <div class="text-sm font-semibold text-gray-900">
                                                                {{ $semester->name }}
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                        {{ $semester->start_date->format('M Y') }} - {{ $semester->end_date->format('M Y') }}
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                        @if($semester->is_active)
                                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                                Active
                                                            </span>
                                                        @else
                                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">
                                                                Archived
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                        {{ $semester->courseAssignments ? $semester->courseAssignments->where('professor_id', auth()->id())->count() : 0 }} courses
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            <!-- No Semesters Available -->
                            <div class="p-6">
                                <div class="flex items-center p-4 bg-[#DEF4C6] text-[#1B512D] rounded-lg shadow-lg">
                                    <i class="fa-solid fa-triangle-exclamation text-lg mr-3"></i>
                                    <div>
                                        <h3 class="font-bold text-sm">No semesters available</h3>
                                        <div class="text-xs">There are no active or past semesters at this time.</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @elseif($currentLevel === 'courses')
                        @if(isset($currentSemester) && count($assignedCourses) > 0)
                            @if($viewMode === 'grid')
                                <!-- Grid View for Courses -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-6">
                                    @foreach($assignedCourses as $course)
                                        <div 
                                            wire:click="handleNavigation('parent_folders', {{ $course->id }})"
                                            class="cursor-pointer group"
                                        >
                                            <div class="bg-white border-2 border-gray-200 rounded-xl p-3 transition-all duration-200 group-hover:shadow-md group-hover:border-green-600 group-hover:translate-y-[-2px]">
                                                
                                                <!-- Logo + Text side by side -->
                                                <div class="flex items-center gap-3">
                                                    <div>
                                                        <i class="fa-solid fa-folder text-green-700 text-4xl"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="font-semibold text-gray-800 text-md truncate">
                                                            {{ $course->course_code }}
                                                        </h3>
                                                        <p class="text-xs text-gray-500 truncate">
                                                            {{ $course->course_name }}
                                                        </p>
                                                        @if($course->program)
                                                            <p class="text-xs text-green-600 font-medium mt-1 truncate">
                                                                {{ $course->program->program_name }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- List View for Courses -->
                                <div class="bg-white overflow-hidden">
                                    <table class="w-full border-collapse">
                                        <thead class="bg-green-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Course Code</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Course Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Program</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Description</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($assignedCourses as $course)
                                            <tr
                                            wire:click="handleNavigation('parent_folders', {{ $course->id }})"
                                            class="cursor-pointer hover:bg-green-50 transition-colors"
                                            >
                                            <!-- Put border on each TD (works consistently) -->
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                <div class="flex items-center">
                                                <div class="flex-shrink-0 flex items-center justify-center mr-3">
                                                    <i class="fa-solid fa-folder text-green-700 text-2xl"></i>
                                                </div>
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $course->course_code }}
                                                </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                {{ $course->course_name }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                @if($course->program)
                                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                                        {{ $course->program->program_name }}
                                                    </span>
                                                @else
                                                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">No Program</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 text-sm text-gray-500 border-b border-gray-300">
                                                {{ Str::limit($course->description, 50) }}
                                            </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            <!-- No Courses Available -->
                            <div class="text-center py-16 px-6">
                                <div class="flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-folder-open text-gray-300 text-4xl"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-800 mb-1">No courses available</h3>
                                <p class="text-gray-500 text-sm">You don't have any assigned courses for this semester.</p>
                            </div>
                        @endif

                    @elseif($currentLevel === 'parent_folders')
                        @if(isset($currentCourse) && count($parentFolders) > 0)
                            @if($viewMode === 'grid')
                                <!-- Grid View for Parent Folders -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-6">
                                    @foreach($parentFolders as $folder)
                                        <div 
                                            wire:click="handleNavigation('parent_folder_contents', {{ $folder->id }})"
                                            class="cursor-pointer group"
                                        >
                                            <div class="bg-white border-2 border-gray-200 rounded-xl p-3 transition-all duration-200 group-hover:shadow-md group-hover:border-green-600 group-hover:translate-y-[-2px]">
                                                
                                                <!-- Logo + Text side by side -->
                                                <div class="flex items-center gap-3">
                                                    <div>
                                                        <i class="fa-solid fa-folder text-green-700 text-4xl"></i>
                                                    </div>
                                                    <div>
                                                        <h3 class="font-semibold text-gray-800 text-md">
                                                            {{ $folder->name }}
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- List View for Parent Folders -->
                                <div class="bg-white overflow-hidden">
                                    <table class="w-full border-collapse">
                                        <thead class="bg-green-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Folder Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($parentFolders as $folder)
                                            <tr
                                            wire:click="handleNavigation('parent_folder_contents', {{ $folder->id }})"
                                            class="cursor-pointer hover:bg-green-50 transition-colors"
                                            >
                                            <!-- Put border on each TD (works consistently) -->
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                <div class="flex items-center">
                                                <div class="flex-shrink-0 flex items-center justify-center mr-3">
                                                    <i class="fa-solid fa-folder text-green-700 text-2xl"></i>
                                                </div>
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $folder->name }}
                                                </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                                    Parent Folder
                                                </span>
                                            </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            <!-- No Parent Folders Available -->
                            <div class="text-center py-16 px-6">
                                <div class="flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-folder-open text-gray-300 text-4xl"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-800 mb-1">No folders available</h3>
                                <p class="text-gray-500 text-sm">This course doesn't have any requirement folders yet.</p>
                            </div>
                        @endif

                    @elseif($currentLevel === 'parent_folder_contents')
                        @if(isset($currentParentFolder) && count($parentFolderContents) > 0)
                            @if($contentType === 'sub_folders')
                                <!-- Display Sub-Folders -->
                                @if($viewMode === 'grid')
                                    <!-- Grid View for Sub-Folders -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-6">
                                        @foreach($parentFolderContents as $subFolder)
                                            <div 
                                                wire:click="handleNavigation('parent_folder_contents', {{ $subFolder->id }})"
                                                class="cursor-pointer group"
                                            >
                                                <div class="bg-white border-2 border-gray-200 rounded-xl p-3 transition-all duration-200 group-hover:shadow-md group-hover:border-green-600 group-hover:translate-y-[-2px]">
                                                    
                                                    <!-- Logo + Text side by side -->
                                                    <div class="flex items-center gap-3">
                                                        <div>
                                                            <i class="fa-solid fa-folder text-green-700 text-4xl"></i>
                                                        </div>
                                                        <div>
                                                            <h3 class="font-semibold text-gray-800 text-md">
                                                                {{ $subFolder->name }}
                                                            </h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <!-- List View for Sub-Folders -->
                                    <div class="bg-white overflow-hidden">
                                        <table class="w-full border-collapse">
                                            <thead class="bg-green-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Sub-Folder Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Parent Folder</th>
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($parentFolderContents as $subFolder)
                                                <tr
                                                wire:click="handleNavigation('parent_folder_contents', {{ $subFolder->id }})"
                                                class="cursor-pointer hover:bg-green-50 transition-colors"
                                                >
                                                <!-- Put border on each TD (works consistently) -->
                                                <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                    <div class="flex items-center">
                                                    <div class="flex-shrink-0 flex items-center justify-center mr-3">
                                                        <i class="fa-solid fa-folder-tree text-green-700 text-2xl"></i>
                                                    </div>
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        {{ $subFolder->name }}
                                                    </div>
                                                    </div>
                                                </td>

                                                <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                                        Sub-folder
                                                    </span>
                                                </td>

                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                    {{ $currentParentFolder->name ?? 'Unknown' }}
                                                </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @elseif($contentType === 'files')
                                <!-- Display Submitted Files Directly -->
                                @if($viewMode === 'grid')
                                    <!-- Grid View for Files -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-6">
                                        @foreach($parentFolderContents as $submission)
                                            <div 
                                                wire:click="selectFile({{ $submission->id }})"
                                                class="cursor-pointer group file-item {{ $selectedFile && $selectedFile->id === $submission->id ? 'ring-2 ring-green-600 rounded-xl bg-green-50' : '' }}"
                                            >
                                                <div class="bg-white border-2 border-gray-200 rounded-xl p-4 transition-all duration-200 group-hover:shadow-md group-hover:border-green-600 group-hover:translate-y-[-2px]">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div>
                                                            <i class="fa-solid {{ $this->getFileIcon($submission->submissionFile->file_name ?? 'file') }} text-xl {{ $this->getFileIconColor($submission->submissionFile->file_name ?? 'file') }}"></i>
                                                        </div>
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $submission->status_badge }}">
                                                            {{ $statuses[$submission->status] ?? $submission->status }}
                                                        </span>
                                                    </div>
                                                    <h3 class="font-semibold text-gray-800 mb-1 text-sm truncate">
                                                        {{ $submission->submissionFile->file_name ?? 'Untitled' }}
                                                    </h3>
                                                    <p class="text-xs text-gray-500 mb-2">
                                                        {{ $submission->requirement_name ?? 'Unknown Requirement' }}
                                                    </p>
                                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                                        <span>{{ $this->formatFileSize($submission->submissionFile->size ?? 0) }}</span>
                                                        <span>{{ $submission->created_at->format('M d, Y') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <!-- List View for Files -->
                                    <div class="bg-white overflow-hidden">
                                        <table class="w-full border-collapse">
                                            <thead class="bg-green-700">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider border-b border-green-800">
                                                        Name
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider border-b border-green-800">
                                                        Requirement
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider border-b border-green-800">
                                                        Size
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider border-b border-green-800">
                                                        Status
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider border-b border-green-800">
                                                        Submitted
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($parentFolderContents as $submission)
                                                    <tr 
                                                        wire:click="selectFile({{ $submission->id }})"
                                                        class="cursor-pointer hover:bg-green-50 transition-colors file-item {{ $selectedFile && $selectedFile->id === $submission->id ? 'bg-green-50' : '' }}"
                                                    >
                                                        <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                            <div class="flex items-center">
                                                                <div class="flex-shrink-0 flex items-center justify-center mr-3">
                                                                    <i class="fa-solid text-2xl {{ $this->getFileIcon($submission->submissionFile->file_name ?? 'file') }} {{ $this->getFileIconColor($submission->submissionFile->file_name ?? 'file') }}"></i>
                                                                </div>
                                                                <div class="text-sm font-semibold text-gray-900">
                                                                    {{ $submission->submissionFile->file_name ?? 'Untitled' }}
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                            {{ $submission->requirement_name ?? 'Unknown' }}
                                                        </td>

                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                            {{ $this->formatFileSize($submission->submissionFile->size ?? 0) }}
                                                        </td>

                                                        <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $submission->status_badge }}">
                                                                {{ $statuses[$submission->status] ?? $submission->status }}
                                                            </span>
                                                        </td>

                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                            {{ $submission->created_at->format('M d, Y') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @endif
                        @else
                            <!-- No Contents Available -->
                            <div class="text-center py-16 px-6">
                                <div class="flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-folder-open text-gray-300 text-4xl"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-800 mb-1">
                                    @if($currentSubFolder)
                                        No contents in this sub-folder
                                    @else
                                        No contents in this folder
                                    @endif
                                </h3>
                                <p class="text-gray-500 text-sm">
                                    @if($currentSubFolder)
                                        The sub-folder "{{ $currentSubFolder->name }}" doesn't contain any files yet.
                                    @else
                                        The folder "{{ $currentParentFolder->name }}" doesn't contain any sub-folders or files yet.
                                    @endif
                                </p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Panel - File Details -->
        @if($currentLevel === 'parent_folder_contents' && $contentType === 'files' && $selectedFile)
            <div class="bg-white rounded-xl overflow-hidden lg:w-96 flex flex-col">
                <!-- File Details Header -->
                <div class="px-6 py-4" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                    <div class="flex items-center justify-between">
                        <!-- Icon + Title side by side -->
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-white text-xl"></i>
                            <h2 class="text-lg font-semibold text-white">File Details</h2>
                        </div>
                    </div>
                </div>

                <!-- File Details Content (scrollable if too long) -->
                <div class="px-6 py-5 flex-1 overflow-y-auto">
                    <!-- File Icon and Name -->
                    <div class="flex items-center gap-4 mb-10">
                        <div>
                            <i class="fa-solid {{ $this->getFileIcon($selectedFile->submissionFile->file_name ?? 'file') }} text-6xl {{ $this->getFileIconColor($selectedFile->submissionFile->file_name ?? 'file') }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 truncate">
                                {{ $selectedFile->submissionFile->file_name ?? 'Untitled' }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ $this->formatFileSize($selectedFile->submissionFile->size ?? 0) }}
                            </p>
                        </div>
                    </div>

                    <!-- File Information -->
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Status</h4>
                            <span class="px-2 py-1 text-sm font-semibold rounded-full {{ $selectedFile->status_badge }}">
                                {{ $statuses[$selectedFile->status] ?? $selectedFile->status }}
                            </span>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Course</h4>
                            <p class="text-sm text-gray-500">{{ $currentCourse->course_code ?? 'Unknown' }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Program</h4>
                            <p class="text-sm text-gray-500">
                                @if($currentCourse && $currentCourse->program)
                                    {{ $currentCourse->program->program_name }}
                                @else
                                    No Program
                                @endif
                            </p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Requirement</h4>
                            <p class="text-sm text-gray-500">{{ $selectedFile->requirement->name ?? 'Unknown' }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Folder</h4>
                            <p class="text-sm text-gray-500">
                                @if($currentSubFolder)
                                    {{ $currentSubFolder->name }} (in {{ $currentParentFolder->name }})
                                @elseif($currentParentFolder)
                                    {{ $currentParentFolder->name }}
                                @else
                                    Unknown
                                @endif
                            </p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Submitted By</h4>
                            <p class="text-sm text-gray-500">{{ $selectedFile->user->name ?? 'Unknown' }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Submitted On</h4>
                            <p class="text-sm text-gray-500">{{ $selectedFile->created_at->format('F j, Y, g:i a') }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Last Updated</h4>
                            <p class="text-sm text-gray-500">{{ $selectedFile->updated_at->format('F j, Y, g:i a') }}</p>
                        </div>

                        @if($selectedFile->status === 'rejected' && $selectedFile->feedback)
                            <div>
                                <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Feedback</h4>
                                <div class="bg-red-50 border border-red-100 rounded-lg p-3">
                                    <p class="text-sm text-red-800">{{ $selectedFile->feedback }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Requirement Folder Button - Only show for current semester files -->
                @if($this->isFileFromCurrentSemester($selectedFile))
                    <div class="px-6 py-3 border-t bg-white">
                        <a 
                            href="{{ $this->getRequirementFolderUrl($selectedFile) }}"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition-colors shadow-sm"
                        >
                            <span class="font-semibold text-sm">Open Requirement Folder</span>
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="px-6 py-4 border-t bg-white flex gap-3">
                    @if($selectedFile->submissionFile)
                        <a 
                            href="{{ $this->getDownloadRoute($selectedFile->id) }}"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm"
                            target="_blank"
                        >
                            <i class="fa-solid fa-download"></i>
                            <span class="font-semibold text-sm">Download</span>
                        </a>
                    @endif

                    @if($selectedFile->submissionFile && $this->canPreview($selectedFile->submissionFile->file_name))
                        <a 
                            href="{{ $this->getPreviewRoute($selectedFile->id) }}"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded-xl transition-colors"
                            target="_blank"
                        >
                            <i class="fa-solid fa-eye"></i>
                            <span class="font-semibold text-sm">Preview</span>
                        </a>
                    @endif

                    {{-- Delete Button (Conditionally disable) --}}
                    @if(isset($selectedFile->requirement->semester) && $selectedFile->requirement->semester->is_active && $selectedFile->status === 'uploaded')
                        <button
                            wire:click="confirmDelete({{ $selectedFile->id }})"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 border-2 border-red-600 text-red-700 hover:bg-red-50 rounded-xl transition-colors"
                        >
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    @else
                        <button
                            disabled
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 border-2 border-gray-300 text-gray-500 rounded-xl cursor-not-allowed"
                            title="{{ $selectedFile->status !== 'uploaded' ? 'Only uploaded files can be deleted' : 'Cannot delete files from archived semesters' }}"
                        >
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <x-modal name="delete-submission-confirmation-modal" :show="$showDeleteModal" maxWidth="md">
            <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete this submission?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. The submitted file will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="cancelDelete" 
                            class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteSubmission" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteSubmission">
                            <i class="fa-solid fa-trash mr-2"></i> Delete
                        </span>
                        <span wire:loading wire:target="deleteSubmission">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('scrollToFile', (fileId) => {
                // Wait for Livewire to update the DOM
                setTimeout(() => {
                    const element = document.querySelector(`[wire\\:click="selectFile(${fileId})"]`);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 100);
            });
        });

        document.addEventListener('click', function(event) {
            const searchContainer = document.querySelector('.relative.lg\\:max-w-96');
            if (searchContainer && !searchContainer.contains(event.target)) {
                @this.closeSearchResults();
            }   
        });

        // Ensure click events work for dynamically loaded content
        document.addEventListener('click', function(e) {
            // Handle course clicks
            if (e.target.closest('[wire\\:click^="handleNavigation(\'parent_folders\'"]')) {
                const element = e.target.closest('[wire\\:click^="handleNavigation(\'parent_folders\'"]');
                const match = element.getAttribute('wire:click').match(/handleNavigation\('parent_folders', (\d+)\)/);
                if (match) {
                    @this.handleNavigation('parent_folders', parseInt(match[1]));
                }
            }
            
            // Handle parent folder content clicks
            if (e.target.closest('[wire\\:click^="handleNavigation(\'parent_folder_contents\'"]')) {
                const element = e.target.closest('[wire\\:click^="handleNavigation(\'parent_folder_contents\'"]');
                const match = element.getAttribute('wire:click').match(/handleNavigation\('parent_folder_contents', (\d+)\)/);
                if (match) {
                    @this.handleNavigation('parent_folder_contents', parseInt(match[1]));
                }
            }
            
            // Handle semester folder clicks
            if (e.target.closest('[wire\\:click^="handleNavigation(\'courses\'"]')) {
                const element = e.target.closest('[wire\\:click^="handleNavigation(\'courses\'"]');
                const match = element.getAttribute('wire:click').match(/handleNavigation\('courses', (\d+)\)/);
                if (match) {
                    @this.handleNavigation('courses', parseInt(match[1]));
                }
            }
        });
    </script>
</div>