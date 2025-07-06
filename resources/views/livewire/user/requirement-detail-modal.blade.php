<div>
    @if($requirement)
        <div class="modal modal-open">
            <div class="modal-box max-w-4xl">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg">{{ $requirement->name }}</h3>
                        <p class="text-gray-500">{{ $requirement->description }}</p>
                    </div>
                    <button wire:click="closeModal" class="btn btn-sm btn-circle">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Details -->
                    <div class="flex flex-col gap-4">
                        <h4 class="font-semibold">Details</h4>
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Priority:</span>
                            <span style="color: {{ \App\Models\SubmittedRequirement::getPriorityColor($requirement->priority) }}">
                                {{ ucfirst($requirement->priority) }}
                            </span>
                        </div>
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Due Date:</span>
                            <span>{{ $requirement->due->format('M j, Y') }} ({{ $requirement->due->diffForHumans() }})</span>
                        </div>
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Status:</span>
                            <span class="badge" style="background-color: {{ $requirement->userSubmissions->first() ? \App\Models\SubmittedRequirement::getStatusColor($requirement->userSubmissions->first()->status) : \App\Models\SubmittedRequirement::getStatusColor('default') }}; color: white">
                                {{ $requirement->userSubmissions->first()?->status_text ?? 'Not Submitted' }}
                            </span>
                        </div>
                        
                        <!-- Guide Files -->
                        @if($requirement->guides->count() > 0)
                            <div class="mt-4">
                                <h4 class="font-semibold mb-2">Guide Files</h4>
                                @foreach($requirement->guides as $guide)
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
                        @if($requirement->userSubmissions->count() > 0)
                            <div class="mt-4">
                                <h5 class="font-medium">Your Previous Submissions</h5>
                                <div class="flex flex-col divide-y mt-2">
                                    @foreach($requirement->userSubmissions as $submission)
                                        <div class="flex justify-between items-center py-2">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-regular fa-file"></i>
                                                <span>
                                                    @if($submission->submissionFile)
                                                        {{ $submission->submissionFile->file_name }}
                                                    @else
                                                        File missing
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex gap-2">
                                                <span class="badge {{ $submission->status_badge }}">
                                                    {{ $submission->status_text }}
                                                </span>
                                                @if($submission->submissionFile)
                                                    <a href="{{ $submission->submissionFile->getUrl() }}" target="_blank" class="btn btn-xs btn-ghost">
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
                    <button wire:click="closeModal" class="btn">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>