<div class="flex flex-col w-full mx-auto min-h-screen">
    {{-- Header Container - Now a separate div --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
        {{-- Header Section --}}
        <div class="flex items-center justify-between px-6 py-6 border-b border-gray-200" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-white text-2xl"></i>
                <h1 class="text-xl font-bold text-white">Recent Submissions</h1>
            </div>
        </div>
    </div>

    {{-- Main Content Container --}}
    <div class="flex-1 bg-white rounded-xl overflow-y-auto p-6" style="max-height: calc(100vh - 125px);">
        @if(!$activeSemester)
            <div class="flex items-center p-4 bg-[#DEF4C6] text-[#1B512D] rounded-lg shadow-lg">
                <i class="fa-solid fa-triangle-exclamation text-lg mr-3"></i>
                <div>
                    <h3 class="font-semibold text-sm">No active semester</h3>
                    <div class="text-xs">Recent submissions will appear here once you have an active semester.</div>
                </div>
            </div>
        @else
            {{-- Search and Filter Controls --}}
            <div class="bg-white mb-5">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4">
                    {{-- Left Side Controls: Search, Filter, and Clear --}}
                    <div class="flex flex-col sm:flex-row items-end gap-4 flex-1">
                        {{-- Search Bar --}}
                        <div class="flex-1 lg:max-w-96 w-full sm:w-auto">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-[#1B512D] text-sm"></i>
                                </div>
                                <input 
                                    id="recentSubmissionsSearch"
                                    type="text" 
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="Search by name or file..."
                                    class="pl-11 block w-full rounded-xl text-gray-500 border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 text-sm" 
                                >
                                <div wire:loading wire:target="search" class="absolute inset-y-3 right-0 pr-4 flex items-center">
                                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-[#1C7C54] border-t-transparent"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Status Filter --}}
                        <div class="min-w-0">
                            <select 
                                wire:model.live="statusFilter"
                                class="block w-[150px] rounded-xl text-gray-500 border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm"
                            >
                                <option value="">All Statuses</option>
                                <option value="under_review">Under Review</option>
                                <option value="revision_needed">Revision Needed</option>
                                <option value="rejected">Rejected</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>

                        {{-- Clear Filters Button --}}
                        @if($search || $statusFilter)
                            <div>
                                <button 
                                    wire:click="clearFilters"
                                    onclick="document.getElementById('recentSubmissionsSearch').value = ''"
                                    class="inline-flex items-center px-4 py-2 bg-white border-2 border-green-600 text-sm font-medium rounded-xl text-gray-500 hover:bg-green-50 h-10 shadow-md"
                                >
                                    <i class="fa-solid fa-xmark text-sm mr-2"></i>
                                    Clear Filters
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Right Side Control: View Mode Toggle Buttons --}}
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
                </div>
            </div>

            {{-- Submissions List --}}
            <div class="{{ $viewMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4' : 'divide-y divide-gray-200' }}">
                @if($recentSubmissions->count() > 0)
                    @foreach($recentSubmissions as $submission)
                        @if ($viewMode === 'list')
                            <div class="mb-4 rounded-xl shadow-sm border border-gray-300 bg-white hover:border-2 hover:border-green-600">
                                <div 
                                    wire:click="showSubmissionDetail({{ $submission->id }})"
                                    class="px-4 py-3 transition-colors duration-150 cursor-pointer"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                @if($submission->submissionFile)
                                                    <div class=" flex items-center justify-center">
                                                        <i class="fas {{ $submission->getFileIcon() }} {{ $submission->getFileIconColor() }} text-3xl"></i>
                                                    </div>
                                                @else
                                                    <div class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center">
                                                        <i class="fa-solid fa-file-circle-question text-gray-400 text-lg"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-800 truncate">
                                                    @if($submission->submissionFile)
                                                        {{ $submission->submissionFile->file_name }}
                                                    @else
                                                        {{ $submission->requirement->name }} <span class="text-gray-400 text-xs">(No file)</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ $submission->requirement->name }} • Submitted {{ $submission->submitted_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end space-y-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $submission->status_badge }}">
                                                {{ $submission->status_text }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else 
                        {{-- Grid View --}}
                            <div
                                wire:click="showSubmissionDetail({{ $submission->id }})"
                                class="rounded-xl border-2 border-gray-200 bg-white shadow-md hover:border-2 hover:border-green-600 p-4 flex flex-col justify-between h-full cursor-pointer"
                            >
                                <div class="flex-grow flex flex-row items-center gap-4 mb-4">
                                    <div class="flex-shrink-0">
                                        @if($submission->submissionFile)
                                            <i class="fas {{ $submission->getFileIcon() }} {{ $submission->getFileIconColor() }} text-4xl"></i>
                                        @else
                                            <i class="fa-solid fa-file-circle-question text-gray-400 text-4xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate" title="{{ $submission->submissionFile ? $submission->submissionFile->file_name : $submission->requirement->name }}">
                                            @if($submission->submissionFile)
                                                {{ $submission->submissionFile->file_name }}
                                            @else
                                                {{ $submission->requirement->name }} <span class="text-gray-400 text-xs">(No file)</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 truncate mt-1" title="{{ $submission->requirement->name }}">
                                            {{ $submission->requirement->name }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex justify-end items-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $submission->status_badge }}">
                                        {{ $submission->status_text }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-12 text-gray-500 {{ $viewMode === 'grid' ? 'col-span-full' : '' }}">
                        @if($search || $statusFilter)
                            <i class="fa-solid fa-folder-open text-4xl mb-2 text-gray-300"></i>
                            <p class="font-semibold text-sm">No submissions found</p>
                            <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search or filter</p>
                        @else
                            <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-4"></i>
                            <p class="text-sm font-semibold">No submissions yet for this semester.</p>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Include the Recent Submission Detail Modal component --}}
    @livewire('user.recents.recent-submission-detail-modal')
</div>