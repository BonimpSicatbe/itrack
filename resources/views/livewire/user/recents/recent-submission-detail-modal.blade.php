{{-- Recent Submission Detail Modal --}}
<div>
    @if($isOpen && $submission && $requirement)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Background overlay - Reduced opacity to show background better --}}
            <div class="fixed inset-0 bg-white bg-opacity-20 transition-opacity backdrop-blur-md" wire:click="closeModal"></div>

            {{-- Modal panel --}}
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="relative inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl z-10">
                    {{-- Header --}}
                    <div class="relative px-8 py-6" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                                    <i class="fa-solid fa-file-lines text-white text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-white">Submission Details</h2>
                                    <p class="text-white/80 text-sm">View your submission information</p>
                                </div>
                            </div>
                            <button 
                                wire:click="closeModal"
                                class="h-10 w-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 backdrop-blur-sm"
                            >
                                <i class="fa-solid fa-times text-white text-lg"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="max-h-[70vh] overflow-y-auto">
                        <div class="px-8 py-6 space-y-6">
                            {{-- Requirement Information --}}
                            <div class="bg-gradient-to-r from-[#DEF4C6]/20 to-[#B1CF5F]/10 rounded-2xl border border-[#73E2A7]/30 p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-[#1B512D] mb-2">{{ $requirement->name }}</h3>
                                        <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-calendar-alt text-[#1C7C54]"></i>
                                                <span>{{ $requirement->semester->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $submission->status_badge }}">
                                            {{ $submission->status_text }}
                                        </span>
                                        @if($requirement->is_required)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-800">
                                                Required
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($requirement->description)
                                    <div class="bg-white/50 rounded-xl p-4 border border-[#73E2A7]/20">
                                        <h4 class="font-semibold text-[#1B512D] mb-2 flex items-center gap-2">
                                            <i class="fa-solid fa-info-circle text-[#1C7C54]"></i>
                                            Description
                                        </h4>
                                        <p class="text-gray-700 text-sm leading-relaxed">{{ $requirement->description }}</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Submission Information --}}
                            <div class="grid md:grid-cols-2 gap-6">
                                {{-- Submission Details --}}
                                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                        <i class="fa-solid fa-upload text-[#1C7C54]"></i>
                                        Submission Information
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">Submitted</span>
                                            <span class="text-sm text-gray-900">{{ $submission->submitted_at->format('M j, Y g:i A') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">Time Ago</span>
                                            <span class="text-sm text-gray-900">{{ $submission->submitted_at->diffForHumans() }}</span>
                                        </div>
                                        @if($submission->reviewed_at)
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                                <span class="text-sm font-medium text-gray-600">Reviewed</span>
                                                <span class="text-sm text-gray-900">{{ $submission->reviewed_at->format('M j, Y g:i A') }}</span>
                                            </div>
                                        @endif
                                        @if($submission->reviewer)
                                            <div class="flex justify-between items-center py-2">
                                                <span class="text-sm font-medium text-gray-600">Reviewer</span>
                                                <span class="text-sm text-gray-900">{{ $submission->reviewer->name }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- File Information --}}
                                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                        <i class="fa-solid fa-file text-[#1C7C54]"></i>
                                        Submitted File
                                    </h4>
                                    @if($submission->submissionFile)
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between p-3 bg-[#DEF4C6]/20 rounded-xl border border-[#73E2A7]/30">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-10 w-10 rounded-lg bg-[#1C7C54]/10 flex items-center justify-center">
                                                        <i class="fa-solid {{ $submission->getFileIcon() }} {{ $submission->getFileIconColor() }} text-lg"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $submission->submissionFile->file_name }}</p>
                                                        <p class="text-xs text-gray-500">{{ number_format($submission->submissionFile->size / 1024, 1) }} KB</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-8">
                                            <div class="h-16 w-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                                <i class="fa-solid fa-file-circle-question text-gray-400 text-2xl"></i>
                                            </div>
                                            <p class="text-gray-500 text-sm">No file attached</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Comments/Notes Section --}}
                            @if($submission->admin_notes)
                                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                        <i class="fa-solid fa-comment-dots text-[#1C7C54]"></i>
                                        Admin Notes
                                    </h4>
                                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                        <p class="text-sm text-gray-700 leading-relaxed">{{ $submission->admin_notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 border-t border-gray-200 px-8 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if($submission->submissionFile)
                                    <button 
                                        wire:click="downloadFile"
                                        class="inline-flex items-center px-4 py-2 bg-[#1C7C54] text-white text-sm font-medium rounded-lg hover:bg-[#1B512D] transition-colors duration-200"
                                    >
                                        <i class="fa-solid fa-download text-xs mr-2"></i>
                                        Download File
                                    </button>
                                @endif
                                <button 
                                    wire:click="closeModal"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200"
                                >
                                    <i class="fa-solid fa-times text-xs mr-2"></i>
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Display flash messages --}}
    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
</div>