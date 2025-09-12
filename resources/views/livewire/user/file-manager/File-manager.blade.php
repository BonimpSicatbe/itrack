<div class="flex flex-col w-full max-w-7xl mx-auto bg-gray-50 min-h-screen">
    <!-- Main Container with Header Inside -->
    <div class="flex-1 bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- File Manager Header - Matching Requirements Header Style -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-folder-open text-white text-2xl"></i>
                <h1 class="text-xl font-bold text-white">File Manager</h1>
                
                <!-- Current Semester Indicator -->
                @if($selectedSemester)
                    <span class="ml-4 px-3 py-1 bg-white/20 rounded-full text-white text-sm">
                        Viewing {{ $selectedSemester->name }}
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                {{-- File Stats --}}
                <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg">
                    <i class="fa-solid fa-folder text-white text-sm"></i>
                    <span class="text-white text-sm font-medium">{{ $totalFiles }} files</span>
                </div>
                <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg">
                    <i class="fa-solid fa-hard-drive text-white text-sm"></i>
                    <span class="text-white text-sm font-medium">{{ $totalSize }}</span>
                </div>
                
                {{-- Archive Button --}}
                <a href="{{ route('user.archive') }}" class="flex items-center gap-2 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                    <i class="fa-solid fa-box-archive text-white text-sm"></i>
                    <span class="text-white text-sm font-medium">View Archive</span>
                </a>
                
                {{-- Semester Manager Toggle Button --}}
                <button 
                    wire:click="toggleSemesterManager"
                    class="flex items-center gap-2 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors"
                >
                    <i class="fa-solid fa-calendar-days text-white text-sm"></i>
                    <span class="text-white text-sm font-medium">Semester</span>
                </button>
            </div>
        </div>

        {{-- Breadcrumb Navigation --}}
        @if($showFolderView && $currentFolder)
            <div class="bg-gray-50 border-b border-gray-200 px-8 py-3">
                <div class="flex items-center gap-2 text-sm">
                    <button wire:click="exitFolderView" class="text-[#1C7C54] hover:text-[#1B512D] flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Current Semester
                    </button>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-600">
                        {{ \App\Models\Semester::find($currentFolder)->name ?? 'Archived Semester' }}
                    </span>
                </div>
            </div>
        @endif

        <!-- Main Content Area with Sidebar -->
        <div class="flex flex-1 min-h-0">
            <!-- Semester Manager Sidebar (Conditional) -->
            @if($showSemesterManager)
                <div class="w-80 bg-white border-r border-gray-200 shadow-inner overflow-y-auto">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-800">Semester Manager</h2>
                            <button wire:click="toggleSemesterManager" class="text-gray-400 hover:text-gray-600">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Search Box -->
                        <div class="relative mb-6">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-[#1B512D] text-sm"></i>
                            </div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchTerm"
                                placeholder="Search files or users..."
                                class="w-full pl-10 pr-4 py-2 text-sm bg-[#DEF4C6]/20 border border-[#73E2A7]/40 rounded-xl focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 focus:bg-white focus:outline-none transition-all duration-200 placeholder-[#1B512D]/60"
                            >
                        </div>
                        
                        <!-- Current Semester Section -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Current Semester</h3>
                            @if($activeSemester)
                                <div class="bg-[#DEF4C6]/20 border border-[#73E2A7]/40 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="font-medium text-[#1B512D]">{{ $activeSemester->name }}</h4>
                                            <p class="text-xs text-gray-600 mt-1">
                                                {{ $activeSemester->start_date->format('M d, Y') }} - {{ $activeSemester->end_date->format('M d, Y') }}
                                            </p>
                                        </div>
                                        <span class="px-2 py-1 bg-[#1C7C54] text-white text-xs rounded-full">Active</span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between text-xs text-[#1B512D]">
                                            <span>Progress</span>
                                            <span>{{ round($semesterProgress) }}%</span>
                                        </div>
                                        <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                                            <div 
                                                class="h-2 rounded-full bg-gradient-to-r from-[#1C7C54] to-[#1B512D]" 
                                                style="width: {{ $semesterProgress }}%"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-600">
                                        @if($daysRemaining > 0)
                                            {{ $daysRemaining }} days remaining
                                        @else
                                            Semester ended
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                    <i class="fa-solid fa-calendar-exclamation text-yellow-500 text-lg mb-2"></i>
                                    <p class="text-sm text-yellow-700">No active semester</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Archived Semesters Section -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Archived Semesters</h3>
                            <div class="space-y-3">
                                @forelse($allSemesters->where('is_active', false) as $semester)
                                    <button 
                                        wire:click="navigateToFolder({{ $semester->id }})"
                                        class="w-full border border-gray-200 rounded-lg p-3 hover:bg-gray-50 hover:border-[#73E2A7]/40 transition-all duration-200 text-left"
                                    >
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-folder text-[#1C7C54]"></i>
                                                <div>
                                                    <h4 class="font-medium text-gray-800 text-sm">{{ $semester->name }}</h4>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        {{ $semester->start_date->format('M d, Y') }} - {{ $semester->end_date->format('M d, Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Archived</span>
                                        </div>
                                    </button>
                                @empty
                                    <div class="text-center py-4 text-gray-500 text-sm">
                                        No archived semesters
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif   

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-h-0">
                <!-- Search and Filter Controls - Matching other pages style -->
                <div class="bg-white border-b border-[#DEF4C6]/30 px-8 py-4 shadow-sm">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4">
                        <div class="flex flex-col sm:flex-row gap-4 flex-1">
                            {{-- Search Bar --}}
                            <div class="flex-1 lg:max-w-96">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-magnifying-glass text-[#1B512D] text-sm"></i>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="Search files by name..."
                                        class="w-full pl-10 pr-4 py-2 text-sm  bg-[#DEF4C6]/20 border border-[#73E2A7]/40 rounded-xl focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 focus:bg-white focus:outline-none transition-all duration-200 placeholder-[#1B512D]/60"
                                        aria-label="Search files"
                                    >
                                    <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                        <div class="animate-spin rounded-full h-4 w-4 border-2 border-[#1C7C54] border-t-transparent"></div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Status Filter --}}
                            <div class="min-w-0 sm:min-w-48">
                                <select
                                    wire:model.live="statusFilter"
                                    class="w-full pl-10 pr-4 py-2 text-sm  bg-[#DEF4C6]/20 border border-[#73E2A7]/40 rounded-xl focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 focus:bg-white focus:outline-none transition-all duration-200"
                                >
                                    <option value="">All Status</option>
                                    @foreach($statuses as $key => $status)
                                        <option value="{{ $key }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        {{-- View Controls and Clear Filters --}}
                        <div class="flex items-center gap-3">
                            {{-- Clear Filters Button --}}
                            @if($search || $statusFilter)
                                <button 
                                    wire:click="clearFilters"
                                    class="inline-flex items-center px-4 py-2 bg-[#DEF4C6]/30 border border-[#73E2A7]/40 text-sm font-medium rounded-xl text-[#1B512D] hover:bg-[#DEF4C6]/50 hover:border-[#73E2A7]/60 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 transition-all duration-200 h-10"
                                >
                                    <i class="fa-solid fa-xmark text-sm mr-2"></i>
                                    Clear Filters
                                </button>
                            @endif
                            
                            {{-- View Mode Toggle --}}
                            <div class="flex items-center bg-[#DEF4C6]/30 rounded-lg p-1">
                                <button 
                                    wire:click="$set('viewMode', 'list')"
                                    class="p-2 {{ $viewMode === 'list' ? 'text-[#1B512D] bg-white rounded-md shadow-sm' : 'text-[#1C7C54]/70 hover:text-[#1B512D] hover:bg-[#DEF4C6]/20 rounded-md transition-colors' }}"
                                >
                                    <i class="fa-solid fa-list text-sm"></i>
                                </button>
                                <button 
                                    wire:click="$set('viewMode', 'grid')"
                                    class="p-2 {{ $viewMode === 'grid' ? 'text-[#1B512D] bg-white rounded-md shadow-sm' : 'text-[#1C7C54]/70 hover:text-[#1B512D] hover:bg-[#DEF4C6]/20 rounded-md transition-colors' }}"
                                >
                                    <i class="fa-solid fa-grip text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Active Filters Display --}}
                    @if($search || $statusFilter)
                        <div class="flex items-center gap-3 mt-4 pt-4 border-t border-[#DEF4C6]/30">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-filter text-[#1C7C54]"></i>
                                <span class="text-sm font-semibold text-[#1B512D]">Active filters:</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($search)
                                    <div class="flex items-center gap-2 px-3 py-2 bg-[#73E2A7]/20 border border-[#73E2A7]/40 rounded-lg">
                                        <i class="fa-solid fa-magnifying-glass text-[#1C7C54] text-xs"></i>
                                        <span class="text-sm text-gray-500 font-medium">"{{ $search }}"</span>
                                        <button wire:click="$set('search', '')" class="ml-1 w-5 h-5 bg-[#73E2A7]/30 hover:bg-[#73E2A7]/50 rounded-full flex items-center justify-center transition-colors duration-200">
                                            <i class="fa-solid fa-xmark text-[#1B512D] text-xs"></i>
                                        </button>
                                    </div>
                                @endif
                                @if($statusFilter)
                                    <div class="flex items-center gap-2 px-3 py-2 bg-[#B1CF5F]/20 border border-[#B1CF5F]/40 rounded-lg">
                                        <i class="fa-solid fa-check-circle text-[#1B512D] text-xs"></i>
                                        <span class="text-sm text-gray-500 font-medium">{{ $statuses[$statusFilter] ?? $statusFilter }}</span>
                                        <button wire:click="$set('statusFilter', '')" class="ml-1 w-5 h-5 bg-[#B1CF5F]/30 hover:bg-[#B1CF5F]/50 rounded-full flex items-center justify-center transition-colors duration-200">
                                            <i class="fa-solid fa-xmark text-[#1B512D] text-xs"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button wire:click="$set('search', ''); $set('statusFilter', '')" class="text-sm text-gray-500 hover:text-[#1B512D] underline ml-auto">
                                Clear all filters
                            </button>
                        </div>
                    @endif

                    {{-- Flash Messages --}}
                    @if (session()->has('message'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-4 rounded" role="alert">
                            <p>{{ session('message') }}</p>
                        </div>
                    @endif
                    
                    @if (session()->has('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mt-4 rounded" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif
                </div>

                {{-- Warning Messages --}}
                @if($activeSemester && $daysRemaining <= 7)
                    <div class="flex items-center gap-2 p-3 mx-8 mt-4 rounded-lg border-l-4 {{ $daysRemaining <= 0 ? 'bg-red-100 border-red-400' : 'bg-yellow-100 border-yellow-400' }}">
                        <i class="fas fa-exclamation-triangle {{ $daysRemaining <= 0 ? 'text-red-500' : 'text-yellow-600' }} text-sm"></i>
                        <span class="text-sm {{ $daysRemaining <= 0 ? 'text-red-700' : 'text-yellow-700' }} font-medium">
                            {{ $daysRemaining <= 0 ? 'Semester has ended' : 'Semester ending soon' }}
                        </span>
                    </div>
                @endif

                {{-- Archived Semester Warning --}}
                @if($selectedSemester && !$selectedSemester->is_active)
                    <div class="flex items-center gap-2 p-3 mx-8 mt-4 rounded-lg bg-blue-100 border-l-4 border-blue-400">
                        <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                        <span class="text-sm text-blue-700 font-medium">
                            You are viewing an archived semester. These files are read-only.
                        </span>
                    </div>
                @endif

                {{-- File Manager Content --}}
                <div class="flex-1 min-h-0 p-8">
                    @if($this->getCurrentViewProperty() === 'folder')
                        {{-- Show files for the selected archived semester --}}
                        @livewire('user.file-manager.show-file-manager', 
                            ['selectedSemesterId' => $selectedSemesterId], 
                            key('show-file-manager-'.$selectedSemesterId)
                        )
                    @elseif($this->getCurrentViewProperty() === 'files')
                        {{-- Show files for the selected semester --}}
                        @livewire('user.file-manager.show-file-manager', 
                            ['selectedSemesterId' => $selectedSemesterId], 
                            key('show-file-manager-'.$selectedSemesterId)
                        )
                    @else
                        {{-- No semester selected state --}}
                        <div class="text-center py-12">
                            <div class="mb-8">
                                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center shadow-lg mx-auto">
                                    <i class="fa-solid fa-folder-open text-4xl text-gray-400"></i>
                                </div>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-3">File Manager</h3>
                            <p class="text-sm text-gray-500 text-center mb-8 max-w-md leading-relaxed mx-auto">
                                Select a semester to view your files and manage your submissions.
                            </p>
                            <button 
                                wire:click="toggleSemesterManager"
                                class="px-6 py-3 bg-gradient-to-r from-[#1C7C54] to-[#1B512D] text-white text-sm font-semibold rounded-lg hover:from-[#1B512D] hover:to-[#1C7C54] transition-all duration-200 shadow-lg hover:shadow-xl"
                            >
                                <i class="fa-solid fa-folder-tree mr-2"></i>
                                View Semesters
                            </button>
                        </div>
                    @endif
                </div>

                
            </div>

            {{-- File Details Sidebar --}}
            @if($showFileDetails && $selectedFile)
                <div class="w-96 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden flex flex-col">
                    {{-- Header matching other pages --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="fa-solid {{ $this->getFileIcon($selectedFile->submissionFile->file_name ?? '') }} text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">File Details</h3>
                                <p class="text-white/80 text-xs truncate max-w-60" title="{{ $selectedFile->submissionFile->file_name ?? 'Unknown File' }}">{{ $selectedFile->submissionFile->file_name ?? 'Unknown File' }}</p>
                            </div>
                        </div>
                        <button class="p-2 text-white/70 hover:text-white hover:bg-white/20 rounded-lg transition-all duration-200" wire:click="closeFileDetails">
                            <i class="fa-solid fa-times text-sm"></i>
                        </button>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 overflow-y-auto p-4">
                        <div class="space-y-4">
                            {{-- File Information --}}
                            <div>
                                <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-info-circle text-[#1C7C54] text-xs"></i>
                                    File Information
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between items-start gap-2">
                                        <span class="text-gray-600">File Name:</span>
                                        <span class="font-medium text-right break-all max-w-48">{{ $selectedFile->submissionFile->file_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-600">File Size:</span>
                                        <span class="font-medium">{{ $this->formatFileSize($selectedFile->submissionFile->size ?? 0) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-600">File Type:</span>
                                        <span class="font-medium uppercase">{{ strtoupper(pathinfo($selectedFile->submissionFile->file_name ?? '', PATHINFO_EXTENSION)) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-600">Uploaded:</span>
                                        <span class="font-medium">{{ $selectedFile->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Requirement Information --}}
                            <div>
                                <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-tasks text-[#1C7C54] text-xs"></i>
                                    Requirement Details
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between items-start gap-2">
                                        <span class="text-gray-600">Requirement:</span>
                                        <span class="font-medium text-right max-w-48">{{ $selectedFile->requirement->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $selectedFile->getStatusBadgeClass() }}">
                                            {{ $selectedFile->getStatusText() }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div>
                                <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-cog text-[#1C7C54] text-xs"></i>
                                    Actions
                                </h4>
                                <div class="space-y-2">
                                    {{-- Download File Button --}}
                                    @if($this->canDownloadFile($selectedFile))
                                        <button 
                                            wire:click="downloadFile({{ $selectedFile->id }})"
                                            class="w-full px-3 py-2 bg-gradient-to-r from-[#1C7C54] to-[#1B512D] text-white text-sm rounded-lg hover:from-[#1B512D] hover:to-[#1C7C54] transition-all duration-200 flex items-center justify-center"
                                            wire:loading.attr="disabled"
                                            wire:target="downloadFile({{ $selectedFile->id }})"
                                        >
                                            <span wire:loading.remove wire:target="downloadFile({{ $selectedFile->id }})">
                                                <i class="fa-solid fa-download mr-2"></i>
                                                Download File
                                            </span>
                                            <span wire:loading wire:target="downloadFile({{ $selectedFile->id }})">
                                                <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                                                Downloading...
                                            </span>
                                        </button>
                                    @endif

                                    {{-- Open File Button --}}
                                    @if($this->canOpenFile($selectedFile))
                                        <a 
                                            href="{{ $this->getFileUrl($selectedFile) }}" 
                                            target="_blank"
                                            class="w-full px-3 py-2 bg-[#DEF4C6]/30 border border-[#73E2A7]/40 text-[#1B512D] text-sm rounded-lg hover:bg-[#DEF4C6]/50 hover:border-[#73E2A7]/60 transition-all duration-200 flex items-center justify-center"
                                        >
                                            <i class="fa-solid fa-external-link-alt mr-2"></i>
                                            <span>Open File</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>