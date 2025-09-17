{{-- Recent Submission Detail Modal --}}
<div>
    @if($isOpen && $submission && $requirement)
        {{-- Modal backdrop --}}
        <div class="fixed inset-0 modal modal-open flex items-center justify-center p-4 z-40" wire:click="closeModal">
            
            {{-- Modal panel --}}
            <div 
                class="bg-white rounded-xl shadow-2xl transform transition-all max-w-3xl w-full mx-auto overflow-hidden"
                wire:click.stop
            >
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-1">
                        <i class="fa-solid fa-circle-info text-[#1C7C54] text-2xl"></i>
                        Submission Details
                    </h2>
                    <button 
                        wire:click="closeModal"
                        class="h-8 w-8 rounded-full text-gray-400 hover:bg-gray-100 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Close modal"
                    >
                        <i class="fa-solid fa-times text-base"></i>
                    </button>
                </div>

                {{-- Content Body --}}
                <div class="max-h-[70vh] overflow-y-auto bg-gray-50 p-6 space-y-6">
                    
                    {{-- Requirement Summary Section --}}
                    <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">{{ $requirement->name }}</h3>
                                <div class="flex items-center gap-3 text-sm text-gray-500 mt-1">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-calendar-alt"></i>
                                        <span>{{ $requirement->semester->name ?? 'N/A' }}</span>
                                    </span>
                                </div>
                            </div>
                            @if($requirement->is_required)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-800">
                                    Required
                                </span>
                            @endif
                        </div>

                        @if($requirement->description)
                            <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600 leading-relaxed">{{ $requirement->description }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- Submitted File Section --}}
                        <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm space-y-4">
                            <div class="flex justify-between items-center">
                                <h4 class="text-base font-semibold text-gray-700">Submitted File</h4>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $submission->status_badge }}">
                                    {{ $submission->status_text }}
                                </span>
                            </div>
                            @if($submission->submissionFile)
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 flex items-center">
                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        <div class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center shrink-0">
                                            <i class="fa-solid {{ $submission->getFileIcon() }} {{ $submission->getFileIconColor() }} text-lg"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-700 truncate">{{ $submission->submissionFile->file_name }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($submission->submissionFile->size / 1024, 1) }} KB</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fa-solid fa-file-circle-question text-gray-300 text-3xl"></i>
                                    <p class="text-sm text-gray-400 mt-2">No file attached</p>
                                </div>
                            @endif
                        </div>

                        {{-- Submission Details Section --}}
                        <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm space-y-4">
                            <h4 class="text-base font-semibold text-gray-700">Submission Information</h4>
                            <div class="space-y-3 text-sm text-gray-600">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Submitted</span>
                                    <span>{{ $submission->submitted_at->format('M j, Y g:i A') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Time Ago</span>
                                    <span>{{ $submission->submitted_at->diffForHumans() }}</span>
                                </div>
                                @if($submission->reviewed_at)
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium">Reviewed</span>
                                        <span>{{ $submission->reviewed_at->format('M j, Y g:i A') }}</span>
                                    </div>
                                @endif
                                @if($submission->reviewer)
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium">Reviewer</span>
                                        <span>{{ $submission->reviewer->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Admin Notes Section --}}
                    @if($submission->admin_notes)
                        <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm space-y-4">
                            <h4 class="text-base font-semibold text-gray-700">Admin Notes</h4>
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600 leading-relaxed">{{ $submission->admin_notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 border-t border-gray-100 px-6 py-4 flex justify-end">
                    @if($submission->submissionFile)
                        <button 
                            wire:click="downloadFile"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200"
                        >
                            <i class="fa-solid fa-download text-xs mr-2"></i>
                            Download File
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Display flash messages (unchanged) --}}
    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
</div>