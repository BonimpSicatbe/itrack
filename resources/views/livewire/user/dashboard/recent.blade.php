<div class="flex flex-col p-4 overflow-hidden bg-white rounded-lg">
    {{-- heading --}}
    <div class="flex flex-row items-center justify-between w-full">
        <div class="text-lg uppercase font-bold">Recent Submissions</div>
    </div>
    
    {{-- list --}}
    @if($recentSubmissions->count() > 0)
        <div class="flex flex-row gap-4 overflow-x-auto w-full py-2">
            @foreach($recentSubmissions as $submission)
                <div 
                    wire:click="selectSubmission({{ $submission->id }})"
                    class="border rounded-lg p-3 min-w-[300px] hover:bg-gray-50 transition-all flex flex-col gap-1 cursor-pointer"
                >
                    <div class="flex justify-between items-start">
                        <div class="text-sm font-bold truncate">{{ $submission->requirement->name }}</div>
                        <span class="badge px-2 py-1 text-xs rounded" 
                              style="background-color: {{ \App\Models\SubmittedRequirement::getStatusColor($submission->status) }}; color: white">
                            {{ $submission->status_text }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-500">
                        Submitted: {{ $submission->submitted_at->format('M j, Y') }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        @if($submission->submissionFile)
                            <i class="fas fa-file mr-1"></i> {{ $submission->submissionFile->file_name }}
                        @else
                            <i class="fas fa-exclamation-circle mr-1"></i> No file attached
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-6 text-gray-500">
            <i class="fa-regular fa-folder-open text-3xl mb-2"></i>
            <p class="text-sm">No recent submissions found</p>
        </div>
    @endif

    <!-- Submission Detail Modal -->
    @if($selectedSubmission)
        <div class="modal modal-open">
            <div class="modal-box max-w-4xl">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg">{{ $selectedSubmission->requirement->name }}</h3>
                        <p class="text-gray-500">{{ $selectedSubmission->requirement->description }}</p>
                    </div>
                    <button wire:click="closeModal" class="btn btn-sm btn-circle">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Details -->
                    <div class="flex flex-col gap-4">
                        <h4 class="font-semibold">Submission Details</h4>
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Status:</span>
                            <span class="badge" style="background-color: {{ \App\Models\SubmittedRequirement::getStatusColor($selectedSubmission->status) }}; color: white">
                                {{ $selectedSubmission->status_text }}
                            </span>
                        </div>
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Submitted:</span>
                            <span>{{ $selectedSubmission->submitted_at->format('M j, Y h:i A') }}</span>
                        </div>
                        @if($selectedSubmission->reviewed_at)
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Reviewed:</span>
                            <span>{{ $selectedSubmission->reviewed_at->format('M j, Y h:i A') }}</span>
                        </div>
                        @endif
                        @if($selectedSubmission->reviewer)
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Reviewed by:</span>
                            <span>{{ $selectedSubmission->reviewer->name }}</span>
                        </div>
                        @endif
                        @if($selectedSubmission->admin_notes)
                        <div class="flex gap-4">
                            <span class="text-gray-500 w-24">Admin Notes:</span>
                            <span class="whitespace-pre-wrap">{{ $selectedSubmission->admin_notes }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- File Preview -->
                    <div class="flex flex-col gap-4">
                        <div class="flex justify-between items-center">
                            <h4 class="font-semibold">Submitted File</h4>
                            @if($selectedSubmission->submissionFile)
                                <button wire:click="togglePreview" class="btn btn-sm">
                                    @if($showPreview)
                                        <i class="fas fa-times mr-1"></i> Hide Preview
                                    @else
                                        <i class="fas fa-eye mr-1"></i> Preview
                                    @endif
                                </button>
                            @endif
                        </div>
                        
                        @if($selectedSubmission->submissionFile)
                            @php
                                $fileUrl = $this->getFileUrl($selectedSubmission);
                                $extension = strtolower($selectedSubmission->submissionFile->extension);
                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                $isPdf = $extension === 'pdf';
                            @endphp

                            @if($showPreview && ($isImage || $isPdf))
                                <div class="border rounded-lg p-4 bg-gray-50 mb-4">
                                    @if($isImage)
                                        <!-- Image Preview -->
                                        <img src="{{ $fileUrl }}" 
                                            alt="File preview" 
                                            class="max-w-full max-h-64 mx-auto">
                                    @elseif($isPdf)
                                        <!-- PDF Preview -->
                                        <iframe src="{{ route('file.preview', $selectedSubmission->id) }}" 
                                                class="w-full h-64 border rounded">
                                            Your browser does not support PDF preview.
                                        </iframe>
                                    @endif
                                </div>
                            @endif

                            <div class="flex flex-col items-center border rounded-lg p-4">
                                <i class="fa-regular fa-file text-5xl text-gray-400 mb-2"></i>
                                <span class="text-sm font-medium">{{ $selectedSubmission->submissionFile->file_name }}</span>
                                <span class="text-xs text-gray-500 mt-1">
                                    {{ number_format($selectedSubmission->submissionFile->size / 1024, 2) }} KB
                                </span>
                                <a href="{{ route('file.download', $selectedSubmission->id) }}" 
                                   class="btn btn-sm btn-primary mt-4">
                                    <i class="fa-solid fa-download mr-1"></i> Download
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fa-solid fa-exclamation-triangle mr-2"></i>
                                No file attached to this submission
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