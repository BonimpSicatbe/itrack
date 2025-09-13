<div>
    @livewire('notification-toast')
    @if($requirement)
        <!-- Backdrop that covers the entire screen -->
        <div class="modal modal-open" wire:click="closeModal">
            <div class="modal-box max-w-5xl max-h-[90vh]" wire:click.stop>
                <!-- Section 1 & 2 - Side by Side -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Section 1: Requirement Details -->
                    <div class="border rounded-lg p-4 bg-gray-50">
                    <h3 class="font-bold text-lg">Requirement Details</h3>
                        <h5 class="mb-2">{{ $requirement->name }}</h5>
                        <p class="text-gray-600 mb-4">{{ $requirement->description }}</p>
                        
                        <div class="space-y-3">
                            <div class="flex gap-4">
                                <span class="text-gray-500 w-32">Priority:</span>
                                <span style="color: {{ \App\Models\SubmittedRequirement::getPriorityColor($requirement->priority) }}">
                                    {{ ucfirst($requirement->priority) }}
                                </span>
                            </div>
                            <div class="flex gap-4">
                                <span class="text-gray-500 w-32">Due Date:</span>
                                <span>{{ $requirement->due->format('M j, Y') }} ({{ $requirement->due->diffForHumans() }})</span>
                            </div>
                            
                            <!-- Guide Files -->
                            @if($requirement->guides->count() > 0)
                                <div class="mt-4 pt-4 border-t">
                                    <h4 class="font-semibold mb-2">Guide Files</h4>
                                    <div class="space-y-2">
                                        @foreach($requirement->guides as $guide)
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-regular fa-file"></i>
                                                    <span class="truncate max-w-xs">{{ $guide->file_name }}</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <a href="{{ route('guide.download', ['media' => $guide->id]) }}" 
                                                    class="text-blue-500 hover:text-blue-700 inline-flex items-center" 
                                                    title="Download">
                                                        <i class="fa-solid fa-download text-sm"></i>
                                                    </a>
                                                    @if($this->isPreviewable($guide->mime_type))
                                                    <a href="{{ route('guide.preview', ['media' => $guide->id]) }}" 
                                                    target="_blank"
                                                    class="text-green-500 hover:text-green-700 inline-flex items-center" 
                                                    title="View">
                                                        <i class="fa-solid fa-eye text-sm"></i>
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

                    <!-- Section 2: Submit Requirement -->
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h4 class="font-semibold mb-4">Submit Requirement</h4>
                        <form wire:submit.prevent="submitRequirement" class="space-y-4">
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
                            
                            <!-- Display selected file name -->
                            @if($file)
                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-regular fa-file text-blue-500"></i>
                                            <span class="text-sm font-medium truncate max-w-xs">
                                                {{ $file->getClientOriginalName() }}
                                            </span>
                                        </div>
                                        <button 
                                            type="button" 
                                            class="btn btn-xs btn-ghost text-error"
                                            wire:click="$set('file', null)"
                                            title="Remove file"
                                        >
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Size: {{ round($file->getSize() / 1024, 1) }} KB
                                    </div>
                                </div>
                            @endif
                            
                            <button 
                                type="submit" 
                                class="btn btn-primary w-full"
                                wire:loading.attr="disabled"
                                :disabled="!$file"
                            >
                                <span wire:loading.remove>Submit Requirement</span>
                                <span wire:loading>
                                    <i class="fa-solid fa-spinner animate-spin"></i> Uploading...
                                </span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Section 3: Previous Submissions (Full Width) -->
                <div class="mt-6 border rounded-lg p-4 bg-gray-50">
                    <h4 class="font-semibold mb-4">Your Previous Submissions</h4>
                    
                    @if($requirement->userSubmissions->count() > 0)
                        <div class="overflow-x-auto max-h-96 overflow-y-auto"> <!-- Added max-h-96 and overflow-y-auto for scrollable content -->
                            <table class="table w-full">
                                <thead>
                                    <tr>
                                        <th>File</th>
                                        <th>Status</th>
                                        <th>Submitted At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requirement->userSubmissions as $submission)
                                        <tr>
                                            <td class="max-w-xs truncate">
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-regular fa-file"></i>
                                                    <span class="truncate">
                                                        @if($submission->submissionFile)
                                                            {{ $submission->submissionFile->file_name }}
                                                        @else
                                                            File missing
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $submission->status_badge }}">
                                            </td>
                                            <td class="whitespace-nowrap">
                                                {{ $submission->submitted_at->format('M j, Y h:i A') }}
                                            </td>
                                            <td class="whitespace-nowrap">
                                                <div class="flex gap-2">
                                                    @if($submission->submissionFile)
                                                        @php
                                                            $extension = strtolower(pathinfo($submission->submissionFile->file_name, PATHINFO_EXTENSION));
                                                            $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
                                                        @endphp
                                                        
                                                        @if($isPreviewable)
                                                            <a href="{{ route('file.preview', $submission->id) }}" 
                                                               target="_blank" 
                                                               class="btn btn-xs btn-ghost">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('file.download', $submission->id) }}" 
                                                           class="btn btn-xs btn-ghost">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                        @if($submission->canBeDeletedBy(auth()->user()))
                                                            @if($confirmingDeletion == $submission->id)
                                                                <div class="flex gap-1">
                                                                    <button wire:click="deleteSubmission({{ $submission->id }})" 
                                                                            class="btn btn-xs btn-error">
                                                                        Confirm
                                                                    </button>
                                                                    <button wire:click="cancelDelete" 
                                                                            class="btn btn-xs btn-ghost">
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            @else
                                                                <button wire:click="confirmDelete({{ $submission->id }})" 
                                                                        class="btn btn-xs btn-ghost text-error">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-500">
                            <i class="fa-regular fa-folder-open text-2xl mb-2"></i>
                            <p>No previous submissions found</p>
                        </div>
                    @endif
                </div>

                <div class="modal-action">
                    <button wire:click="closeModal" class="btn">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>