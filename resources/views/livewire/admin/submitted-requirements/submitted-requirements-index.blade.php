<div class="flex flex-col gap-2 w-[92%] mx-auto mb-6">
    <!-- Header / Toolbar -->
    <div class="flex justify-between items-center bg-1C7C54 text-white p-4 rounded-2xl shadow-md">
        <div class="flex items-center gap-3">
            <div class="pl-3 bg-1C7C54/10 rounded-xl">
                <i class="fa-solid fa-paper-plane text-white text-2xl"></i>
            </div>
            <h2 class="text-xl md:text-xl font-semibold">Submitted Requirements</h2>
        </div>

        <!-- Always show view toggle buttons -->
        <div class="flex items-center gap-1 bg-white/20 p-1 rounded-xl">
            <!-- List Toggle -->
            <button 
                wire:click="switchView('list')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                title="List view"
            >
                <i class="fas fa-list"></i>
            </button>
            <!-- Grid Toggle -->
            <button 
                wire:click="switchView('grid')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                title="Grid view"
            >
                <i class="fas fa-th"></i>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col gap-6">

        @if($activeSemester)
            <!-- Filter Bar -->
            <div class="bg-DEF4C6 p-4 rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-sm">
                <!-- Category Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    @foreach($categories as $key => $label)
                        <button
                            wire:click="setCategory('{{ $key }}')"
                            class="px-4 py-2 text-sm rounded-xl font-medium transition-colors 
                                   {{ $category === $key ? 'bg-1C7C54 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-73E2A7 hover:text-1B512D' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                @if($category === 'file')
                    <!-- Search + Status Filter -->
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <!-- Search -->
                        <div class="relative max-w-md w-[300px]">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-1C7C54 text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                class="block w-[300px] p-2 pl-9 text-sm text-1B512D border border-DEF4C6 rounded-xl bg-white focus:ring-1C7C54 focus:border-1C7C54" 
                                placeholder="Search files or users..."
                            >
                        </div>

                        <!-- Status -->
                        <div class="flex items-center gap-2">
                            <label for="statusFilter" class="text-sm font-medium text-1B512D whitespace-nowrap">Status:</label>
                            <select 
                                id="statusFilter" 
                                wire:model.live="statusFilter"
                                class="block p-2 text-sm text-1B512D border border-DEF4C6 rounded-xl bg-white focus:ring-1C7C54 focus:border-1C7C54"
                            >
                                <option value="">All Statuses</option>
                                <option value="under_review">Under Review</option>
                                <option value="revision_needed">Revision Needed</option>
                                <option value="rejected">Rejected</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Content -->
        <div class="overflow-y-auto" style="max-height: calc(100vh - 250px);">
            @if($activeSemester)

                @if($category === 'file')
                    <div class="mb-4">
                        {{ $submittedRequirements->links('livewire.pagination') }}
                    </div>

                    @if($viewMode === 'list')
                        <!-- List View with Column Layout -->
                        <div class="flex flex-col gap-2 ml-2 mr-2 mb-2">
                            <!-- Column Headers -->
                            <div class="grid grid-cols-12 gap-4 px-4 py-2 bg-DEF4C6/30 rounded-lg text-sm font-semibold text-1B512D">
                                <div class="col-span-3">File</div>
                                <div class="col-span-2">Requirement</div>
                                <div class="col-span-3">Submitted By</div>
                                <div class="col-span-2">Status</div>
                                <div class="col-span-2">Date Submitted</div>
                            </div>
                            
                            <!-- File Items -->
                            @forelse ($submittedRequirements as $submittedRequirement)
                                <a href="{{ route('admin.submitted-requirements.show', ['submitted_requirement' => $submittedRequirement, 'file_id' => $submittedRequirement->media->first()->id]) }}" 
                                   class="grid grid-cols-12 gap-4 p-4 bg-white rounded-xl shadow-sm hover:shadow-md transition items-center">
                                    <!-- File Icon & Name -->
                                    <div class="col-span-3 flex items-center gap-3">
                                        <div class="w-8 h-8 flex items-center justify-center">
                                            <i class="fas {{ $submittedRequirement->getFileIcon() }} {{ $submittedRequirement->getFileIconColor() }} text-xl"></i>
                                        </div>
                                        <span class="text-sm font-medium text-1B512D truncate" title="{{ $submittedRequirement->media->first()->file_name ?? 'No file attached' }}">
                                            {{ $submittedRequirement->media->first()->file_name ?? 'No file attached' }}
                                        </span>
                                    </div>
                                    
                                    <!-- Requirement -->
                                    <div class="col-span-2 text-sm text-gray-600 truncate" title="{{ $submittedRequirement->requirement->name }}">
                                        {{ $submittedRequirement->requirement->name }}
                                    </div>
                                    
                                    <!-- Submitted By -->
                                    <div class="col-span-3 text-sm text-gray-600 truncate" title="{{ $submittedRequirement->user->full_name }}@if($submittedRequirement->user->college) ({{ $submittedRequirement->user->college->name }})@endif">
                                        {{ $submittedRequirement->user->full_name }}
                                        @if($submittedRequirement->user->college)
                                            <span class="text-xs">({{ $submittedRequirement->user->college->name }})</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="col-span-2 flex items-center">
                                        <span class="px-2 py-1 font-semibold text-xs rounded-full {{ $submittedRequirement->status_badge }}">
                                            {{ $submittedRequirement->status === 'under_review' ? 'Needs Review' : $submittedRequirement->status_text }}
                                        </span>
                                    </div>
                                    
                                    <!-- Date -->
                                    <div class="col-span-2 text-xs text-gray-400">
                                        {{ $submittedRequirement->submitted_at->diffForHumans() }}
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-col items-center justify-center py-8 text-gray-400 col-span-12">
                                    <i class="fas fa-folder-open text-4xl mb-2"></i>
                                    <p>No submitted requirements found.</p>
                                </div>
                            @endforelse
                        </div>

                    @else
                        <!-- Grid View -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-2">
                            @forelse ($submittedRequirements as $submittedRequirement)
                                <a href="{{ route('admin.submitted-requirements.show', ['submitted_requirement' => $submittedRequirement, 'file_id' => $submittedRequirement->media->first()->id]) }}" 
                                   class="bg-white rounded-2xl shadow-md hover:shadow-lg transition p-6 flex flex-col items-center text-center gap-3">
                                    <!-- Icon -->
                                    <div class="w-16 h-16 flex items-center justify-center bg-DEF4C6 rounded-full">
                                        <i class="fas {{ $submittedRequirement->getFileIcon() }} {{ $submittedRequirement->getFileIconColor() }} text-2xl"></i>
                                    </div>
                                    <!-- File Info -->
                                    <h3 class="font-semibold text-1B512D truncate w-full" title="{{ $submittedRequirement->media->first()->file_name ?? 'No file attached' }}">
                                        {{ $submittedRequirement->media->first()->file_name ?? 'No file attached' }}
                                    </h3>
                                    <p class="text-sm text-gray-600 truncate w-full" title="{{ $submittedRequirement->requirement->name }}">
                                        <span class="font-medium text-1C7C54">Requirement:</span> {{ $submittedRequirement->requirement->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate w-full" title="{{ $submittedRequirement->user->full_name }}@if($submittedRequirement->user->college) ({{ $submittedRequirement->user->college->name }})@endif">
                                        Submitted by: {{ $submittedRequirement->user->full_name }}
                                        @if($submittedRequirement->user->college)
                                            <br>({{ $submittedRequirement->user->college->name }})
                                        @endif
                                    </p>
                                    <!-- Status -->
                                    <span class="px-3 py-1 text-xs rounded-full {{ $submittedRequirement->status_badge }}">
                                        {{ $submittedRequirement->status === 'under_review' ? 'Needs Review' : $submittedRequirement->status_text }}
                                    </span>
                                    <!-- Time -->
                                    <p class="text-xs text-gray-400">{{ $submittedRequirement->submitted_at->diffForHumans() }}</p>
                                </a>
                            @empty
                                <div class="flex flex-col items-center justify-center py-8 text-gray-400 col-span-3">
                                    <i class="fas fa-folder-open text-4xl mb-2"></i>
                                    <p>No submitted requirements found.</p>
                                </div>
                            @endforelse
                        </div>
                    @endif

                @else
                    <!-- Groups (other categories) - Apply view mode here too -->
                    @if($viewMode === 'list')
                        <!-- List View for Groups -->
                        <div class="flex flex-col gap-2 ml-2 mr-2 mb-2">
                            <!-- Column Headers -->
                            <div class="grid grid-cols-12 gap-4 px-4 py-2 bg-DEF4C6/30 rounded-lg text-sm font-semibold text-1B512D">
                                <div class="col-span-10">Requirement Name</div>
                                <div class="col-span-2">Items Count</div>
                            </div>
                            
                            <!-- Group Items -->
                            @forelse ($groupedItems as $groupId => $group)
                                <a href="{{ route('admin.submitted-requirements.requirement', ['requirement_id' => $groupId]) }}" 
                                class="grid grid-cols-12 gap-4 p-4 bg-white rounded-xl shadow-sm hover:shadow-md transition items-center">
                                    <!-- Group Name -->
                                    <div class="col-span-10 flex items-center gap-3">
                                        <i class="fas fa-folder text-1C7C54 text-xl"></i>
                                        <span class="text-sm font-medium text-1B512D truncate" title="{{ $group['name'] }}">
                                            {{ $group['name'] }}
                                        </span>
                                    </div>
                                    
                                    <!-- Items Count -->
                                    <div class="col-span-2 flex items-center">
                                        <span class="px-2 py-1 text-xs bg-DEF4C6 text-1C7C54 rounded-full">
                                            {{ $group['count'] }} {{ $group['count'] == 1 ? 'item' : 'items' }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-col items-center justify-center py-8 text-gray-400 col-span-12">
                                    <i class="fas fa-folder-open text-4xl mb-2"></i>
                                    <p>No requirements found.</p>
                                </div>
                            @endforelse
                        </div>
                    @else
                        <!-- Grid View for Groups (default) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-3">
                            @forelse ($groupedItems as $groupId => $group)
                                <a href="{{ route('admin.submitted-requirements.requirement', ['requirement_id' => $groupId]) }}" 
                                class="bg-white rounded-2xl shadow-md hover:shadow-lg transition p-6 flex flex-col gap-3">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-folder text-1C7C54 text-xl"></i>
                                        <h3 class="font-semibold text-1B512D truncate" title="{{ $group['name'] }}">{{ $group['name'] }}</h3>
                                    </div>
                                    <span class="text-xs bg-DEF4C6 text-1C7C54 px-2 py-1 rounded-full w-fit">
                                        {{ $group['count'] }} {{ $group['count'] == 1 ? 'item' : 'items' }}
                                    </span>
                                </a>
                            @empty
                                <div class="flex flex-col items-center justify-center py-8 text-gray-400 col-span-3">
                                    <i class="fas fa-folder-open text-4xl mb-2"></i>
                                    <p>No requirements found.</p>
                                </div>
                            @endforelse
                        </div>
                    @endif
                @endif
            @else
                <div class="bg-DEF4C6 text-1B512D p-6 rounded-xl flex items-center gap-3 border border-B1CF5F">
                    <i class="fa-solid fa-triangle-exclamation text-B1CF5F text-xl"></i>
                    <span>No active semester. Please activate a semester to view submitted requirements.</span>
                </div>
            @endif
        </div>
    </div>
</div>