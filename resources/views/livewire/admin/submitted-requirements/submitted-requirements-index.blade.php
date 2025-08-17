<div class="flex flex-col gap-4">
    <div class="flex flex-col gap-4 bg-white p-6 w-full rounded-lg shadow-md">
        <!-- Header and View Toggle -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Submitted Requirements</h2>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 bg-gray-100 p-1 rounded-lg">
                    <button 
                        wire:click="switchView('list')" 
                        class="p-2 rounded-md transition-colors {{ $viewMode === 'list' ? 'bg-white shadow-sm' : 'hover:bg-gray-200' }}"
                        title="List view"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button 
                        wire:click="switchView('grid')" 
                        class="p-2 rounded-md transition-colors {{ $viewMode === 'grid' ? 'bg-white shadow-sm' : 'hover:bg-gray-200' }}"
                        title="Grid view"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @foreach($categories as $key => $label)
                <button
                    wire:click="setCategory('{{ $key }}')"
                    class="px-4 py-2 text-sm rounded-md transition-colors {{ $category === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Default File View -->
        @if($category === 'file')
            <!-- Search and Status Filter in same row -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                <!-- Search Bar -->
                <div class="relative max-w-md w-full md:w-100px">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500" 
                        placeholder="Search files, or users..."
                    >
                </div>

                <!-- Status Filter and Reset Button -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2">
                        <label for="statusFilter" class="text-sm font-medium text-gray-700 whitespace-nowrap">Status:</label>
                        <select 
                            id="statusFilter" 
                            wire:model.live="statusFilter"
                            class="block p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="under_review">Under Review</option>
                            <option value="revision_needed">Revision Needed</option>
                            <option value="rejected">Rejected</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="text-sm text-gray-500">
                Showing {{ $submittedRequirements->firstItem() }} to {{ $submittedRequirements->lastItem() }} of {{ $submittedRequirements->total() }} results
            </div>

            @if($viewMode === 'list')
                <!-- List View for Files -->
                <div class="flex flex-col gap-4">
                    @forelse ($submittedRequirements as $submittedRequirement)
                        <a href="{{ route('admin.submitted-requirements.show', ['submitted_requirement' => $submittedRequirement, 'file_id' => $submittedRequirement->media->first()->id]) }}" class="flex items-center justify-between p-4 border-b hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center">
                                    @if($submittedRequirement->media->count() > 0)
                                        @foreach ($submittedRequirement->media as $media)
                                            <img src="{{ $media->getUrl() }}" alt="Media" class="w-12 h-12 object-cover rounded-full">
                                        @endforeach
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="font-semibold">
                                        @if($submittedRequirement->media->count() > 0)
                                            {{ $submittedRequirement->media->first()->file_name }}
                                        @else
                                            No file attached
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Requirement:</span> {{ $submittedRequirement->requirement->name }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Submitted by:</span> {{ $submittedRequirement->user->full_name }}
                                        @if($submittedRequirement->user->college)
                                            ({{ $submittedRequirement->user->college->name }})
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $submittedRequirement->status_badge }}">
                                    {{ $submittedRequirement->status === 'under_review' ? 'Needs Review' : $submittedRequirement->status_text }}
                                </span>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">
                                        {{ $submittedRequirement->submitted_at->diffForHumans() }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $submittedRequirement->submitted_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-500 py-4 text-center">No submitted requirements found.</p>
                    @endforelse
                </div>
            @else
                <!-- Grid View for Files -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse ($submittedRequirements as $submittedRequirement)
                        <a href="{{ route('admin.submitted-requirements.show', ['submitted_requirement' => $submittedRequirement, 'file_id' => $submittedRequirement->media->first()->id]) }}" class="flex flex-col p-4 border rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-semibold">
                                        @if($submittedRequirement->media->count() > 0)
                                            {{ $submittedRequirement->media->first()->file_name }}
                                        @else
                                            No file attached
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">Requirement:</span> {{ $submittedRequirement->requirement->name }}
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full {{ $submittedRequirement->status_badge }}">
                                    {{ $submittedRequirement->status === 'under_review' ? 'Needs Review' : $submittedRequirement->status_text }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Submitted by:</span> {{ $submittedRequirement->user->full_name }}
                                @if($submittedRequirement->user->college)
                                    <br>({{ $submittedRequirement->user->college->name }})
                                @endif
                            </p>
                            <div class="flex justify-between items-end mt-auto">
                                <div class="flex -space-x-2">
                                    @if($submittedRequirement->media->count() > 0)
                                        @foreach ($submittedRequirement->media as $media)
                                            <img src="{{ $media->getUrl() }}" alt="Media" class="w-12 h-12 object-cover rounded-full">
                                        @endforeach
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">
                                    {{ $submittedRequirement->submitted_at->diffForHumans() }}
                                </p>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-500 col-span-3 py-4 text-center">No submitted requirements found.</p>
                    @endforelse
                </div>
            @endif

            <div class="mt-4">
                {{ $submittedRequirements->links() }}
            </div>
        @else
            <!-- Grouped View (for categories other than 'file') -->
            @if($viewMode === 'list')
                <!-- List View for Groups -->
                <div class="flex flex-col gap-4">
                    @forelse ($groupedItems as $groupId => $group)
                        <a href="#" wire:click.prevent class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between bg-gray-50 p-4 hover:bg-gray-100">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                    <h3 class="font-semibold">{{ $group['name'] }}</h3>
                                    <span class="text-xs bg-gray-200 px-2 py-1 rounded-full">{{ $group['count'] }} items</span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-500 py-4 text-center">No groups found in this category.</p>
                    @endforelse
                </div>
            @else
                <!-- Grid View for Groups -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse ($groupedItems as $groupId => $group)
                        <a href="#" wire:click.prevent class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                            <div class="flex flex-col h-full">
                                <div class="flex items-center justify-between bg-gray-50 p-4 hover:bg-gray-100">
                                    <div class="flex items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                        <h3 class="font-semibold">{{ $group['name'] }}</h3>
                                    </div>
                                    <span class="text-xs bg-gray-200 px-2 py-1 rounded-full">{{ $group['count'] }} items</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-500 col-span-3 py-4 text-center">No groups found in this category.</p>
                    @endforelse
                </div>
            @endif
        @endif
    </div>
</div>