{{-- recent.blade.php --}}
<div class="bg-white rounded-xl px-6 py-4">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-xl font-semibold text-gray-800">Recent Submissions</h3>
        <a href="{{ route('user.recents') }}" class="text-sm text-green-700 hover:text-green-500 font-semibold hover:underline">
            see more <i class="fa-solid fa-chevron-right text-xs"></i>
        </a>
    </div>

    {{-- List --}}
    @if($recentSubmissions->count() > 0)
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($recentSubmissions as $submission)
                <div wire:click="showRequirementDetail({{ $submission->id }})"
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-xl cursor-pointer transition-colors hover:bg-green-50">
                    
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        {{-- File Icon --}}
                        @if($submission->submissionFile)
                            @php
                                $fileIcon = $submission->getFileIcon();
                                $fileColor = $submission->getFileIconColor();
                            @endphp
                            <i class="fas {{ $fileIcon }} text-lg {{ $fileColor }}"></i>
                        @else
                            <i class="fas fa-file-times text-gray-400 text-lg"></i>
                        @endif
                        
                        {{-- File Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">
                                @if($submission->submissionFile)
                                    {{ $submission->submissionFile->file_name }}
                                @else
                                    No file uploaded
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $submission->submitted_at->format('M j, Y') }}
                                • {{ $submission->requirement?->name ?? 'Deleted Requirement' }}
                            </p>
                        </div>
                    </div>

                    {{-- Status --}}
                    @php
                        $statusBadgeClass = $submission->status_badge;
                    @endphp
                    <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap {{ $statusBadgeClass }}">
                        {{ $submission->status_text }}
                    </span>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-300">
            <i class="fas fa-folder-open text-3xl mb-2"></i>
            <p class="text-gray-500 text-sm font-semibold">No recent submissions</p>
        </div>
    @endif

    <!-- Replace with the Recent Submission Detail Modal component -->
    @livewire('user.recents.recent-submission-detail-modal')
</div>