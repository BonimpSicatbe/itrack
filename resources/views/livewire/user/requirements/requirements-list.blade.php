<div class="flex flex-col w-full bg-gray-50 min-h-screen">
    <!-- Enhanced Toolbar -->
    <div class="bg-white border-b border-gray-200 px-8 py-4 shadow-sm">
        <div class="flex items-center justify-between">
            <!-- Search and Filters -->
            <div class="flex items-center gap-4 flex-1">
                <div class="relative w-96">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search requirements by name or description..."
                        class="w-full pl-12 pr-12 py-3 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white focus:outline-none transition-all duration-200 placeholder-gray-500"
                    >
                    <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-500 border-t-transparent"></div>
                    </div>
                </div>
                
                <!-- Enhanced Status Filter -->
                <div class="relative">
                    <select
                        wire:model.live="statusFilter"
                        class="appearance-none px-4 py-3 pr-10 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white focus:outline-none transition-all duration-200 cursor-pointer"
                    >
                        <option value="">All Status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    
                </div>
            </div>

            <!-- Enhanced View Controls -->
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                    <button class="p-2 {{ $viewMode === 'list' ? 'text-blue-600 bg-white rounded-md shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-md transition-colors' }}" wire:click="$set('viewMode', 'list')">
                        <i class="fa-solid fa-list text-sm"></i>
                    </button>
                    <button class="p-2 {{ $viewMode === 'grid' ? 'text-blue-600 bg-white rounded-md shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-md transition-colors' }}" wire:click="$set('viewMode', 'grid')">
                        <i class="fa-solid fa-grip text-sm"></i>
                    </button>
                </div>
                
                <!-- Enhanced Sort Dropdown -->
                <div class="flex items-center gap-3">
                    
                    <!-- Sort Dropdown with Direction Toggle -->
                    <div class="flex items-center bg-gray-50 rounded-lg border border-gray-300 overflow-hidden">
                        <!-- Sort Field Selector -->
                        <div class="relative">
                            <select
                                wire:model.live="sortField"
                                class="appearance-none px-4 py-3 pl-4 pr-8 text-sm bg-transparent border-none focus:ring-0 focus:outline-none cursor-pointer"
                            >
                                <option value="due">Sort by Due Date</option>
                                <option value="name">Sort by Name</option>
                                <option value="priority">Sort by Priority</option>
                                <option value="created_at">Sort by Created</option>
                            </select>
                        </div>
                        
                        <!-- Sort Direction Toggle -->
                        <button
                            wire:click="sortBy('{{ $sortField }}')"
                            class="px-3 py-3 border-l border-gray-300 text-gray-400 hover:text-blue-600 transition-colors"
                            title="Toggle sort direction"
                        >
                            @if($sortDirection === 'asc')
                                <i class="fa-solid fa-arrow-up-wide-short text-sm"></i>
                            @else
                                <i class="fa-solid fa-arrow-down-wide-short text-sm"></i>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Active Filters -->
        @if($search || $statusFilter)
            <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-filter text-blue-600"></i>
                    <span class="text-sm font-semibold text-gray-700">Active filters:</span>
                </div>
                <div class="flex items-center gap-2">
                    @if($search)
                        <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                            <i class="fa-solid fa-magnifying-glass text-blue-600 text-xs"></i>
                            <span class="text-sm text-blue-800 font-medium">"{{ $search }}"</span>
                            <button wire:click="$set('search', '')" class="ml-1 w-5 h-5 bg-blue-100 hover:bg-blue-200 rounded-full flex items-center justify-center transition-colors duration-200">
                                <i class="fa-solid fa-xmark text-blue-600 text-xs"></i>
                            </button>
                        </div>
                    @endif
                    @if($statusFilter)
                        <div class="flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-lg">
                            <i class="fa-solid fa-check-circle text-green-600 text-xs"></i>
                            <span class="text-sm text-green-800 font-medium">{{ $statuses[$statusFilter] ?? $statusFilter }}</span>
                            <button wire:click="$set('statusFilter', '')" class="ml-1 w-5 h-5 bg-green-100 hover:bg-green-200 rounded-full flex items-center justify-center transition-colors duration-200">
                                <i class="fa-solid fa-xmark text-green-600 text-xs"></i>
                            </button>
                        </div>
                    @endif
                </div>
                <button wire:click="$set('search', ''); $set('statusFilter', '')" class="text-sm text-gray-500 hover:text-gray-700 underline ml-auto">
                    Clear all filters
                </button>
            </div>
        @endif
    </div>

    <!-- Only show table header in list view -->
    @if($viewMode === 'list')
        <!-- Enhanced File List Header -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 px-8 py-3">
            <div class="grid grid-cols-11 gap-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                <div class="col-span-5 flex items-center gap-2">
                    <i class="fa-solid fa-file-lines text-gray-400"></i>
                    Name
                </div>
                <div class="col-span-2 flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-gray-400"></i>
                    Due Date
                </div>
                <div class="col-span-2 flex items-center gap-2">
                    <i class="fa-solid fa-exclamation-triangle text-gray-400"></i>
                    Priority
                </div>
                <div class="col-span-2 flex items-center gap-2">
                    <i class="fa-solid fa-check-circle text-gray-400"></i>
                    Status
                </div>
                <!-- Removed Actions Column -->
            </div>
        </div>
    @endif

    <!-- Enhanced Requirements List -->
    <div class="flex-1 bg-white">
        @if($viewMode === 'grid')
            <!-- Grid View Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                @forelse($requirements as $requirement)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5 cursor-pointer" wire:click="$dispatch('showRequirementDetail', { requirementId: {{ $requirement->id }} })">
                        <div class="flex items-center justify-between mb-4">
                            @php
                                $extension = pathinfo($requirement->name, PATHINFO_EXTENSION);
                                $iconData = match(strtolower($extension)) {
                                    'pdf' => ['icon' => 'fa-file-pdf', 'color' => 'text-red-600'],
                                    'doc', 'docx' => ['icon' => 'fa-file-word', 'color' => 'text-purple-600'],
                                    'ppt', 'pptx' => ['icon' => 'fa-file-powerpoint', 'color' => 'text-orange-600'],
                                    default => ['icon' => 'fa-file', 'color' => 'text-purple-600']
                                };
                            @endphp
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fa-solid {{ $iconData['icon'] }} {{ $iconData['color'] }} text-xl"></i>
                            </div>
                            @if($requirement->due->isPast())
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Overdue</span>
                            @endif
                        </div>
                        
                        <h3 class="font-semibold text-gray-900 mb-2 truncate">{{ $requirement->name }}</h3>
                        <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $requirement->description }}</p>
                        
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $requirement->due->format('M j, Y') }}</div>
                                <div class="text-xs {{ $requirement->due->isPast() ? 'text-red-600' : 'text-gray-500' }}">
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
                        
                        <div class="pt-3 border-t border-gray-100">
                            @if($requirement->userSubmissions->count() > 0)
                                @php
                                    $status = $requirement->userSubmissions->first()->status;
                                    $statusConfig = match($status) {
                                        'approved' => ['color' => 'green', 'icon' => 'fa-check-circle'],
                                        'pending' => ['color' => 'yellow', 'icon' => 'fa-clock'],
                                        'rejected' => ['color' => 'red', 'icon' => 'fa-times-circle'],
                                        default => ['color' => 'gray', 'icon' => 'fa-circle']
                                    };
                                @endphp
                                <span class="inline-flex items-center text-xs font-semibold text-{{ $statusConfig['color'] }}-800">
                                    <i class="fa-solid {{ $statusConfig['icon'] }} mr-1 text-{{ $statusConfig['color'] }}-600"></i>
                                    {{ $requirement->userSubmissions->first()->status_text }}
                                </span>
                            @else
                                <span class="inline-flex items-center text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-circle-pause text-gray-500 mr-1"></i>
                                    Not Started
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <!-- Empty state remains the same -->
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
                            @if($search || $statusFilter)
                                We couldn't find any requirements matching your current search criteria. Try adjusting your filters or search terms.
                            @else
                                You don't have any requirements assigned yet. New requirements will appear here when they're created.
                            @endif
                        </p>
                        @if($search || $statusFilter)
                            <button wire:click="$set('search', ''); $set('statusFilter', '')" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl">
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
                    class="grid grid-cols-11 gap-6 items-center px-8 py-5 border-b border-gray-100 hover:bg-blue-50/50 cursor-pointer group transition-all duration-200 {{ $index % 2 === 1 ? 'bg-gray-50/30' : 'bg-white' }}"
                >
                    <!-- Name Column -->
                    <div class="col-span-5 flex items-center gap-4 min-w-0">
                        <div class="flex-shrink-0 relative">
                            @php
                                $extension = pathinfo($requirement->name, PATHINFO_EXTENSION);
                                $iconData = match(strtolower($extension)) {
                                    'pdf' => ['icon' => 'fa-file-pdf', 'color' => 'text-red-600', 'bg' => 'bg-red-100', 'hover_color' => 'group-hover:text-red-700', 'hover_bg' => 'group-hover:bg-red-200'],
                                    'doc', 'docx' => ['icon' => 'fa-file-word', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100', 'hover_color' => 'group-hover:text-purple-700', 'hover_bg' => 'group-hover:bg-purple-200'],
                                    'ppt', 'pptx' => ['icon' => 'fa-file-powerpoint', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100', 'hover_color' => 'group-hover:text-orange-700', 'hover_bg' => 'group-hover:bg-orange-200'],
                                    'xls', 'xlsx' => ['icon' => 'fa-file-excel', 'color' => 'text-green-600', 'bg' => 'bg-green-100', 'hover_color' => 'group-hover:text-green-700', 'hover_bg' => 'group-hover:bg-green-200'],
                                    default => ['icon' => 'fa-file', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100', 'hover_color' => 'group-hover:text-purple-700', 'hover_bg' => 'group-hover:bg-purple-200']
                                };
                            @endphp
                            <div class="w-10 h-10 {{ $iconData['bg'] }} {{ $iconData['hover_bg'] }} rounded-lg flex items-center justify-center group-hover:scale-105 transition-all duration-200">
                                <i class="fa-solid {{ $iconData['icon'] }} {{ $iconData['color'] }} {{ $iconData['hover_color'] }} text-lg transition-colors duration-200"></i>
                            </div>
                            @if($requirement->due->isPast())
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white">
                                    <i class="fa-solid fa-exclamation text-white text-xs absolute top-0 left-0.5"></i>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-700 transition-colors mb-1">
                                {{ $requirement->name }}
                            </p>
                            <p class="text-xs text-gray-500 line-clamp-1">{{ $requirement->description }}</p>
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
                            <span class="text-xs mt-1 {{ $requirement->due->isPast() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
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
                        <span class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-semibold border border-{{ $priorityConfig['color'] }}-200 bg-{{ $priorityConfig['color'] }}-50 text-{{ $priorityConfig['color'] }}-800 group-hover:shadow-sm transition-shadow">
                            <i class="fa-solid {{ $priorityConfig['icon'] }} mr-2 text-{{ $priorityConfig['color'] }}-600"></i>
                            {{ ucfirst($requirement->priority) }}
                        </span>
                    </div>

                    <!-- Status Column -->
                    <div class="col-span-2">
                        @if($requirement->userSubmissions->count() > 0)
                            @php
                                $status = $requirement->userSubmissions->first()->status;
                                $statusConfig = match($status) {
                                    'approved' => ['color' => 'green', 'icon' => 'fa-check-circle'],
                                    'pending' => ['color' => 'yellow', 'icon' => 'fa-clock'],
                                    'rejected' => ['color' => 'red', 'icon' => 'fa-times-circle'],
                                    default => ['color' => 'gray', 'icon' => 'fa-circle']
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-semibold border border-{{ $statusConfig['color'] }}-200 bg-{{ $statusConfig['color'] }}-50 text-{{ $statusConfig['color'] }}-800 group-hover:shadow-sm transition-shadow">
                                <i class="fa-solid {{ $statusConfig['icon'] }} mr-2 text-{{ $statusConfig['color'] }}-600"></i>
                                {{ $requirement->userSubmissions->first()->status_text }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 group-hover:shadow-sm transition-shadow">
                                <i class="fa-solid fa-circle-pause text-gray-500 mr-2"></i>
                                Not Started
                            </span>
                        @endif
                    </div>

                    <!-- Removed Actions Column -->
                </div>
            @empty
                <!-- Empty state remains the same -->
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
                        @if($search || $statusFilter)
                            We couldn't find any requirements matching your current search criteria. Try adjusting your filters or search terms.
                        @else
                            You don't have any requirements assigned yet. New requirements will appear here when they're created.
                        @endif
                    </p>
                    @if($search || $statusFilter)
                        <button wire:click="$set('search', ''); $set('statusFilter', '')" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fa-solid fa-refresh mr-2"></i>
                            Clear All Filters
                        </button>
                    @endif
                </div>
            @endforelse
        @endif
    </div>

    <!-- Enhanced Footer/Status Bar -->
    <div class="bg-white border-t border-gray-200 px-8 py-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-6 text-sm text-gray-600">
                @if($requirements->count() > 0)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-gray-400"></i>
                        <span>Showing <span class="font-semibold text-gray-900">{{ $requirements->count() }}</span> {{ $requirements->count() === 1 ? 'requirement' : 'requirements' }}</span>
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

    <!-- Include the reusable modal component -->
    @livewire('user.requirement-detail-modal')
</div>