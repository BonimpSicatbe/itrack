{{-- Wrapper div to contain everything and avoid multiple root elements --}}
<div>
    {{-- Main Container with Header Inside --}}
    <div class="flex-1 bg-white rounded-lg shadow-sm overflow-hidden">
        {{-- Recent Submissions Header - Matching Pending Requirements Style --}}
        <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-white text-2xl"></i>
                <h1 class="text-2xl font-bold text-white">Recent Submissions</h1>
            </div>
        </div>

        {{-- Search and Filter Controls --}}
        <div class="bg-white border-b border-[#DEF4C6]/30 px-8 py-4 shadow-sm">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4">
                <div class="flex flex-col sm:flex-row gap-4 flex-1">
                    {{-- Search Bar - Now First --}}
                    <div class="flex-1 lg:max-w-96">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-[#1B512D] text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search by name or file..."
                                class="w-full pl-10 pr-10 py-1 text-sm bg-[#DEF4C6]/20 border border-[#73E2A7]/40 rounded-xl focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 focus:bg-white focus:outline-none transition-all duration-200 placeholder-[#1B512D]/60"
                                aria-label="Search submissions">
                            <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <div class="animate-spin rounded-full h-4 w-4 border-2 border-[#1C7C54] border-t-transparent"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Status Filter - Now Second --}}
                    <div class="min-w-0 sm:min-w-48">
                        <select 
                            wire:model.live="statusFilter"
                            class="block w-full pl-10 pr-10 py-1 text-sm bg-[#DEF4C6]/20 border border-[#73E2A7]/40 rounded-xl focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 focus:bg-white focus:outline-none transition-all duration-200"
                        >
                            <option value="">All Statuses</option>
                            <option value="under_review">Under Review</option>
                            <option value="revision_needed">Revision Needed</option>
                            <option value="rejected">Rejected</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                </div>
                
                {{-- Clear Filters Button --}}
                @if($search || $statusFilter)
                    <div class="flex items-end">
                        <button 
                            wire:click="clearFilters"
                            class="inline-flex items-center px-4 py-2 bg-[#DEF4C6]/30 border border-[#73E2A7]/40 text-sm font-medium rounded-xl text-[#1B512D] hover:bg-[#DEF4C6]/50 hover:border-[#73E2A7]/60 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 transition-all duration-200 h-10"
                        >
                            <i class="fa-solid fa-xmark text-sm mr-2"></i>
                            Clear Filters
                        </button>
                    </div>
                @endif
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
                                <span class="text-sm text-[#1B512D] font-medium">"{{ $search }}"</span>
                                <button wire:click="$set('search', '')" class="ml-1 w-5 h-5 bg-[#73E2A7]/30 hover:bg-[#73E2A7]/50 rounded-full flex items-center justify-center transition-colors duration-200">
                                    <i class="fa-solid fa-xmark text-[#1B512D] text-xs"></i>
                                </button>
                            </div>
                        @endif
                        @if($statusFilter)
                            <div class="flex items-center gap-2 px-3 py-2 bg-[#B1CF5F]/20 border border-[#B1CF5F]/40 rounded-lg">
                                <i class="fa-solid fa-check-circle text-[#1B512D] text-xs"></i>
                                <span class="text-sm text-[#1B512D] font-medium">{{ ucfirst(str_replace('_', ' ', $statusFilter)) }}</span>
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

        {{-- Submissions List --}}
        <div class="divide-y divide-gray-200">
            @if($recentSubmissions->count() > 0)
                @foreach($recentSubmissions as $submission)
                    <div 
                        wire:click="showRequirementDetail({{ $submission->id }})"
                        class="px-6 py-4 hover:bg-[#DEF4C6]/10 transition-colors duration-150 cursor-pointer"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @if($submission->submissionFile)
                                        <div class="h-10 w-10 rounded-md bg-[#DEF4C6]/30 flex items-center justify-center">
                                            <i class="fa-solid fa-file text-[#1C7C54] text-lg"></i>
                                        </div>
                                    @else
                                        <div class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center">
                                            <i class="fa-solid fa-file-circle-question text-gray-400 text-lg"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $submission->requirement->name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Submitted {{ $submission->submitted_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $submission->status_badge }}">
                                    {{ $submission->status_text }}
                                </span>
                                @if($submission->submissionFile)
                                    <span class="text-xs text-gray-400 flex items-center">
                                        <i class="fa-solid fa-file text-gray-400 text-xs mr-1"></i>
                                        {{ $submission->submissionFile->file_name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-12">
                    <div class="relative mb-8">
                        <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fa-regular fa-folder-open text-4xl text-gray-400"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-[#1C7C54] to-[#1B512D] rounded-full flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-search text-white text-sm"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">No submissions found</h3>
                    <p class="text-gray-600 text-center mb-8 max-w-md leading-relaxed mx-auto">
                        @if($statusFilter || $search)
                            We couldn't find any submissions matching your current search criteria. Try adjusting your filters or search terms.
                        @else
                            You haven't submitted any requirements yet. New submissions will appear here once you submit requirements.
                        @endif
                    </p>
                    @if($statusFilter || $search)
                        <button wire:click="$set('search', ''); $set('statusFilter', '')" class="px-6 py-3 bg-gradient-to-r from-[#1C7C54] to-[#1B512D] text-white text-sm font-semibold rounded-lg hover:from-[#1B512D] hover:to-[#1C7C54] transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fa-solid fa-refresh mr-2"></i>
                            Clear All Filters
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Enhanced Footer/Status Bar --}}
        <div class="bg-white border-t border-gray-200 px-8 py-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6 text-sm text-gray-600">
                    @if($recentSubmissions->count() > 0)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-gray-400"></i>
                            <span>Showing <span class="font-semibold text-gray-900">{{ $recentSubmissions->count() }}</span> {{ $recentSubmissions->count() === 1 ? 'submission' : 'submissions' }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock text-gray-400"></i>
                        <span>Last updated: <span class="font-medium">{{ now()->format('M j, Y g:i A') }}</span></span>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span>System Online</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include the Requirement Detail Modal component --}}
    @livewire('user.requirement-detail-modal')
</div>