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
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name or file..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition duration-150"
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
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md shadow-sm"
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
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
        @if($recentSubmissions->count() > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($recentSubmissions as $submission)
                    <li 
                        wire:click="showRequirementDetail({{ $submission->id }})"
                        class="px-6 py-4 hover:bg-emerald-50 transition-colors duration-150 cursor-pointer"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @if($submission->submissionFile)
                                        <div class="h-10 w-10 rounded-md bg-emerald-100 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $submission->requirement->name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Submitted {{ $submission->submitted_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                      style="background-color: {{ \App\Models\SubmittedRequirement::getStatusColor($submission->status) }}; color: white">
                                    {{ $submission->status_text }}
                                </span>
                                @if($submission->submissionFile)
                                    <span class="text-xs text-gray-400 flex items-center">
                                        <svg class="h-3 w-3 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        {{ $submission->submissionFile->file_name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No submissions found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($statusFilter || $search)
                        Try adjusting your search or filter criteria
                    @else
                        You haven't submitted any requirements yet
                    @endif
                </p>
            </div>
        @endif
    </div>

    <!-- Include the Requirement Detail Modal component -->
    @livewire('user.requirement-detail-modal')
</div>