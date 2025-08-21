<div class="flex flex-col gap-4 h-full file-manager">
    {{-- Search --}}
    <div class="flex items-center gap-2">
        <div class="relative flex-1 max-w-md">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                class="input input-bordered input-sm w-full pl-10" 
                placeholder="Search by file name..."
            />
            <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
        </div>
    </div>

    {{-- View Toggle and Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="join">
                <button 
                    wire:click="$set('viewMode', 'grid')"
                    class="btn btn-sm join-item {{ $viewMode === 'grid' ? 'btn-primary' : 'btn-outline' }}"
                    title="Grid view"
                >
                    <i class="fa-solid fa-th text-xs"></i>
                </button>
                <button 
                    wire:click="$set('viewMode', 'list')"
                    class="btn btn-sm join-item {{ $viewMode === 'list' ? 'btn-primary' : 'btn-outline' }}"
                    title="List view"
                >
                    <i class="fa-solid fa-list text-xs"></i>
                </button>
            </div>

            <div class="divider divider-horizontal mx-1"></div>
            
            <select wire:model.live="statusFilter" class="select select-bordered select-sm">
                <option value="">All Status</option>
                @foreach($statuses as $key => $status)
                    <option value="{{ $key }}">{{ $status }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="perPage" class="select select-bordered select-sm">
                <option value="12">12 per page</option>
                <option value="24">24 per page</option>
                <option value="48">48 per page</option>
            </select>
        </div>
    </div>

    {{-- Files Content --}}
    <div class="flex-1 min-h-0 overflow-hidden">
        @if($files->count() > 0)
            @if($viewMode === 'grid')
                {{-- Grid View --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 h-full overflow-y-auto">
                    @foreach($files as $file)
                        <div class="card bg-base-100 border hover:shadow-lg transition-all duration-200 hover:-translate-y-1 cursor-pointer"
                             wire:click="openFileDetails({{ $file->id }})">
                            <div class="card-body p-4">
                                {{-- File Icon and Name --}}
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <i class="fa-solid {{ $this->getFileIcon($file->submissionFile->file_name ?? '') }} text-2xl text-primary"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-medium text-sm truncate" title="{{ $file->submissionFile->file_name ?? 'Unknown File' }}">
                                            {{ $file->submissionFile->file_name ?? 'Unknown File' }}
                                        </h3>
                                    </div>
                                </div>

                                {{-- File Details --}}
                                <div class="space-y-2 mt-3">
                                    {{-- Status Badge --}}
                                    <div class="flex items-center justify-between">
                                        <span class="badge {{ $file->status_badge }} badge-sm">
                                            {{ $file->status_text }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $this->formatFileSize($file->submissionFile->size ?? 0) }}
                                        </span>
                                    </div>

                                    {{-- Upload Date --}}
                                    <div class="text-xs text-gray-500">
                                        <i class="fa-regular fa-clock mr-1"></i>
                                        {{ $file->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- List View --}}
                <div class="h-full overflow-y-auto">
                    <div class="space-y-2">
                        @foreach($files as $file)
                            <div class="card bg-base-100 border hover:shadow-md transition-shadow duration-200 cursor-pointer"
                                 wire:click="openFileDetails({{ $file->id }})">
                                <div class="card-body p-4">
                                    <div class="flex items-center gap-4">
                                        {{-- File Icon --}}
                                        <div class="flex-shrink-0">
                                            <i class="fa-solid {{ $this->getFileIcon($file->submissionFile->file_name ?? '') }} text-2xl text-primary"></i>
                                        </div>

                                        {{-- File Info --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="font-medium text-base truncate" title="{{ $file->submissionFile->file_name ?? 'Unknown File' }}">
                                                        {{ $file->submissionFile->file_name ?? 'Unknown File' }}
                                                    </h3>
                                                    <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                                        <span>
                                                            <i class="fa-regular fa-clock mr-1"></i>
                                                            {{ $file->created_at->format('M d, Y \a\t H:i') }}
                                                        </span>
                                                        <span>
                                                            <i class="fa-solid fa-database mr-1"></i>
                                                            {{ $this->formatFileSize($file->submissionFile->size ?? 0) }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Status --}}
                                                <div class="flex items-center gap-3">
                                                    <span class="badge {{ $file->status_badge }} badge-sm whitespace-nowrap">
                                                        {{ $file->status_text }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Pagination --}}
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Showing {{ $files->firstItem() ?? 0 }} to {{ $files->lastItem() ?? 0 }} of {{ $files->total() }} files
                </div>
                {{ $files->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col gap-4 text-gray-500 items-center justify-center h-full min-h-[400px]">
                <div class="flex items-center gap-4 text-6xl">
                    <i class="fa-solid fa-folder-open"></i>
                    <div class="divider divider-horizontal"></div>
                    <i class="fa-solid fa-file"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-xl font-bold mb-2">No files found</h3>
                    @if(!empty($search) || !empty($statusFilter))
                        <p class="text-sm">Try adjusting your search or filters</p>
                        <button 
                            wire:click="clearFilters"
                            class="btn btn-sm btn-ghost mt-2"
                        >
                            <i class="fa-solid fa-filter-circle-xmark mr-2"></i>
                            Clear filters
                        </button>
                    @else
                        <p class="text-sm">You haven't submitted any files yet</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error">
            <i class="fa-solid fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>