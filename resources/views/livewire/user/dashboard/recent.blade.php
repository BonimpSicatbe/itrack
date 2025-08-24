<div class="bg-white rounded-lg border p-4">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Recent Submissions</h3>
        <a href="{{ route('user.recents') }}" 
           class="text-sm font-medium hover:underline"
           style="color: #1C7C54;">
            View All →
        </a>
    </div>

    {{-- List --}}
    @if($recentSubmissions->count() > 0)
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($recentSubmissions as $submission)
                <div wire:click="showRequirementDetail({{ $submission->id }})"
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer transition-colors"
                     style="hover:background-color: #f0fdf4;">
                    
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        {{-- File Icon --}}
                        @if($submission->submissionFile)
                            <i class="fas fa-file-alt text-lg" style="color: #1C7C54;"></i>
                        @else
                            <i class="fas fa-file-times text-gray-400 text-lg"></i>
                        @endif
                        
                        {{-- File Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">
                                {{ $submission->requirement?->name ?? 'Deleted Requirement' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $submission->submitted_at->format('M j, Y') }}
                                @if($submission->submissionFile)
                                    • {{ $submission->submissionFile->file_name }}
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Status --}}
                    @php
                        $status = strtolower($submission->status);
                        $badgeColor = match($status) {
                            'approved' => '#22c55e',  // Green
                            'rejected' => '#ef4444',  // Red
                            'pending' => '#f59e0b',   // Orange/amber
                            default => '#6b7280'      // Gray
                        };
                    @endphp
                    <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap text-white"
                          style="background-color: {{ $badgeColor }};">
                        {{ $submission->status_text }}
                    </span>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-2xl mb-2"></i>
            <p>No recent submissions</p>
        </div>
    @endif

    <!-- Include the Requirement Detail Modal component -->
    @livewire('user.requirement-detail-modal')
</div>