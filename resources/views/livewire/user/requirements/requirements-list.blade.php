<div class="flex flex-col gap-6 w-full bg-white rounded-xl shadow-sm p-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold uppercase text-gray-800">Your Pending Items</h2>
        <span class="badge badge-lg" style="background-color: {{ \App\Models\SubmittedRequirement::getPriorityColor('default') }}; color: white">{{ $requirements->count() }} items</span>
    </div>

    <!-- Enhanced Search and Filters -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-5 border border-gray-200">
        <!-- Search Bar -->
        <div class="mb-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search requirements..."
                    class="input input-bordered w-full pl-10 h-12 text-base rounded-lg border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                >
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <div wire:loading wire:target="search" class="loading loading-spinner loading-sm text-blue-500"></div>
                </div>
            </div>
        </div>

        <!-- Filters and Sorting Row -->
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center">
            <!-- Status Filter -->
            <div class="flex items-center gap-3 bg-white rounded-lg px-4 py-2 border border-gray-200 shadow-sm min-w-0 flex-shrink-0">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                    <i class="fa-solid fa-filter text-blue-500"></i>
                    <span>Status:</span>
                </div>
                <select
                    wire:model.live="statusFilter"
                    class="select select-bordered select-sm bg-transparent border-0 focus:outline-none text-gray-700 font-medium min-w-[120px]"
                >
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Enhanced Sort Controls -->
            <div class="flex items-center gap-3 bg-white rounded-lg px-4 py-2 border border-gray-200 shadow-sm min-w-0">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                    <i class="fa-solid fa-sort text-green-500"></i>
                    <span>Sort by:</span>
                </div>
                <div class="flex items-center gap-2">
                    <select
                        wire:model.live="sortField"
                        class="select select-bordered select-sm bg-transparent border-0 focus:outline-none text-gray-700 font-medium min-w-[120px]"
                    >
                        <option value="due">Due Date</option>
                        <option value="name">Name</option>
                        <option value="priority">Priority</option>
                        <option value="created_at">Created At</option>
                    </select>
                    <div class="divider divider-horizontal mx-1"></div>
                    <button
                        wire:click="sortBy('{{ $sortField }}')"
                        class="btn btn-sm btn-circle bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 border-0 text-white shadow-sm hover:shadow-md transition-all duration-200"
                        title="{{ $sortDirection === 'asc' ? 'Sort Descending' : 'Sort Ascending' }}"
                    >
                        @if($sortDirection === 'asc')
                            <i class="fa-solid fa-arrow-up-short-wide text-sm"></i>
                        @else
                            <i class="fa-solid fa-arrow-down-wide-short text-sm"></i>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Quick Sort Buttons -->
            <div class="flex items-center gap-2 ml-auto">
                <span class="text-xs text-gray-500 font-medium">Quick sort:</span>
                <div class="btn-group">
                    <button
                        wire:click="quickSort('due')"
                        class="btn btn-xs {{ $sortField === 'due' ? 'btn-primary' : 'btn-outline' }} transition-all duration-200"
                        title="Sort by Due Date"
                    >
                        <i class="fa-regular fa-calendar text-xs"></i>
                        <span class="hidden sm:inline ml-1">Due</span>
                    </button>
                    <button
                        wire:click="quickSort('priority')"
                        class="btn btn-xs {{ $sortField === 'priority' ? 'btn-warning' : 'btn-outline' }} transition-all duration-200"
                        title="Sort by Priority"
                    >
                        <i class="fa-solid fa-exclamation text-xs"></i>
                        <span class="hidden sm:inline ml-1">Priority</span>
                    </button>
                    <button
                        wire:click="quickSort('name')"
                        class="btn btn-xs {{ $sortField === 'name' ? 'btn-info' : 'btn-outline' }} transition-all duration-200"
                        title="Sort by Name"
                    >
                        <i class="fa-solid fa-font text-xs"></i>
                        <span class="hidden sm:inline ml-1">Name</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Filters Display -->
        @if($search || $statusFilter)
            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-200">
                <span class="text-xs font-medium text-gray-500">Active filters:</span>
                @if($search)
                    <span class="badge badge-sm bg-blue-100 text-blue-800 border-blue-200">
                        <i class="fa-solid fa-magnifying-glass text-xs mr-1"></i>
                        "{{ $search }}"
                        <button wire:click="$set('search', '')" class="ml-1 hover:text-blue-600">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </span>
                @endif
                @if($statusFilter)
                    <span class="badge badge-sm bg-green-100 text-green-800 border-green-200">
                        <i class="fa-solid fa-filter text-xs mr-1"></i>
                        {{ $statuses[$statusFilter] ?? $statusFilter }}
                        <button wire:click="$set('statusFilter', '')" class="ml-1 hover:text-green-600">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </span>
                @endif
                <button wire:click="clearAllFilters" class="text-xs text-gray-500 hover:text-gray-700 underline ml-2">
                    Clear all
                </button>
            </div>
        @endif
    </div>

    <!-- Requirements List -->
    <div class="flex flex-col gap-3">
        @forelse($requirements as $requirement)
            <div
                wire:click="$dispatch('showRequirementDetail', { requirementId: {{ $requirement->id }} })"
                class="flex flex-col p-5 border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 cursor-pointer transition-all duration-200 hover:shadow-md group"
            >
                <div class="flex justify-between">
                    <div class="flex flex-col flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-800 group-hover:text-gray-900 transition-colors duration-200">{{ $requirement->name }}</h3>
                        <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ $requirement->description }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2 ml-4 flex-shrink-0">
                        <span class="badge badge-lg font-medium" style="background-color: {{ \App\Models\SubmittedRequirement::getPriorityColor($requirement->priority) }}; color: white">
                            {{ ucfirst($requirement->priority) }}
                        </span>
                        @if($requirement->userSubmissions->count() > 0)
                            <span class="badge" style="background-color: {{ \App\Models\SubmittedRequirement::getStatusColor($requirement->userSubmissions->first()->status) }}; color: white">
                                {{ $requirement->userSubmissions->first()->status_text }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-500 mt-3 pt-3 border-t border-gray-100">
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-blue-500"></i>
                        <span class="font-medium">Due: {{ $requirement->due->format('M j, Y') }}</span>
                    </div>
                    <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                    <span class="text-xs {{ $requirement->due->isPast() ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                        {{ $requirement->due->diffForHumans() }}
                    </span>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-16 text-gray-500">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-regular fa-face-frown text-2xl text-gray-400"></i>
                </div>
                <p class="text-lg font-medium text-gray-600">No requirements found</p>
                <p class="text-sm text-gray-400 mt-1">Try adjusting your search or filters</p>
            </div>
        @endforelse
    </div>

    <!-- Include the reusable modal component -->
    @livewire('user.requirement-detail-modal')
</div>