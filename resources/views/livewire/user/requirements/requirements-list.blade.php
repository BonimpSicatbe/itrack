<div class="flex flex-col w-full bg-gray-50 min-h-screen">
    <!-- Enhanced Professional Header -->
    <div class="bg-white border-b border-gray-200 px-8 py-6 shadow-sm">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-5">
                <div class="relative">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-folder-open text-white text-xl"></i>
                    </div>
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Requirements Manager</h1>
                    <p class="text-sm text-gray-600 mt-1">Organize and track your document requirements</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 shadow-sm">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-file-lines text-white text-sm"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-blue-900">{{ $requirements->count() }}</span>
                        <span class="text-sm text-blue-700 ml-1">{{ $requirements->count() === 1 ? 'item' : 'items' }}</span>
                    </div>
                </div>
                <button class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span class="text-sm font-medium">New</span>
                </button>
            </div>
        </div>
    </div>

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
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Enhanced View Controls -->
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                    <button class="p-2 text-blue-600 bg-white rounded-md shadow-sm">
                        <i class="fa-solid fa-list text-sm"></i>
                    </button>
                    <button class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fa-solid fa-grip text-sm"></i>
                    </button>
                </div>
                
                <!-- Enhanced Sort Dropdown -->
                <div class="relative">
                    <select
                        wire:model.live="sortField"
                        class="appearance-none px-4 py-3 pr-12 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white focus:outline-none transition-all duration-200 cursor-pointer"
                    >
                        <option value="due">Sort by Due Date</option>
                        <option value="name">Sort by Name</option>
                        <option value="priority">Sort by Priority</option>
                        <option value="created_at">Sort by Created</option>
                    </select>
                    <button
                        wire:click="sortBy('{{ $sortField }}')"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        @if($sortDirection === 'asc')
                            <i class="fa-solid fa-sort-up text-sm"></i>
                        @else
                            <i class="fa-solid fa-sort-down text-sm"></i>
                        @endif
                    </button>
                </div>
                
                <button class="p-3 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                </button>
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
                <button wire:click="clearAllFilters" class="text-sm text-gray-500 hover:text-gray-700 underline ml-auto">
                    Clear all filters
                </button>
            </div>
        @endif
    </div>

    <!-- Enhanced File List Header -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 px-8 py-3">
        <div class="grid grid-cols-12 gap-6 text-xs font-semibold text-gray-600 uppercase tracking-wider">
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
            <div class="col-span-1 text-center">Actions</div>
        </div>
    </div>

    <!-- Enhanced Requirements List -->
    <div class="flex-1 bg-white">
        @forelse($requirements as $index => $requirement)
            <div
                wire:click="$dispatch('showRequirementDetail', { requirementId: {{ $requirement->id }} })"
                class="grid grid-cols-12 gap-6 items-center px-8 py-5 border-b border-gray-100 hover:bg-blue-50/50 cursor-pointer group transition-all duration-200 {{ $index % 2 === 1 ? 'bg-gray-50/30' : 'bg-white' }}"
            >
                <!-- Enhanced Name Column with Updated File Type Colors -->
                <div class="col-span-5 flex items-center gap-4 min-w-0">
                    <div class="flex-shrink-0 relative">
                        @php
                            $extension = pathinfo($requirement->name, PATHINFO_EXTENSION);
                            $iconData = match(strtolower($extension)) {
                                'pdf' => ['icon' => 'fa-file-pdf', 'color' => 'text-red-600', 'bg' => 'bg-red-100', 'hover_color' => 'group-hover:text-red-700', 'hover_bg' => 'group-hover:bg-red-200'],
                                'doc', 'docx' => ['icon' => 'fa-file-word', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100', 'hover_color' => 'group-hover:text-purple-700', 'hover_bg' => 'group-hover:bg-purple-200'],
                                'ppt', 'pptx' => ['icon' => 'fa-file-powerpoint', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100', 'hover_color' => 'group-hover:text-orange-700', 'hover_bg' => 'group-hover:bg-orange-200'],
                                'xls', 'xlsx' => ['icon' => 'fa-file-excel', 'color' => 'text-green-600', 'bg' => 'bg-green-100', 'hover_color' => 'group-hover:text-green-700', 'hover_bg' => 'group-hover:bg-green-200'],
                                'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp' => ['icon' => 'fa-file-image', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100', 'hover_color' => 'group-hover:text-blue-700', 'hover_bg' => 'group-hover:bg-blue-200'],
                                'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm' => ['icon' => 'fa-file-video', 'color' => 'text-red-600', 'bg' => 'bg-red-100', 'hover_color' => 'group-hover:text-red-700', 'hover_bg' => 'group-hover:bg-red-200'],
                                'mp3', 'wav', 'flac', 'aac', 'ogg' => ['icon' => 'fa-file-audio', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100', 'hover_color' => 'group-hover:text-purple-700', 'hover_bg' => 'group-hover:bg-purple-200'],
                                'zip', 'rar', '7z', 'tar', 'gz' => ['icon' => 'fa-file-zipper', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100', 'hover_color' => 'group-hover:text-yellow-700', 'hover_bg' => 'group-hover:bg-yellow-200'],
                                'txt', 'rtf' => ['icon' => 'fa-file-lines', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100', 'hover_color' => 'group-hover:text-purple-700', 'hover_bg' => 'group-hover:bg-purple-200'],
                                'csv' => ['icon' => 'fa-file-csv', 'color' => 'text-green-600', 'bg' => 'bg-green-100', 'hover_color' => 'group-hover:text-green-700', 'hover_bg' => 'group-hover:bg-green-200'],
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

                <!-- Enhanced Due Date Column -->
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

                <!-- Enhanced Priority Column -->
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

                <!-- Enhanced Status Column -->
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

                <!-- Enhanced Actions Column -->
                <div class="col-span-1">
                    <div class="flex items-center justify-center">
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                            <button class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Details">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                            <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors" title="More Options">
                                <i class="fa-solid fa-ellipsis-h text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- Enhanced Empty State -->
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
                    <button wire:click="clearAllFilters" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="fa-solid fa-refresh mr-2"></i>
                        Clear All Filters
                    </button>
                @endif
            </div>
        @endforelse
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