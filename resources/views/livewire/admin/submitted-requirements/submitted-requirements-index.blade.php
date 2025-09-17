<div class="flex flex-col gap-2 mb-6">
    <!-- Header / Toolbar -->
    <div class="flex justify-between items-center text-white p-4 rounded-xl shadow-md" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="flex items-center gap-3">
            <div class="pl-3 bg-1C7C54/10 rounded-xl">
                <i class="fa-solid fa-paper-plane text-white text-2xl"></i>
            </div>
            <h2 class="text-xl md:text-xl font-semibold">Submitted Requirements</h2>
        </div>

        <!-- View toggle buttons -->
        <div class="flex items-center gap-1 bg-white/20 p-1 rounded-xl">
            <button 
                wire:click="switchView('list')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                title="List view"
            >
                <i class="fas fa-bars"></i>
            </button>
            <button 
                wire:click="switchView('grid')" 
                class="p-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                title="Grid view"
            >
                <i class="fas fa-border-all"></i>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col gap-4">
        @if($activeSemester)
            <!-- Filter Bar -->
            <div class="rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left: Search + Status Filter (only if category is 'file') -->
                @if($category === 'file')
                    <div class="flex flex-col sm:flex-row items-center gap-4 p-2">
                        <div class="flex items-center gap-2">
                            <select 
                                id="statusFilter" 
                                wire:model.live="statusFilter"
                                class="block p-2 w-[150px] text-sm text-gray-500 border border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 rounded-xl"
                            >
                                <option value="">All Statuses</option>
                                <option value="under_review">Under Review</option>
                                <option value="revision_needed">Revision Needed</option>
                                <option value="rejected">Rejected</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>

                        <div class="relative max-w-md w-full md:w-sm">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-gray-500 text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                class="block w-sm p-2 pl-9 text-sm text-1B512D border border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 rounded-xl" 
                                placeholder="Search files or users..."
                            >
                        </div>
                    </div>
                @endif

                <!-- Right: Category Buttons -->
                <div class="flex flex-wrap items-center gap-1 border border-gray-300 shadow-sm p-1 rounded-xl bg-white font-semibold ml-auto mr-2">
                    @foreach($categories as $key => $label)
                        <button
                            wire:click="setCategory('{{ $key }}')"
                            class="px-4 py-2 text-sm rounded-lg font-medium transition-colors 
                                {{ $category === $key ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-green-600/20 hover:text-1B512D' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Content -->
            <div class="overflow-y-auto" style="max-height: calc(100vh - 250px);">
                @if($category === 'file')

                    @if($viewMode === 'list')
                        <!-- List View -->
                        <div class="flex flex-col gap-2 mb-2">                            
                            <!-- Column Headers -->
                            <div class="grid grid-cols-12 gap-4 px-4 py-3 bg-green-700 rounded-xl text-xs font-semibold text-white uppercase">
                                <div class="col-span-3">File</div>
                                <div class="col-span-2">Requirement</div>
                                <div class="col-span-3">Submitted By</div>
                                <div class="col-span-2">Status</div>
                                <div class="col-span-2">Date Submitted</div>
                            </div>
                            
                            <!-- File Items -->
                            @forelse ($submittedRequirements as $submittedRequirement)
                                @php
                                    $media = $submittedRequirement->media->first();
                                    $user = $submittedRequirement->user;
                                    $requirement = $submittedRequirement->requirement;
                                @endphp
                                <a href="{{ route('admin.submitted-requirements.show', ['submitted_requirement' => $submittedRequirement, 'file_id' => $media->id]) }}" 
                                   class="grid grid-cols-12 gap-4 p-4 text-gray-500 bg-white rounded-xl border border-gray-300 hover:bg-green-50 transition items-center">
                                    <!-- File Icon & Name -->
                                    <div class="col-span-3 flex items-center gap-3">
                                        <div class="w-8 h-8 flex items-center justify-center">
                                            <i class="fas {{ $submittedRequirement->getFileIcon() }} {{ $submittedRequirement->getFileIconColor() }} text-xl"></i>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-800 truncate" title="{{ $media->file_name ?? 'No file attached' }}">
                                            {{ $media->file_name ?? 'No file attached' }}
                                        </span>
                                    </div>
                                    
                                    <!-- Requirement -->
                                    <div class="col-span-2 text-sm text-gray-500 truncate" title="{{ $requirement->name }}">
                                        {{ $requirement->name }}
                                    </div>
                                    
                                    <!-- Submitted By -->
                                    <div class="col-span-3 text-sm text-gray-600 truncate" title="{{ $user->full_name }}@if($user->college) ({{ $user->college->name }})@endif">
                                        {{ $user->full_name }}
                                        @if($user->college)
                                            <span class="text-xs">({{ $user->college->name }})</span>
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
                                <div class="flex flex-col items-center justify-center py-8 text-gray-500 col-span-12">
                                    <i class="fa-solid fa-folder-open text-3xl text-gray-300 mb-2"></i>
                                    <p class="text-sm font-semibold text-gray-500">No submitted requirements found.</p>
                                    @if($search)
                                        <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    @else
                        <!-- Grid View -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5">
                            @forelse ($submittedRequirements as $submittedRequirement)
                                @php
                                    $media = $submittedRequirement->media->first();
                                    $user = $submittedRequirement->user;
                                    $requirement = $submittedRequirement->requirement;
                                @endphp
                                
                                <a href="{{ route('admin.submitted-requirements.show', ['submitted_requirement' => $submittedRequirement, 'file_id' => $media->id]) }}" 
                                class="group bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-300 overflow-hidden transform hover:-translate-y-1 flex flex-col mt-2">
                                    <!-- Header with status -->
                                    <div class="px-4 py-3 border-b-1 border-gray-300 flex justify-between items-center">
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $submittedRequirement->status_badge }} capitalize">
                                            {{ $submittedRequirement->status === 'under_review' ? 'Needs Review' : $submittedRequirement->status_text }}
                                        </span>
                                        <span class="text-xs text-gray-400">{{ $submittedRequirement->submitted_at->diffForHumans() }}</span>
                                    </div>
                                    
                                    <!-- File info section -->
                                    <div class="p-5 flex-grow">
                                        <div class="flex items-center mb-4">
                                            <div class="w-12 h-12 flex items-center justify-center rounded-xl mr-4">
                                                <i class="fas {{ $submittedRequirement->getFileIcon() }} {{ $submittedRequirement->getFileIconColor() }} text-2xl"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-gray-800 text-sm truncate" title="{{ $media->file_name ?? 'No file attached' }}">
                                                    {{ $media->file_name ?? 'No file attached' }}
                                                </h3>
                                                <p class="text-sm text-green-700 mb-2 font-medium truncate" title="{{ $requirement->name }}">
                                                    {{ $requirement->name }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Submitted by section -->
                                    <div class="px-5 py-3 bg-green-700 border-t border-gray-100">
                                        <p class="text-xs text-white truncate" title="{{ $user->full_name }}@if($user->college) ({{ $user->college->name }})@endif">
                                            <span class="font-medium">Submitted by:</span> {{ $user->full_name }}
                                            @if($user->college)
                                                <span class="block text-white text-xs mt-1 truncate">({{ $user->college->name }})</span>
                                            @endif
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <div class="col-span-full py-12 text-center">
                                    <i class="fa-solid fa-folder-open text-3xl text-gray-300 mb-2"></i>
                                    <p class="text-sm font-semibold text-gray-500">No submitted requirements found.</p>
                                    @if($search)
                                        <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    @endif
                @else
                    <!-- Groups (other categories) -->
                    @if($viewMode === 'list')
                        <!-- List View for Groups -->
                        <div class="flex flex-col gap-2 ml-2 mr-2 mb-2">
                            <div class="grid grid-cols-12 gap-4 px-4 py-3 bg-green-700 rounded-lg text-xs font-semibold text-white uppercase">
                                <div class="col-span-10">Requirement Name</div>
                                <div class="col-span-2">Items Count</div>
                            </div>
                            
                            @forelse ($groupedItems as $groupId => $group)
                                <a href="{{ route('admin.submitted-requirements.requirement', ['requirement_id' => $groupId]) }}" 
                                class="grid grid-cols-12 gap-4 p-4 bg-white rounded-xl border border-gray-300 hover:bg-green-50">
                                    <div class="col-span-10 flex items-center gap-3">
                                        <i class="fas fa-folder-open text-green-700 text-xl"></i>
                                        <span class="text-sm font-semibold text-gray-800 truncate" title="{{ $group['name'] }}">
                                            {{ $group['name'] }}
                                        </span>
                                    </div>
                                    <div class="col-span-2 flex items-center">
                                        <span class="px-2 py-1 text-xs bg-DEF4C6 text-1C7C54 font-semibold rounded-full">
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
                        <!-- Grid View for Groups -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-9 gap-8 mb-6 mt-2">
                            @forelse ($groupedItems as $groupId => $group)
                                <a href="{{ route('admin.submitted-requirements.requirement', ['requirement_id' => $groupId]) }}" 
                                class="flex flex-col items-center text-center gap-3 group transition-all">
                                
                                    <!-- Folder Icon with Count -->
                                    <div class="relative">
                                        <i class="fas fa-folder-open text-8xl text-green-700 hover:text-green-800"></i>

                                        <!-- Count Badge -->
                                        <span class="absolute -top-0.5 -right-3 bg-DEF4C6 text-gray-800 text-sm font-bold 
                                                    rounded-full w-8 h-8 flex items-center justify-center shadow-md 
                                                    group-hover:bg-2A7F3F">
                                            {{ $group['count'] }}
                                        </span>
                                    </div>

                                    <!-- Requirement Name -->
                                    <h3 class="font-semibold text-1B512D truncate text-sm max-w-[160px] group-hover:text-2A7F3F" 
                                        title="{{ $group['name'] }}">
                                        {{ $group['name'] }}
                                    </h3>
                                </a>
                            @empty
                                <!-- Empty State -->
                                <div class="flex flex-col items-center justify-center py-10 text-gray-400 col-span-full">
                                    <i class="fas fa-folder-open text-8xl mb-3"></i>
                                    <p class="text-sm">No requirements found.</p>
                                </div>
                            @endforelse
                        </div>
                    @endif
                @endif
            </div>
        @else
            <div class="flex items-center p-4 bg-[#DEF4C6] text-[#1B512D] rounded-lg shadow-lg">
                <i class="fa-solid fa-triangle-exclamation text-lg mr-3"></i>
                <div>
                    <h3 class="font-bold">No Active Semester</h3>
                    <div class="text-xs">Please activate a semester to view submitted requirements.</div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .group:hover .folder-icon {
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            transform: translateY(-2px);
        }
    </style>
</div>