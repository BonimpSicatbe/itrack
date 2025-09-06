<div class="flex flex-col w-full max-w-7xl mx-auto bg-gray-50 min-h-screen">
    <!-- Main Container with Header Inside -->
    <div class="flex-1 bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Pending Requirements Header - Now Inside Container -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-white text-2xl"></i>
                <h1 class="text-2xl font-bold text-white">Requirements List</h1>
            </div>
            <div>
                @livewire('user.requirements.calendar-button')
            </div>
        </div>

       <!-- Enhanced Toolbar -->
<div class="bg-white border-b border-[#DEF4C6]/30 px-8 py-4 shadow-sm">
    <div class="flex items-center justify-between">
        <!-- Search and Filters -->
        <div class="flex items-center gap-4 flex-1 flex-wrap">
            <div class="relative flex-1 min-w-[300px] max-w-md">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-[#1B512D] text-sm"></i>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search requirements by name or description..."
                    class="w-full pl-10 pr-10 py-1 text-sm bg-[#DEF4C6]/20 border border-[#73E2A7]/40 rounded-xl focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 focus:bg-white focus:outline-none transition-all duration-200 placeholder-[#1B512D]/60"
                >
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-[#1C7C54] border-t-transparent"></div>
                </div>
            </div>
            
            <!-- Fixed: Enhanced Completion Filter Buttons -->
            <div class="flex items-center gap-1 bg-white/80 p-1 rounded-xl font-semibold border border-gray-300 shadow-sm">
                <button 
                    type="button"
                    class="px-4 py-1.5 text-sm rounded-lg transition-all duration-200 {{ $completionFilter === 'all' ? 'bg-[#1C7C54] text-white shadow-sm' : 'hover:bg-[#1C7C54]/20 text-[#1C7C54]' }}"
                    wire:click="setCompletionFilter('all')">
                    All
                </button>
                <button 
                    type="button"
                    class="px-4 py-1.5 text-sm rounded-lg transition-all duration-200 {{ $completionFilter === 'pending' ? 'bg-[#B1CF5F] text-white shadow-sm' : 'hover:bg-[#B1CF5F]/20 text-[#B1CF5F]' }}"
                    wire:click="setCompletionFilter('pending')">
                    Pending
                </button>
                <button 
                    type="button"
                    class="px-4 py-1.5 text-sm rounded-lg transition-all duration-200 {{ $completionFilter === 'submitted' ? 'bg-[#1B512D] text-white shadow-sm' : 'hover:bg-[#1B512D]/20 text-[#1B512D]' }}"
                    wire:click="setCompletionFilter('submitted')">
                    Submitted
                </button>
            </div>

            <!-- Enhanced View Controls -->
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-[#DEF4C6]/30 rounded-lg p-1">
                    <button class="p-2 {{ $viewMode === 'list' ? 'text-[#1B512D] bg-white rounded-md shadow-sm' : 'text-[#1C7C54]/70 hover:text-[#1B512D] hover:bg-[#DEF4C6]/20 rounded-md transition-colors' }}" wire:click="$set('viewMode', 'list')">
                        <i class="fa-solid fa-list text-sm"></i>
                    </button>
                    <button class="p-2 {{ $viewMode === 'grid' ? 'text-[#1B512D] bg-white rounded-md shadow-sm' : 'text-[#1C7C54]/70 hover:text-[#1B512D] hover:bg-[#DEF4C6]/20 rounded-md transition-colors' }}" wire:click="$set('viewMode', 'grid')">
                        <i class="fa-solid fa-grip text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash message -->
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

    <!-- FIXED: Enhanced Active Filters with proper filter handling -->
    @if($search || ($completionFilter && $completionFilter !== 'all'))
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
                @if($completionFilter && $completionFilter !== 'all')
                    <div class="flex items-center gap-2 px-3 py-2 bg-[#B1CF5F]/20 border border-[#B1CF5F]/40 rounded-lg">
                        <i class="fa-solid fa-check-circle text-[#1B512D] text-xs"></i>
                        <span class="text-sm text-[#1B512D] font-medium">{{ $completionStatuses[$completionFilter] ?? ucfirst($completionFilter) }}</span>
                        <button wire:click="setCompletionFilter('all')" class="ml-1 w-5 h-5 bg-[#B1CF5F]/30 hover:bg-[#B1CF5F]/50 rounded-full flex items-center justify-center transition-colors duration-200">
                            <i class="fa-solid fa-xmark text-[#1B512D] text-xs"></i>
                        </button>
                    </div>
                @endif
            </div>
            <button wire:click="$set('search', ''); setCompletionFilter('all')" class="text-sm text-[#1C7C54]/70 hover:text-[#1B512D] underline ml-auto">
                Clear all filters
            </button>
        </div>
    @endif
</div>

        <!-- Only show table header in list view -->
        @if($viewMode === 'list')
            <!-- Enhanced File List Header -->
            <div class="bg-gradient-to-r from-[#DEF4C6]/20 to-[#B1CF5F]/10 border-b border-[#73E2A7]/30 px-8 py-3">
                <div class="grid grid-cols-10 gap-6 text-xs font-semibold text-[#1B512D] uppercase tracking-wider">
                    <div class="col-span-4 flex items-center gap-2">
                        <button 
                            type="button"
                            wire:click="sortBy('name')" 
                            class="flex items-center gap-2 hover:text-[#1C7C54] transition-colors disabled:opacity-50"
                        >
                            <i class="fa-solid fa-file-lines text-[#1C7C54]/70"></i>
                            <span>Name</span>
                            @if($sortField === 'name')
                                @if($sortDirection === 'asc')
                                    <i class="fa-solid fa-sort-up ml-1 text-[#1C7C54]"></i>
                                @else
                                    <i class="fa-solid fa-sort-down ml-1 text-[#1C7C54]"></i>
                                @endif
                            @else
                                <i class="fa-solid fa-sort ml-1 opacity-50"></i>
                            @endif
                        </button>
                        <div wire:loading wire:target="sortBy" class="ml-1">
                            <i class="fa-solid fa-spinner fa-spin text-xs text-[#1C7C54]"></i>
                        </div>
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <button 
                            type="button"
                            wire:click="sortBy('due')" 
                            class="flex items-center gap-2 hover:text-[#1C7C54] transition-colors disabled:opacity-50"
                        >
                            <i class="fa-regular fa-calendar text-[#1C7C54]/70"></i>
                            <span>Due Date</span>
                            @if($sortField === 'due')
                                @if($sortDirection === 'asc')
                                    <i class="fa-solid fa-sort-up ml-1 text-[#1C7C54]"></i>
                                @else
                                    <i class="fa-solid fa-sort-down ml-1 text-[#1C7C54]"></i>
                                @endif
                            @else
                                <i class="fa-solid fa-sort ml-1 opacity-50"></i>
                            @endif
                        </button>
                        <div wire:loading wire:target="sortBy" class="ml-1">
                            <i class="fa-solid fa-spinner fa-spin text-xs text-[#1C7C54]"></i>
                        </div>
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <button 
                            type="button"
                            wire:click="sortBy('priority')" 
                            class="flex items-center gap-2 hover:text-[#1C7C54] transition-colors disabled:opacity-50"
                        >
                            <i class="fa-solid fa-exclamation-triangle text-[#1C7C54]/70"></i>
                            <span>Priority</span>
                            @if($sortField === 'priority')
                                @if($sortDirection === 'asc')
                                    <i class="fa-solid fa-sort-up ml-1 text-[#1C7C54]"></i>
                                @else
                                    <i class="fa-solid fa-sort-down ml-1 text-[#1C7C54]"></i>
                                @endif
                            @else
                                <i class="fa-solid fa-sort ml-1 opacity-50"></i>
                            @endif
                        </button>
                        <div wire:loading wire:target="sortBy" class="ml-1">
                            <i class="fa-solid fa-spinner fa-spin text-xs text-[#1C7C54]"></i>
                        </div>
                    </div>
                    <div class="col-span-2 flex items-center gap-2 justify-end">
                        <span>Actions</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Enhanced Requirements List Content -->
        @if($viewMode === 'grid')
            <!-- Grid View Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
               @forelse($requirements as $requirement)
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5 cursor-pointer {{ $highlightedRequirement == $requirement->id ? 'ring-2 ring-[#1C7C54] ring-offset-2 bg-[#DEF4C6]/10' : '' }}" 
         wire:click="$dispatch('showRequirementDetail', { requirementId: {{ $requirement->id }} })">
                        <div class="flex items-center justify-between mb-4">
                            @php
                             $extension = pathinfo($requirement->name, PATHINFO_EXTENSION);
                            $iconData = match(strtolower($extension)) {
                                'pdf' => ['icon' => 'fa-file-pdf', 'color' => 'text-[#1C7C54]'],
                                'doc', 'docx' => ['icon' => 'fa-file-word', 'color' => 'text-[#1B512D]'],
                                'ppt', 'pptx' => ['icon' => 'fa-file-powerpoint', 'color' => 'text-[#73E2A7]'],
                                'xls', 'xlsx' => ['icon' => 'fa-file-excel', 'color' => 'text-[#B1CF5F]'],
                                default => ['icon' => 'fa-file', 'color' => 'text-[#1C7C54]']
                            };
                             @endphp

                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center relative">
                                <i class="fa-solid {{ $iconData['icon'] }} {{ $iconData['color'] }} text-xl"></i>
                                @if($requirement->due->isPast() && !$this->isRequirementSubmitted($requirement->id))
                                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white">
                                        <i class="fa-solid fa-exclamation text-white text-xs absolute top-0 left-0.5"></i>
                                    </div>
                                @endif
                            </div>
                            @if($requirement->due->isPast() && !$this->isRequirementSubmitted($requirement->id))
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Overdue</span>
                            @endif
                        </div>
                        
                        <h3 class="font-semibold text-gray-900 mb-2 truncate">{{ $requirement->name }}</h3>
                        
                        
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="text-xs font-medium text-gray-900">{{ $requirement->due->format('M j, Y') }}</div>
                                <div class="text-xs {{ $requirement->due->isPast() && !$this->isRequirementSubmitted($requirement->id) ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                    {{ $requirement->due->diffForHumans() }}
                                </div>
                            </div>
                            
                            @php
                                $priorityConfig = match($requirement->priority) {
                                    'high' => ['color' => 'red', 'icon' => 'fa-circle-exclamation'],
                                    'medium' => ['color' => 'yellow', 'icon' => 'fa-circle-minus'],
                                    'low' => ['color' => 'green', 'icon' => 'fa-circle-check'],
                                    default => ['color' => 'gray', 'icon' => 'fa-circle']
                                };
                            @endphp
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-{{ $priorityConfig['color'] }}-100 text-{{ $priorityConfig['color'] }}-800">
                                {{ ucfirst($requirement->priority) }}
                            </span>
                        </div>
                        
                        <!-- Mark as Done Button for Grid View -->
                        <div class="mt-4" onclick="event.stopPropagation();">
                            @if($this->isRequirementSubmitted($requirement->id))
                                <div class="mt-4 flex flex-col gap-2">
                                    <span class="px-3 py-2 bg-green-100 text-green-800 text-xs font-medium rounded-full flex items-center justify-center">
                                        <i class="fa-solid fa-check mr-1"></i> Submitted
                                    </span>
                                    <button 
                                        wire:click="markAsUndone({{ $requirement->id }})" 
                                        class="w-full px-3 py-2 bg-gray-100 text-gray-800 text-xs font-medium rounded-full hover:bg-gray-200 transition-colors flex items-center justify-center"
                                        wire:loading.attr="disabled"
                                    >
                                        <div wire:loading wire:target="markAsUndone({{ $requirement->id }})" class="mr-2">
                                            <i class="fa-solid fa-spinner fa-spin"></i>
                                        </div>
                                        <i wire:loading.remove wire:target="markAsUndone({{ $requirement->id }})" class="fa-solid fa-rotate-left mr-1"></i>
                                        <span wire:loading.remove wire:target="markAsUndone({{ $requirement->id }})">Mark as Undone</span>
                                        <span wire:loading wire:target="markAsUndone({{ $requirement->id }})">Processing...</span>
                                    </button>
                                </div>
                            @else
                                <!-- Keep the existing "Mark as Done" button -->
                                <button 
                                    wire:click="markAsDone({{ $requirement->id }})" 
                                    class="w-full px-3 py-2 bg-blue-100 text-blue-800 text-xs font-medium rounded-full hover:bg-blue-200 transition-colors flex items-center justify-center"
                                    wire:loading.attr="disabled"
                                >
                                    <div wire:loading wire:target="markAsDone({{ $requirement->id }})" class="mr-2">
                                        <i class="fa-solid fa-spinner fa-spin"></i>
                                    </div>
                                    <i wire:loading.remove wire:target="markAsDone({{ $requirement->id }})" class="fa-solid fa-check mr-1"></i>
                                    <span wire:loading.remove wire:target="markAsDone({{ $requirement->id }})">Mark as Done</span>
                                    <span wire:loading wire:target="markAsDone({{ $requirement->id }})">Processing...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <!-- Empty state -->
                    <div class="col-span-full flex flex-col items-center justify-center py-24 px-8">
                        <div class="relative mb-8">
                            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-400"></i>
                            </div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                                <i class="fa-solid fa-search text-white text-sm"></i>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">No requirements found</h3>
                        <p class="text-gray-600 text-center mb-8 max-w-md leading-relaxed">
                            @if($search || ($completionFilter && $completionFilter !== 'all'))
                                We couldn't find any requirements matching your current search criteria. Try adjusting your filters or search terms.
                            @else
                                You don't have any requirements assigned yet for the current semester. New requirements will appear here when they're created.
                            @endif
                        </p>
                        @if($search || ($completionFilter && $completionFilter !== 'all'))
                            <button wire:click="$set('search', ''); setCompletionFilter('all')" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="fa-solid fa-refresh mr-2"></i>
                                Clear All Filters
                            </button>
                        @endif
                    </div>
                @endforelse
            </div>
        @else
            <!-- List View Layout -->
           @forelse($requirements as $index => $requirement)
            <div
                wire:click="$dispatch('showRequirementDetail', { requirementId: {{ $requirement->id }} })"
                class="grid grid-cols-10 gap-6 items-center px-8 py-3 border-b border-gray-100 hover:bg-blue-50/50 cursor-pointer group transition-all duration-200 {{ $index % 2 === 1 ? 'bg-gray-50/30' : 'bg-white' }} {{ $highlightedRequirement == $requirement->id ? 'ring-2 ring-[#1C7C54] ring-offset-2 bg-[#DEF4C6]/20' : '' }}"
            >
                    <!-- Name Column -->
                    <div class="col-span-4 flex items-center gap-4 min-w-0">
                        <div class="flex-shrink-0 relative">
                            @php
                                $extension = pathinfo($requirement->name, PATHINFO_EXTENSION);
                                $iconData = match(strtolower($extension)) {
                                    'pdf' => [
                                        'icon' => 'fa-file-pdf', 
                                        'color' => 'text-[#1C7C54]', 
                                        'bg' => 'bg-[#DEF4C6]/30', 
                                        'hover_color' => 'group-hover:text-[#1B512D]', 
                                        'hover_bg' => 'group-hover:bg-[#DEF4C6]/50'
                                    ],
                                    'doc', 'docx' => [
                                        'icon' => 'fa-file-word', 
                                        'color' => 'text-[#1B512D]', 
                                        'bg' => 'bg-[#73E2A7]/20', 
                                        'hover_color' => 'group-hover:text-[#1C7C54]', 
                                        'hover_bg' => 'group-hover:bg-[#73E2A7]/40'
                                    ],
                                    'ppt', 'pptx' => [
                                        'icon' => 'fa-file-powerpoint', 
                                        'color' => 'text-[#73E2A7]', 
                                        'bg' => 'bg-[#B1CF5F]/20', 
                                        'hover_color' => 'group-hover:text-[#1B512D]', 
                                        'hover_bg' => 'group-hover:bg-[#B1CF5F]/40'
                                    ],
                                    'xls', 'xlsx' => [
                                        'icon' => 'fa-file-excel', 
                                        'color' => 'text-[#B1CF5F]', 
                                        'bg' => 'bg-[#DEF4C6]/20', 
                                        'hover_color' => 'group-hover:text-[#1C7C54]', 
                                        'hover_bg' => 'group-hover:bg-[#DEF4C6]/40'
                                    ],
                                    default => [
                                        'icon' => 'fa-file', 
                                        'color' => 'text-[#1C7C54]', 
                                        'bg' => 'bg-[#DEF4C6]/30', 
                                        'hover_color' => 'group-hover:text-[#1B512D]', 
                                        'hover_bg' => 'group-hover:bg-[#DEF4C6]/50'
                                    ]
                                };
                            @endphp
                            <div class="w-10 h-10 {{ $iconData['bg'] }} {{ $iconData['hover_bg'] }} rounded-lg flex items-center justify-center group-hover:scale-105 transition-all duration-200">
                                <i class="fa-solid {{ $iconData['icon'] }} {{ $iconData['color'] }} {{ $iconData['hover_color'] }} text-lg transition-colors duration-200"></i>
                            </div>
                            {{-- Warning sign only shows if requirement is overdue AND not submitted --}}
                            @if($requirement->due->isPast() && !$this->isRequirementSubmitted($requirement->id))
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white">
                                    <i class="fa-solid fa-exclamation text-white text-xs absolute top-0 left-0.5"></i>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-700 transition-colors mb-1">
                                {{ $requirement->name }}
                            </p>
                            
                        </div>
                    </div>

                    <!-- Due Date Column -->
                    <div class="col-span-2">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900">{{ $requirement->due->format('M j, Y') }}</span>
                                @if($requirement->due->isToday())
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">Today</span>
                                @endif
                            </div>
                            <span class="text-xs mt-1 {{ $requirement->due->isPast() && !$this->isRequirementSubmitted($requirement->id) ? 'text-red-600 font-small' : 'text-gray-500' }}">
                                {{ $requirement->due->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <!-- Priority Column -->
                    <div class="col-span-2">
                        @php
                            $priorityConfig = match($requirement->priority) {
                                'high' => ['color' => 'red', 'icon' => 'fa-circle-exclamation'],
                                'medium' => ['color' => 'yellow', 'icon' => 'fa-circle-minus'],
                                'low' => ['color' => 'green', 'icon' => 'fa-circle-check'],
                                default => ['color' => 'gray', 'icon' => 'fa-circle']
                            };
                        @endphp
                        <span class="inline-flex items-center text-xs font-semibold text-{{ $priorityConfig['color'] }}-800">
                            <i class="fa-solid {{ $priorityConfig['icon'] }} mr-2 text-{{ $priorityConfig['color'] }}-600"></i>
                            {{ ucfirst($requirement->priority) }}
                        </span>
                    </div>

                    <!-- Mark as Done Button for List View -->
                    <div class="col-span-2 flex justify-end" onclick="event.stopPropagation();">
                        @if($this->isRequirementSubmitted($requirement->id))
                            <div class="flex justify-end gap-2">
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex items-center">
                                    <i class="fa-solid fa-check mr-1"></i> Submitted
                                </span>
                                <button 
                                    wire:click="markAsUndone({{ $requirement->id }})" 
                                    class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full hover:bg-gray-200 transition-colors flex items-center"
                                    wire:loading.attr="disabled"
                                >
                                    <div wire:loading wire:target="markAsUndone({{ $requirement->id }})" class="mr-1">
                                        <i class="fa-solid fa-spinner fa-spin"></i>
                                    </div>
                                    <i wire:loading.remove wire:target="markAsUndone({{ $requirement->id }})" class="fa-solid fa-rotate-left mr-1"></i>
                                    <span wire:loading.remove wire:target="markAsUndone({{ $requirement->id }})">Undo</span>
                                    <span wire:loading wire:target="markAsUndone({{ $requirement->id }})">Processing...</span>
                                </button>
                            </div>
                        @else
                            <!-- Keep the existing "Mark as Done" button -->
                            <button 
                                wire:click="markAsDone({{ $requirement->id }})" 
                                class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full hover:bg-blue-200 transition-colors flex items-center"
                                wire:loading.attr="disabled"
                            >
                                <div wire:loading wire:target="markAsDone({{ $requirement->id }})" class="mr-1">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                </div>
                                <i wire:loading.remove wire:target="markAsDone({{ $requirement->id }})" class="fa-solid fa-check mr-1"></i>
                                <span wire:loading.remove wire:target="markAsDone({{ $requirement->id }})">Mark as Done</span>
                                <span wire:loading wire:target="markAsDone({{ $requirement->id }})">Processing...</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <!-- Empty state -->
                <div class="flex flex-col items-center justify-center py-24 px-8">
                    <div class="relative mb-8">
                        <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fa-regular fa-folder-open text-4xl text-gray-400"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-search text-white text-sm"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">No requirements found</h3>
                    <p class="text-gray-600 text-center mb-8 max-w-md leading-relaxed">
                        @if($search || ($completionFilter && $completionFilter !== 'all'))
                            We couldn't find any requirements matching your current search criteria. Try adjusting your filters or search terms.
                        @else
                            You don't have any requirements assigned yet for the current semester. New requirements will appear here when they're created.
                        @endif
                    </p>
                    @if($search || ($completionFilter && $completionFilter !== 'all'))
                        <button wire:click="$set('search', ''); setCompletionFilter('all')" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fa-solid fa-refresh mr-2"></i>
                            Clear All Filters
                        </button>
                    @endif
                </div>
            @endforelse
        @endif

       

        <!-- Enhanced Footer/Status Bar -->
        <div class="bg-white border-t border-gray-200 px-8 py-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6 text-sm text-gray-600">
                    @if($requirements->count() > 0)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-gray-400"></i>
                            <span>Showing <span class="font-semibold text-gray-900">{{ $requirements->firstItem() }}</span> to <span class="font-semibold text-gray-900">{{ $requirements->lastItem() }}</span> of <span class="font-semibold text-gray-900">{{ $requirements->total() }}</span> {{ $requirements->total() === 1 ? 'requirement' : 'requirements' }}</span>
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

        <!-- Pagination for list view -->
        @if($requirements->hasPages() && $viewMode === 'list')
            <div class="bg-white border-t border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ $requirements->firstItem() }} to {{ $requirements->lastItem() }} of {{ $requirements->total() }} results
                    </div>
                    <div>
                        {{ $requirements->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Include the reusable modal component -->
    @livewire('user.requirement-detail-modal')

    <!-- Add this script at the bottom of the file -->
@script
<script>
    // Listen for Livewire initialization
    Livewire.hook('component.initialized', (component) => {
        if (component.name === 'user.requirements.requirements-list') {
            // Check if there's a highlighted requirement in the URL
            const urlParams = new URLSearchParams(window.location.search);
            const requirementId = urlParams.get('requirement');
            
            if (requirementId) {
                // Wait a bit for the component to fully initialize
                setTimeout(() => {
                    // Dispatch event to show the requirement detail modal
                    window.dispatchEvent(new CustomEvent('showRequirementDetail', {
                        detail: { requirementId: requirementId }
                    }));
                }, 300);
            }
        }
    });
</script>
@endscript
</div>