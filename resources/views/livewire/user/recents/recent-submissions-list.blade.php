<div class="flex flex-col gap-6">
    {{-- Header and Search Bar Row --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Recent Submissions</h1>
            <p class="text-sm text-gray-500 mt-1">View and manage your recent requirement submissions</p>
        </div>
        
        {{-- Search Bar --}}
        <div class="w-full md:w-96">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name or file..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition duration-150"
                    aria-label="Search submissions">
            </div>
        </div>
    </div>

    {{-- Filter Controls --}}
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Status Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                <select 
                    wire:model.live="statusFilter"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm"
                >
                    <option value="">All Statuses</option>
                    <option value="under_review">Under Review</option>
                    <option value="revision_needed">Revision Needed</option>
                    <option value="rejected">Rejected</option>
                    <option value="approved">Approved</option>
                </select>
            </div>
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
