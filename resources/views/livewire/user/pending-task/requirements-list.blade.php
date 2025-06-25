<div class="flex flex-col gap-4 w-full bg-white rounded-lg p-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-lg font-bold uppercase">Your Pending Items</h2>
        <span class="badge badge-neutral">{{ $requirements->total() }} items</span>
    </div>

    <!-- Search and Sort -->
    <div class="flex flex-col md:flex-row gap-4 w-full">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search"
            placeholder="Search requirements..." 
            class="input input-bordered w-full md:w-96"
        >
        
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

    <!-- Requirements List -->
    <div class="flex flex-col gap-3">
        @forelse($requirements as $requirement)
            <div 
                wire:click="selectRequirement({{ $requirement->id }})"
                class="flex flex-col p-4 border rounded-lg hover:bg-gray-50 cursor-pointer"
            >
                <div class="flex justify-between">
                    <div class="flex flex-col">
                        <h3 class="font-semibold">{{ $requirement->name }}</h3>
                        <p class="text-sm text-gray-500 line-clamp-2">{{ $requirement->description }}</p>
                    </div>
                    <span class="badge {{ 
                        $requirement->priority === 'high' ? 'badge-error' : 
                        ($requirement->priority === 'medium' ? 'badge-warning' : 'badge-info') 
                    }}">
                        {{ ucfirst($requirement->priority) }}
                    </span>
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
                <p>No pending requirements found</p>
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

    <!-- Requirement Detail Modal -->
    @if($selectedRequirement)
        <div class="modal modal-open">
            <div class="modal-box max-w-4xl">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg">{{ $selectedRequirement->name }}</h3>
                        <p class="text-gray-500">{{ $selectedRequirement->description }}</p>
                    </div>
                    <button wire:click="closeDetail" class="btn btn-sm btn-circle">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Details -->
                    <div class="flex flex-col gap-4">
                        <h4 class="font-semibold">Details</h4>
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Priority:</span>
                            <span class="{{ 
                                $selectedRequirement->priority === 'high' ? 'text-error' : 
                                ($selectedRequirement->priority === 'medium' ? 'text-warning' : 'text-info') 
                            }}">
                                {{ ucfirst($selectedRequirement->priority) }}
                            </span>
                        </div>
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Due Date:</span>
                            <span>{{ $selectedRequirement->due->format('M j, Y') }} ({{ $selectedRequirement->due->diffForHumans() }})</span>
                        </div>
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Created:</span>
                            <span>{{ $selectedRequirement->created_at->format('M j, Y') }}</span>
                        </div>
                        
                        <!-- Guide Files -->
                        @if($selectedRequirement->guides->count() > 0)
                            <div class="mt-4">
                                <h4 class="font-semibold mb-2">Guide Files</h4>
                                @foreach($selectedRequirement->guides as $guide)
                                    <a href="{{ $guide->getUrl() }}" target="_blank" class="flex items-center gap-2 text-blue-500 hover:text-blue-700">
                                        <i class="fa-regular fa-file"></i>
                                        <span>{{ $guide->file_name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Submission Form -->
                    <div class="flex flex-col gap-4">
                        <h4 class="font-semibold">Submit Requirement</h4>
                        <form wire:submit.prevent="submitRequirement" class="flex flex-col gap-4">
                            <div>
                                <input 
                                    type="file" 
                                    wire:model="file" 
                                    class="file-input file-input-bordered w-full"
                                    wire:loading.attr="disabled"
                                >
                                @error('file')
                                    <span class="text-error text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <textarea 
                                wire:model="submissionNotes"
                                placeholder="Add any notes for the admin..."
                                class="textarea textarea-bordered w-full"
                            ></textarea>
                            
                            <button 
                                type="submit" 
                                class="btn btn-primary"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove>Submit Requirement</span>
                                <span wire:loading>
                                    <i class="fa-solid fa-spinner animate-spin"></i> Uploading...
                                </span>
                            </button>
                        </form>

                        <!-- Previous Submissions -->
                        @if($selectedRequirement->userSubmissions->count() > 0)
                            <div class="mt-4">
                                <h5 class="font-medium">Your Previous Submissions</h5>
                                <div class="flex flex-col divide-y mt-2">
                                    @foreach($selectedRequirement->userSubmissions as $submission)
                                        <div class="flex justify-between items-center py-2">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-regular fa-file"></i>
                                                <span>
                                                    @if($submission->media->first())
                                                        {{ $submission->media->first()->file_name }}
                                                    @else
                                                        File missing
                                                    @endif
                                                </span>
                                                <span class="badge {{ $submission->status_badge }}">
                                                    {{ SubmittedRequirement::statuses()[$submission->status] }}
                                                </span>
                                            </div>
                                            <div class="flex gap-2">
                                                @if($submission->media->first())
                                                    <a href="{{ $submission->media->first()->getUrl() }}" target="_blank" class="btn btn-xs btn-ghost">
                                                        <i class="fa-solid fa-eye"></i> View
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="modal-action">
                    <button wire:click="closeDetail" class="btn">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>