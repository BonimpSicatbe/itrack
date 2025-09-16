<div class="flex flex-col w-full mx-auto min-h-screen">
    <!-- Header Section with Gap -->
    <div class="bg-white rounded-xl shadow-sm mb-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-folder-open text-white text-2xl"></i>
                    <h1 class="text-xl font-bold text-white">File Manager</h1>
                </div>
                <div class="flex items-center gap-3">
                    {{-- File Stats --}}
                    <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-xl">
                        <i class="fa-solid fa-folder text-white text-sm"></i>
                        <span class="text-white text-sm font-medium">{{ $totalFiles }} files</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-xl">
                        <i class="fa-solid fa-hard-drive text-white text-sm"></i>
                        <span class="text-white text-sm font-medium">{{ $totalSize }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col lg:flex-row gap-3" style="max-height: calc(100vh - 125px);">
        <!-- Left Panel - File List -->
        <div class="bg-white rounded-xl overflow-auto {{ $selectedFile ? 'lg:flex-1' : 'flex-1' }}">
            <!-- Breadcrumb Section - Always Visible -->
            <div class="bg-white border-b border-gray-200 px-6 py-3 rounded-t-xl flex items-center justify-between">
                <!-- Breadcrumb -->
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        @if(count($breadcrumb) > 0)
                            @foreach($breadcrumb as $index => $item)
                                <li class="flex items-center">
                                    @if($index > 0)
                                        <i class="fa-solid fa-chevron-right text-gray-400 text-xs mx-2"></i>
                                    @endif
                                    
                                    @if($index === count($breadcrumb) - 1)
                                        <span class="text-sm font-medium text-gray-700">{{ $item['name'] }}</span>
                                    @else
                                        <button 
                                            wire:click="handleNavigation('{{ $item['level'] }}', {{ $item['id'] }})"
                                            class="text-sm text-[#1C7C54] hover:text-amber-600 font-semibold"
                                        >
                                            {{ $item['name'] }}
                                        </button>
                                    @endif
                                </li>
                            @endforeach
                        @else
                            <li class="flex items-center">
                                <span class="text-sm font-medium text-gray-700">File Manager</span>
                            </li>
                        @endif
                    </ol>
                </nav>

                <!-- View Toggle - Show for relevant levels regardless of data -->
                @if($currentLevel === 'semesters' || $currentLevel === 'requirements' || $currentLevel === 'files')
                    <div class="flex gap-1 bg-green-700/15 p-1 rounded-xl">
                        <button
                            wire:click="changeViewMode('grid')"
                            class="p-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-white text-green-600' }}"
                            aria-label="Grid view"
                        >
                            <i class="fa-solid fa-border-all"></i>
                        </button>
                        <button
                            wire:click="changeViewMode('list')"
                            class="p-2 rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-white text-green-600' }}"
                            aria-label="List view"
                        >
                            <i class="fa-solid fa-bars"></i>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Search and Filter Section - Show for relevant levels regardless of data -->
            @if($currentLevel === 'semesters' || $currentLevel === 'requirements' || $currentLevel === 'files')
                <div class="bg-white px-6 py-4">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div class="flex flex-col sm:flex-row gap-4 flex-1">
                            {{-- Search Bar --}}
                            <div class="flex-1 lg:max-w-96 relative">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-500 text-sm"></i>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="searchQuery"
                                        wire:keydown.escape="closeSearchResults"
                                        placeholder="Search semesters, requirements, or files..."
                                        class="block w-full p-2 pl-9 text-sm text-1B512D border border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 rounded-xl"
                                        aria-label="Search files"
                                        autocomplete="off"
                                    >
                                    <div wire:loading wire:target="searchQuery" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                    </div>
                                </div>
                                
                                <!-- Search Results Dropdown -->
                                @if($showSearchResults && count($searchResults) > 0)
                                    <div class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200 max-h-96 overflow-y-auto">
                                        <div class="py-2">
                                            @foreach($searchResults as $result)
                                                <button
                                                    wire:click="selectSearchResult('{{ $result['type'] }}', {{ $result['id'] }}, {{ $result['semester_id'] ?? 'null' }}, {{ $result['requirement_id'] ?? 'null' }})"
                                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 transition-colors flex items-center"
                                                    wire:key="search-result-{{ $result['type'] }}-{{ $result['id'] }}"
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
                                                            <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded-full capitalize">
                                                                {{ $result['type'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Status Filter --}}
                            @if($currentLevel === 'files')
                                <div class="min-w-0 sm:min-w-48">
                                    <select
                                        wire:model.live="statusFilter"
                                        class="block p-2 w-[150px] text-sm text-gray-500 border border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 rounded-xl"
                                        aria-label="Filter by status"
                                    >
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $key => $status)
                                            <option value="{{ $key }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
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
                                            wire:click="handleNavigation('requirements', {{ $semester->id }})"
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
                                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex-shrink-0 ml-2">
                                                            Active
                                                        </span>
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
                                                    Requirements
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($allSemesters as $semester)
                                                <tr 
                                                    wire:click="handleNavigation('requirements', {{ $semester->id }})"
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
                                                                Inactive
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                        {{ $semester->requirements->count() }} requirements
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
                                        <div class="text-xs">You don't have any active or previous semesters at this time.</div>
                                    </div>
                                </div>
                            <div>
                        @endif

                    @elseif($currentLevel === 'requirements')
                        @if(isset($currentSemester) && $currentSemester->requirements && $currentSemester->requirements->count() > 0)
                            @if($viewMode === 'grid')
                                <!-- Grid View for Requirements -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-6">
                                    @foreach($currentSemester->requirements as $requirement)
                                        <div 
                                            wire:click="handleNavigation('files', {{ $requirement->id }})"
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
                                                            {{ $requirement->name }}
                                                        </h3>
                                                        <p class="text-xs text-gray-500">
                                                            {{ $requirement->submitted_requirements_count }} files
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- List View for Requirements -->
                                <div class="bg-white overflow-hidden">
                                    <table class="w-full border-collapse">
                                        <thead class="bg-green-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Requirement</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Files</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Description</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($currentSemester->requirements as $requirement)
                                            <tr
                                            wire:click="handleNavigation('files', {{ $requirement->id }})"
                                            class="cursor-pointer hover:bg-green-50 transition-colors"
                                            >
                                            <!-- Put border on each TD (works consistently) -->
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-300">
                                                <div class="flex items-center">
                                                <div class="flex-shrink-0 flex items-center justify-center mr-3">
                                                    <i class="fa-solid fa-folder text-green-700 text-2xl"></i>
                                                </div>
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $requirement->name }}
                                                </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-b border-gray-300">
                                                {{ $requirement->submitted_requirements_count }} files
                                            </td>

                                            <td class="px-6 py-4 text-sm text-gray-500 border-b border-gray-300">
                                                {{ Str::limit($requirement->description, 50) }}
                                            </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            <!-- No Requirements Available -->
                            <div class="text-center py-16 px-6">
                                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-list-check text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-800 mb-1">No requirements available</h3>
                                <p class="text-gray-500">This semester doesn't have any requirements yet.</p>
                            </div>
                        @endif

                    @elseif($currentLevel === 'files')
                        @if(isset($currentRequirement) && $currentRequirement->submittedRequirements->count() > 0)
                            <!-- Files View -->
                            @if($viewMode === 'grid')
                                <!-- Grid View -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-6">
                                    @foreach($currentRequirement->submittedRequirements as $submission)
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
                                            @foreach($currentRequirement->submittedRequirements as $submission)
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
                        @else
                            <!-- No Files Available -->
                            <div class="text-center py-16 px-6">
                                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-file text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-800 mb-1">No files submitted</h3>
                                <p class="text-gray-500">No files have been submitted for this requirement yet.</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        @if($selectedFile)
            <div class="bg-white rounded-xl overflow-hidden lg:w-96 flex flex-col">
                <!-- File Details Header -->
                <div class="px-6 py-4" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                    <div class="flex items-center justify-between">
                        <!-- Icon + Title side by side -->
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-white text-xl"></i>
                            <h2 class="text-lg font-semibold text-white">File Details</h2>
                        </div>

                        <button 
                            wire:click="deselectFile"
                            class="text-white/80 hover:text-white transition-colors"
                            aria-label="Close details"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
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

                <!-- Fixed Action Buttons -->
                <div class="px-6 py-4 border-t bg-white flex gap-3">
                    @if($selectedFile->submissionFile)
                        <a 
                            href="{{ $this->getDownloadRoute($selectedFile->id) }}"
                            class="flex-1 flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white rounded-full transition-colors shadow-sm"
                            target="_blank"
                        >
                            <i class="fa-solid fa-download"></i>
                            <span class="font-semibold text-sm">Download</span>
                        </a>
                    @endif

                    @if($selectedFile->submissionFile && $this->canPreview($selectedFile->submissionFile->file_name))
                        <a 
                            href="{{ $this->getPreviewRoute($selectedFile->id) }}"
                            class="flex-1 flex items-center justify-center gap-2 px-2 py-2 border-2 border-green-600 text-green-700 hover:bg-green-50 rounded-full transition-colors"
                            target="_blank"
                        >
                            <i class="fa-solid fa-eye"></i>
                            <span class="font-semibold text-sm">Preview</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Handle file highlighting (without opening right panel)
            Livewire.on('fileHighlighted', (fileId) => {
                // Remove highlight from all files
                document.querySelectorAll('.file-item').forEach(item => {
                    item.classList.remove('ring-2', 'ring-green-600', 'rounded-xl', 'bg-green-50');
                });
                
                // Add highlight to the selected file
                const element = document.querySelector(`[wire\\:click="selectFile(${fileId})"]`);
                if (element) {
                    element.classList.add('ring-2', 'ring-green-600', 'rounded-xl', 'bg-green-50');
                    
                    // Remove highlight after 5 seconds
                    setTimeout(() => {
                        element.classList.remove('ring-2', 'ring-green-600', 'rounded-xl', 'bg-green-50');
                    }, 5000);
                }
            });

            Livewire.on('scrollToFile', (fileId) => {
                // Wait for Livewire to update the DOM
                setTimeout(() => {
                    const element = document.querySelector(`[wire\\:click="selectFile(${fileId})"]`);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 100);
            });

            // Handle search result selection
            Livewire.on('navigateToFileAfterSearch', (fileId) => {
                // Wait for navigation to complete, then highlight the file
                setTimeout(() => {
                    @this.highlightFileAfterSearch(fileId);
                }, 500);
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
            // Handle requirement folder clicks
            if (e.target.closest('[wire\\:click^="handleNavigation(\'files\'"]')) {
                const element = e.target.closest('[wire\\:click^="handleNavigation(\'files\'"]');
                const match = element.getAttribute('wire:click').match(/handleNavigation\('files', (\d+)\)/);
                if (match) {
                    @this.handleNavigation('files', parseInt(match[1]));
                }
            }
            
            // Handle semester folder clicks
            if (e.target.closest('[wire\\:click^="handleNavigation(\'requirements\'"]')) {
                const element = e.target.closest('[wire\\:click^="handleNavigation(\'requirements\'"]');
                const match = element.getAttribute('wire:click').match(/handleNavigation\('requirements', (\d+)\)/);
                if (match) {
                    @this.handleNavigation('requirements', parseInt(match[1]));
                }
            }
        });
        </script>
</div>