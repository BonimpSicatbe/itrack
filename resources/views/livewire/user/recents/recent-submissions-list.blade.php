<div class="flex flex-col gap-4">
    {{-- Recent Submissions Header --}}

    {{-- Main Recent Submissions Section --}}
    <div class="flex flex-col p-4 overflow-hidden bg-white rounded-lg">
        @if($recentSubmissions->count() > 0)
            <div class="flex flex-col gap-2 w-full py-2">
                @foreach($recentSubmissions as $submission)
                    <div wire:click="showRequirementDetail({{ $submission->id }})"
                        class="border rounded-lg p-3 w-full hover:bg-gray-50 transition-all flex flex-col gap-1 cursor-pointer">
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
    </div>

    <!-- Include the Requirement Detail Modal component -->
    @livewire('user.requirement-detail-modal')
</div>