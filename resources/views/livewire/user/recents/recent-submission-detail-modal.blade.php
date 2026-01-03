{{-- Recent Submission Detail Modal --}}
<div>
    @if($isOpen && $submission && $requirement)
        {{-- Modal backdrop --}}
        <div class="fixed inset-0 modal modal-open flex items-center justify-center p-4 z-40" wire:click="closeModal">
            
            {{-- Modal panel --}}
            <div 
                class="bg-white rounded-xl shadow-2xl transform transition-all max-w-4xl w-full mx-auto overflow-hidden"
                wire:click.stop
            >
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                    <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-white text-2xl"></i>
                        Submission Details
                        @if(count($correctionNotes) > 0)
                            <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full ml-2">
                                {{ count($correctionNotes) }} feedback
                            </span>
                        @endif
                    </h2>
                    <button 
                        wire:click="closeModal"
                        class="h-8 w-8 rounded-full hover:bg-white/20 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Close modal"
                    >
                        <i class="fa-solid fa-times text-base text-white"></i>
                    </button>
                </div>

                {{-- Content Body --}}
                <div class="max-h-[70vh] overflow-y-auto bg-gray-50">
                    {{-- Main Content Grid --}}
                    <div class="p-6 space-y-6">
                        {{-- Requirement Summary Section --}}
                        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $requirement->name }}</h3>
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span class="flex items-center gap-2">
                                            <i class="fa-solid fa-calendar-alt text-green-600"></i>
                                            <span>{{ $requirement->semester->name ?? 'N/A' }}</span>
                                        </span>
                                        @if($requirement->is_required)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-800">
                                                Required
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium {{ $submission->status_badge }}">
                                    {{ $submission->status_text }}
                                </span>
                            </div>

                            @if($requirement->description)
                                <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <p class="text-sm text-gray-700 leading-relaxed">{{ $requirement->description }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- Two Column Layout --}}
                        <div class="grid md:grid-cols-2 gap-6">
                            {{-- Submitted File Section --}}
                            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                                <h4 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-file-arrow-up text-blue-600"></i>
                                    Submitted File
                                </h4>
                                @if($submission->submissionFile)
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 flex items-center">
                                        <div class="flex items-center gap-4 flex-1 min-w-0">
                                            <div class="h-12 w-12 rounded-lg bg-white border border-gray-200 flex items-center justify-center shrink-0 shadow-sm">
                                                <i class="fa-solid {{ $submission->getFileIcon() }} {{ $submission->getFileIconColor() }} text-xl"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $submission->submissionFile->file_name }}</p>
                                                <p class="text-xs text-gray-500 mt-1">{{ number_format($submission->submissionFile->size / 1024, 1) }} KB</p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-6 border-2 border-dashed border-gray-300 rounded-lg">
                                        <i class="fa-solid fa-file-circle-question text-gray-300 text-4xl mb-3"></i>
                                        <p class="text-sm text-gray-400 font-medium">No file attached</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Submission Details Section --}}
                            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                                <h4 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-info-circle text-green-600"></i>
                                    Submission Information
                                </h4>
                                <div class="space-y-3">
                                    @if($submission->course)
                                        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                            <span class="font-medium text-gray-600 flex items-center gap-2">
                                                <i class="fa-solid fa-book text-gray-400"></i>
                                                Course
                                            </span>
                                            <div class="text-right">
                                                <span class="block text-sm font-semibold text-gray-800">{{ $submission->course->course_code }}</span>
                                                <span class="block text-xs text-gray-500">{{ $submission->course->course_name }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                        <span class="font-medium text-gray-600 flex items-center gap-2">
                                            <i class="fa-regular fa-calendar text-gray-400"></i>
                                            Submitted
                                        </span>
                                        <span class="text-sm font-semibold text-gray-800">{{ $submission->submitted_at->format('M j, Y g:i A') }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                        <span class="font-medium text-gray-600">Time Ago</span>
                                        <span class="text-sm text-gray-500">{{ $submission->submitted_at->diffForHumans() }}</span>
                                    </div>
                                    
                                    @if($submission->reviewed_at)
                                        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                            <span class="font-medium text-gray-600 flex items-center gap-2">
                                                <i class="fa-solid fa-check-circle text-gray-400"></i>
                                                Reviewed
                                            </span>
                                            <span class="text-sm font-semibold text-gray-800">{{ $submission->reviewed_at->format('M j, Y g:i A') }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($submission->reviewer)
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-gray-600 flex items-center gap-2">
                                                <i class="fa-solid fa-user-check text-gray-400"></i>
                                                Reviewer
                                            </span>
                                            <span class="text-sm font-semibold text-gray-800">{{ $submission->reviewer->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Latest Admin Notes --}}
                        @if($submission->admin_notes)
                            <div class="bg-white rounded-xl p-5 border border-yellow-200 shadow-sm border-l-4 border-l-yellow-500">
                                <h4 class="text-base font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-note-sticky text-yellow-600"></i>
                                    Latest Admin Notes
                                </h4>
                                <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <p class="text-sm text-gray-700 leading-relaxed">{{ $submission->admin_notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Correction Notes Section - Full Width --}}
                    @if(count($correctionNotes) > 0)
                        <div class="border-t border-gray-200 bg-white">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-3">
                                    <i class="fa-solid fa-message-pen text-blue-600"></i>
                                    Feedback & Correction History
                                    <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
                                        {{ count($correctionNotes) }} entries
                                    </span>
                                </h4>
                                
                                {{-- Timeline-style correction notes --}}
                                <div class="space-y-4">
                                    @foreach($correctionNotes as $index => $note)
                                        <div class="border border-gray-200 rounded-xl overflow-hidden transition-all duration-200 hover:shadow-md">
                                            {{-- Note Header --}}
                                            <div class="bg-gradient-to-r from-gray-50 to-white px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                                                <div class="flex items-center space-x-4">
                                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-semibold text-sm">
                                                        {{ count($correctionNotes) - $index }}
                                                    </div>
                                                    <div>
                                                        <span class="text-sm font-semibold text-gray-800">
                                                            Feedback from {{ $note['admin']['name'] ?? 'Admin' }}
                                                        </span>
                                                        <span class="block text-xs text-gray-500 mt-1">
                                                            {{ \Carbon\Carbon::parse($note['created_at'])->format('M j, Y g:i A') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $note['status_badge'] }}">
                                                    {{ $note['status_label'] }}
                                                </span>
                                            </div>
                                            
                                            {{-- Note Content --}}
                                            <div class="p-5 bg-white">
                                                <div class="space-y-4">
                                                    {{-- File Information --}}
                                                    @if($note['file_name'])
                                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                            <div class="flex items-center space-x-3">
                                                                <i class="fa-regular fa-file text-gray-500"></i>
                                                                <div>
                                                                    <span class="text-sm font-semibold text-gray-700">Original File</span>
                                                                    <p class="text-sm text-gray-600">{{ $note['file_name'] }}</p>
                                                                </div>
                                                            </div>
                                                            @if($note['has_file_been_replaced'])
                                                                <div class="flex items-center space-x-2 text-yellow-600">
                                                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                                                    <span class="text-sm font-semibold">Updated</span>
                                                                    <span class="text-xs bg-yellow-100 px-2 py-1 rounded">{{ $note['current_file_name'] }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    
                                                    {{-- Correction Notes --}}
                                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                                        <p class="text-sm text-gray-700 leading-relaxed">{{ $note['correction_notes'] }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                    {{-- Left side: Folder button --}}
                    <div>
                        @if($this->getRequirementFolderUrl())
                            <a 
                                href="{{ $this->getRequirementFolderUrl() }}"
                                class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                            >
                                Open Requirement Folder
                                <i class="fa-solid fa-arrow-up-right-from-square ml-2"></i>
                            </a>
                        @endif
                    </div>

                    {{-- Right side: File actions --}}
                    <div class="flex gap-3">
                        @if($submission->submissionFile)
                            {{-- Download Button --}}
                            <button 
                                wire:click="downloadFile"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                            >
                                <i class="fa-solid fa-download mr-2"></i>
                                Download
                            </button>

                            {{-- Preview Button (opens in new tab) --}}
                            @if($this->isPreviewable)
                                <a 
                                    href="{{ $this->getPreviewUrl() }}"
                                    target="_blank"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                >
                                    <i class="fa-solid fa-eye mr-2"></i>
                                    Preview
                                </a>
                            @endif 
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Display flash messages --}}
    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg z-50 shadow-lg" role="alert">
            <div class="flex items-center">
                <i class="fa-solid fa-exclamation-circle mr-2"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif
</div>  