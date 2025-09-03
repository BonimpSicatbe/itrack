<div class="flex flex-col w-full max-w-7xl mx-auto bg-gray-50 min-h-screen">
    <!-- Main Container with Header Inside -->
    <div class="flex-1 bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- File Manager Header - Matching Requirements Header Style -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-folder-open text-white text-2xl"></i>
                <h1 class="text-2xl font-bold text-white">File Manager</h1>
            </div>
            <div class="flex items-center gap-6">
                {{-- File Stats --}}
                <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg">
                    <i class="fa-solid fa-folder text-white text-sm"></i>
                    <span class="text-white text-sm font-medium">{{ $totalFiles }} files</span>
                </div>
                <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg">
                    <i class="fa-solid fa-hard-drive text-white text-sm"></i>
                    <span class="text-white text-sm font-medium">{{ $totalSize }}</span>
                </div>
                
                {{-- Active Semester Display --}}
                @if($activeSemester)
                    <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg">
                        <i class="fas fa-calendar-alt text-white text-sm"></i>
                        <span class="text-white text-sm font-medium">{{ $activeSemester->name }}</span>
                        @php
                            $totalDays = $activeSemester->start_date->diffInDays($activeSemester->end_date);
                            $passedDays = $activeSemester->start_date->diffInDays(now());
                            $semesterProgress = min(100, max(0, ($passedDays / $totalDays) * 100));
                            $daysRemaining = max(0, now()->diffInDays($activeSemester->end_date, false));
                        @endphp
                        <span class="text-white text-xs font-medium">{{ number_format($semesterProgress, 0) }}%</span>
                        <div class="flex items-center gap-1">
                            <span class="flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-1.5 w-1.5 rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-400"></span>
                            </span>
                            <span class="text-xs font-medium text-green-400">Active</span>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg">
                        <i class="fas fa-calendar-times text-white text-sm"></i>
                        <span class="text-white text-sm font-medium opacity-75">No Active Semester</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Enhanced Toolbar - Matching Requirements Style -->
        <div class="bg-white border-b border-[#DEF4C6]/30 px-8 py-4 shadow-sm">
            <div class="flex items-center justify-between">
                {{-- Search and Filters --}}
                <div class="flex items-center gap-4 flex-1 flex-wrap">
                    <div class="relative flex-1 min-w-[300px] max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-[#1B512D] text-sm"></i>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search files by name..."
                            class="w-full pl-10 pr-10 py-1 text-sm bg-[#DEF4C6]/20 border border-[#73E2A7]/40 rounded-xl focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 focus:bg-white focus:outline-none transition-all duration-200 placeholder-[#1B512D]/60"
                        >
                        <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <div class="animate-spin rounded-full h-4 w-4 border-2 border-[#1C7C54] border-t-transparent"></div>
                        </div>
                    </div>
                    
                    {{-- Status Filter --}}
                    <div class="relative">
                        <select
                            wire:model.live="statusFilter"
                            class="appearance-none w-full pl-10 pr-10 py-1 text-sm bg-[#DEF4C6]/20 border border-[#73E2A7]/40 rounded-xl focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 focus:bg-white focus:outline-none transition-all duration-200 cursor-pointer"
                        >
                            <option value="">All Status</option>
                            @foreach($statuses as $key => $status)
                                <option value="{{ $key }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- View Controls - Matching Requirements Style --}}
                <div class="flex items-center gap-3">
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

            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-4" role="alert">
                    <p>{{ session('message') }}</p>
                </div>
            @endif
            
            @if (session()->has('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mt-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            {{-- Active Filters - Matching Requirements Style --}}
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
                                <span class="text-sm text-[#1B512D] font-medium">"{{ $search }}"</span>
                                <button wire:click="$set('search', '')" class="ml-1 w-5 h-5 bg-[#73E2A7]/30 hover:bg-[#73E2A7]/50 rounded-full flex items-center justify-center transition-colors duration-200">
                                    <i class="fa-solid fa-xmark text-[#1B512D] text-xs"></i>
                                </button>
                            </div>
                        @endif
                        @if($statusFilter)
                            <div class="flex items-center gap-2 px-3 py-2 bg-[#B1CF5F]/20 border border-[#B1CF5F]/40 rounded-lg">
                                <i class="fa-solid fa-check-circle text-[#1B512D] text-xs"></i>
                                <span class="text-sm text-[#1B512D] font-medium">{{ $statuses[$statusFilter] ?? $statusFilter }}</span>
                                <button wire:click="$set('statusFilter', '')" class="ml-1 w-5 h-5 bg-[#B1CF5F]/30 hover:bg-[#B1CF5F]/50 rounded-full flex items-center justify-center transition-colors duration-200">
                                    <i class="fa-solid fa-xmark text-[#1B512D] text-xs"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                    <button wire:click="$set('search', ''); $set('statusFilter', '')" class="text-sm text-[#1C7C54]/70 hover:text-[#1B512D] underline ml-auto">
                        Clear all filters
                    </button>
                </div>
            @endif
        </div>

        {{-- Warning Messages with Custom Colors --}}
        @if($activeSemester && $daysRemaining <= 7)
            <div class="flex items-center gap-2 p-3 mx-8 mt-4 rounded-lg border-l-4 {{ $daysRemaining <= 0 ? 'bg-red-100 border-red-400' : 'bg-yellow-100 border-yellow-400' }}">
                <i class="fas fa-exclamation-triangle {{ $daysRemaining <= 0 ? 'text-red-500' : 'text-yellow-600' }} text-sm"></i>
                <span class="text-sm {{ $daysRemaining <= 0 ? 'text-red-700' : 'text-yellow-700' }} font-medium">
                    {{ $daysRemaining <= 0 ? 'Semester has ended' : 'Semester ending soon' }}
                </span>
            </div>
        @endif

        {{-- Breadcrumbs with Custom Colors --}}
        <div class="px-8 py-4 border-b border-gray-200 bg-gray-50">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li>
                        <span class="flex items-center gap-2 text-[#1B512D] font-medium">
                            <i class="fa-solid fa-file text-[#1C7C54]"></i>
                            @if($activeSemester)
                                {{ $activeSemester->name }} Files
                            @else
                                Submitted Files
                            @endif
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Main Content with File Details at Top Level --}}
        <div class="flex-1 min-h-0 overflow-hidden">
            <div class="flex gap-4 h-full p-8">
                {{-- File Manager Content --}}
                <div class="flex-1 min-h-0 {{ $showFileDetails ? 'pr-0' : '' }}">
                    @livewire('user.file-manager.show-file-manager')
                </div>

                {{-- File Details Sidebar --}}
                @if($showFileDetails && $selectedFile)
                    <div class="w-96 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden flex flex-col">
                        {{-- Compact Header --}}
                        <div class="p-3 border-b bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid {{ $this->getFileIcon($selectedFile->submissionFile->file_name ?? '') }} text-lg"></i>
                                    <div>
                                        <h3 class="font-bold text-sm">File Details</h3>
                                        <p class="text-xs text-gray-600 truncate max-w-60" title="{{ $selectedFile->submissionFile->file_name ?? 'Unknown File' }}">{{ $selectedFile->submissionFile->file_name ?? 'Unknown File' }}</p>
                                    </div>
                                </div>
                                <button class="p-1 hover:bg-gray-200 rounded-full transition-colors" wire:click="closeFileDetails">
                                    <i class="fa-solid fa-times text-sm text-gray-500"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Content starts immediately after header --}}
                        <div class="flex-1 overflow-y-auto p-3">
                            <div class="space-y-4">
                                {{-- File Information --}}
                                <div>
                                    <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                        <i class="fa-solid fa-info-circle text-blue-500 text-xs"></i>
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
                                        <div class="flex justify-between gap-2">
                                            <span class="text-gray-600">Time:</span>
                                            <span class="font-medium">{{ $selectedFile->created_at->format('H:i') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Semester Information --}}
                                @if($activeSemester)
                                    <div>
                                        <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                            <i class="fa-solid fa-calendar-alt text-purple-500 text-xs"></i>
                                            Semester Information
                                        </h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between items-start gap-2">
                                                <span class="text-gray-600">Semester:</span>
                                                <span class="font-medium text-right">{{ $activeSemester->name }}</span>
                                            </div>
                                            <div class="flex justify-between gap-2">
                                                <span class="text-gray-600">Period:</span>
                                                <span class="font-medium">{{ $activeSemester->start_date->format('M Y') }} - {{ $activeSemester->end_date->format('M Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Requirement Information --}}
                                <div>
                                    <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                        <i class="fa-solid fa-tasks text-green-500 text-xs"></i>
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
                                        @if($selectedFile->requirement->description ?? null)
                                            <div>
                                                <span class="text-gray-600 block mb-1">Description:</span>
                                                <div class="bg-gray-50 p-2 rounded text-gray-700 leading-relaxed text-xs">
                                                    {{ $selectedFile->requirement->description }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Notes Section --}}
                                @if($selectedFile->notes)
                                    <div>
                                        <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                            <i class="fa-solid fa-sticky-note text-yellow-500 text-xs"></i>
                                            Notes
                                        </h4>
                                        <div class="bg-gray-50 p-2 rounded text-gray-700 leading-relaxed text-xs">
                                            {{ $selectedFile->notes }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Actions --}}
                                <div>
                                    <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                        <i class="fa-solid fa-cog text-purple-500 text-xs"></i>
                                        Actions
                                    </h4>
                                    <div class="space-y-2">
                                        {{-- Download File Button --}}
                                        @if($this->canDownloadFile($selectedFile))
                                            <button 
                                                wire:click="downloadFile({{ $selectedFile->id }})"
                                                class="w-full px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center"
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
                                                class="w-full px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center"
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