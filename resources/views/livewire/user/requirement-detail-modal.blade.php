<div>
    @if($requirement)
        <div class="modal modal-open">
            <div class="modal-box max-w-5xl"> <!-- Increased max-width for better spacing -->
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg">{{ $requirement->name }}</h3>
                        <p class="text-gray-500">{{ $requirement->description }}</p>
                    </div>
                    <button wire:click="closeModal" class="btn btn-sm btn-circle">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Section 1 & 2 - Side by Side -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Section 1: Requirement Details -->
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h4 class="font-semibold mb-4">Requirement Details</h4>
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
                                            <a href="{{ $guide->getUrl() }}" target="_blank" class="flex items-center gap-2 text-blue-500 hover:text-blue-700">
                                                <i class="fa-regular fa-file"></i>
                                                <span>{{ $guide->file_name }}</span>
                                            </a>
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
                            
                            <textarea 
                                wire:model="submissionNotes"
                                placeholder="Add any notes for the admin..."
                                class="textarea textarea-bordered w-full"
                                rows="3"
                            ></textarea>
                            
                            <button 
                                type="submit" 
                                class="btn btn-primary w-full"
                                wire:loading.attr="disabled"
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
                        <div class="overflow-x-auto">
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
                                            <td>
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
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: {{ \App\Models\SubmittedRequirement::getStatusColor($submission->status) }}; color: white">
                                                    {{ $submission->status_text }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $submission->submitted_at->format('M j, Y h:i A') }}
                                            </td>
                                            <td>
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
                                                                <i class="fa-solid fa-eye"></i> Preview
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('file.download', $submission->id) }}" 
                                                           class="btn btn-xs btn-ghost">
                                                            <i class="fa-solid fa-download"></i> Download
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