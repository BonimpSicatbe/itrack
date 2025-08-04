<div class="flex flex-col gap-4">
    {{-- Recent Submissions Header --}}

    {{-- Filter Controls --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        {{-- Status Filter --}}
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                <i class="fas fa-flag mr-1.5 text-gray-500"></i>Status
            </label>
            <select wire:model.live="statusFilter"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white transition-all duration-200">
                <option value="">All Statuses</option>
                <option value="under_review">Under Review</option>
                <option value="revision_needed">Revision Needed</option>
                <option value="rejected">Rejected</option>
                <option value="approved">Approved</option>
            </select>
        </div>
    </div>

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
