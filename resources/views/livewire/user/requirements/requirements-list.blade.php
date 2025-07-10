<div class="flex flex-col gap-4 w-full bg-white rounded-lg p-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-lg font-bold uppercase">Your Pending Items</h2>
        <span class="badge" style="background-color: {{ \App\Models\SubmittedRequirement::getPriorityColor('default') }}; color: white">{{ $requirements->total() }} items</span>
    </div>

    <!-- Search and Filters -->
    <div class="flex flex-col md:flex-row gap-4 w-full">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search"
            placeholder="Search requirements..." 
            class="input input-bordered w-full md:w-96"
        >
        
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
            <!-- Status Filter -->
            <div class="flex items-center gap-2">
                <span class="text-sm">Status:</span>
                <select 
                    wire:model.live="statusFilter"
                    class="select select-bordered select-sm"
                >
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Sort By -->
            <div class="flex items-center gap-2">
                <span class="text-sm">Sort by:</span>
                <select 
                    wire:model.live="sortField"
                    class="select select-bordered select-sm"
                >
                    <option value="due">Due Date</option>
                    <option value="name">Name</option>
                    <option value="priority">Priority</option>
                    <option value="created_at">Created At</option>
                </select>
                <button 
                    wire:click="sortBy('{{ $sortField }}')"
                    class="btn btn-sm btn-square"
                >
                    @if($sortDirection === 'asc')
                        <i class="fa-solid fa-sort-up"></i>
                    @else
                        <i class="fa-solid fa-sort-down"></i>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Requirements List -->
    <div class="flex flex-col gap-3">
        @forelse($requirements as $requirement)
            <div 
                wire:click="$dispatch('showRequirementDetail', { requirementId: {{ $requirement->id }} })"
                class="flex flex-col p-4 border rounded-lg hover:bg-gray-50 cursor-pointer"
            >
                <div class="flex justify-between">
                    <div class="flex flex-col">
                        <h3 class="font-semibold">{{ $requirement->name }}</h3>
                        <p class="text-sm text-gray-500 line-clamp-2">{{ $requirement->description }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="badge" style="background-color: {{ \App\Models\SubmittedRequirement::getPriorityColor($requirement->priority) }}; color: white">
                            {{ ucfirst($requirement->priority) }}
                        </span>
                        @if($requirement->userSubmissions->count() > 0)
                            <span class="badge" style="background-color: {{ \App\Models\SubmittedRequirement::getStatusColor($requirement->userSubmissions->first()->status) }}; color: white">
                                {{ $requirement->userSubmissions->first()->status_text }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                    <i class="fa-regular fa-calendar"></i>
                    <span>Due: {{ $requirement->due->format('M j, Y') }}</span>
                    <span>•</span>
                    <span>{{ $requirement->due->diffForHumans() }}</span>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                <i class="fa-regular fa-face-frown text-4xl mb-2"></i>
                <p>No requirements found</p>
            </div>
        @endforelse
    </div>

    <!-- Load More -->
    @if($requirements->hasMorePages())
        <div class="flex justify-center">
            <button wire:click="loadMore" class="btn btn-primary">
                Load More
            </button>
        </div>
    @endif

    <!-- Include the reusable modal component -->
    @livewire('user.requirement-detail-modal')
</div>