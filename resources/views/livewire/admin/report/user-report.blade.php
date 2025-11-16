<div>
    <div>
        <!-- Tab Content -->
        <div class="bg-white rounded-xl p-6">
            <!-- Faculty Report Tab -->
            <div class="space-y-6">
                <!-- Header with Title and Generate Button -->
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Faculty Report</h2>
                    <button wire:click="generateReport" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Generate PDF Report
                        <i class="fa-regular fa-file-export ml-2 text-lg"></i>
                    </button>
                </div>
                
                <!-- Filters Section -->
                <div class="bg-gray-50 rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Semester Filter -->
                        <div>
                            <label for="selectedSemester" class="block text-sm font-medium text-gray-700 mb-1">
                                Semester
                            </label>
                            <select wire:model.live="selectedSemester" id="selectedSemester" 
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hybrid Search Box for Faculty -->
                        <div class="relative">
                            <label for="userSearch" class="block text-sm font-medium text-gray-700 mb-1">
                                Search Faculty
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live="search"
                                    wire:focus="showDropdown"
                                    id="userSearch"
                                    placeholder="Click to see all faculty or type to search..."
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                >
                                @if($selectedUser)
                                    <button 
                                        wire:click="clearUserSelection"
                                        type="button"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>

                            <!-- User Dropdown -->
                            @if($showUserDropdown && $userSearchResults->count() > 0)
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                    <div class="py-1">
                                        @if(empty($search))
                                            <div class="px-4 py-2 text-xs text-gray-500 bg-gray-50 border-b">
                                                Showing all faculty members
                                            </div>
                                        @endif
                                        @foreach($userSearchResults as $user)
                                            <button 
                                                wire:click="selectUser({{ $user->id }})"
                                                type="button"
                                                wire:key="user-{{ $user->id }}"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 border-b border-gray-100 last:border-b-0"
                                            >
                                                <div class="font-medium">{{ $user->full_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                                @if($user->position)
                                                    <div class="text-xs text-gray-600">{{ $user->position }}</div>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($showUserDropdown && $userSearchResults->count() === 0 && !empty($search))
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-xl shadow-lg">
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                        No faculty members found for "{{ $search }}"
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- New Submission Status Filter -->
                        <div>
                            <label for="submissionFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                Submission Filter
                            </label>
                            <select wire:model.live="submissionFilter" id="submissionFilter" 
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="all">All Requirements</option>
                                <option value="with_submission">With Submission</option>
                                <option value="no_submission">No Submission</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Faculty Details Table -->
                <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 relative">
                    @if($selectedUser)
                        <!-- Display selected user information -->
                        <div class="p-6">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Name:</p>
                                        <p class="text-base font-semibold text-gray-900">{{ $selectedUser->full_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Email:</p>
                                        <p class="text-sm text-gray-900">{{ $selectedUser->email }}</p>
                                    </div>
                                    @if($selectedUser->position)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Position:</p>
                                        <p class="text-sm text-gray-900">{{ $selectedUser->position }}</p>
                                    </div>
                                    @endif
                                    @if($selectedUser->college)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">College:</p>
                                        <p class="text-sm text-gray-900">{{ $selectedUser->college->name }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Report Summary Section -->
                            <div class="mb-8">
                                <div class="section-title mb-4">
                                    <h3 class="text-base font-semibold text-gray-900 border-b pb-2">OVERALL SUBMISSION SUMMARY</h3>
                                </div>
                                
                                @php
                                    // Calculate summary data
                                    $summaryData = $this->getSubmissionSummary();
                                @endphp
                                
                                <div class="summary-grid grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                    <div class="summary-item bg-white border border-green-500 rounded-lg p-4 text-center">
                                        <div class="summary-name text-xs font-semibold text-gray-700 uppercase tracking-wide">Total Requirements</div>
                                        <div class="summary-value text-2xl font-bold text-green-600 mt-1">{{ $summaryData['total_requirements'] }}</div>
                                    </div>
                                    <div class="summary-item bg-white border border-green-500 rounded-lg p-4 text-center">
                                        <div class="summary-name text-xs font-semibold text-gray-700 uppercase tracking-wide">Submitted</div>
                                        <div class="summary-value text-2xl font-bold text-green-500 mt-1">{{ $summaryData['submitted_count'] }}</div>
                                    </div>
                                    <div class="summary-item bg-white border border-green-500 rounded-lg p-4 text-center">
                                        <div class="summary-name text-xs font-semibold text-gray-700 uppercase tracking-wide">Approved</div>
                                        <div class="summary-value text-2xl font-bold text-green-700 mt-1">{{ $summaryData['approved_count'] }}</div>
                                    </div>
                                    <div class="summary-item bg-white border border-green-500 rounded-lg p-4 text-center">
                                        <div class="summary-name text-xs font-semibold text-gray-700 uppercase tracking-wide">No Submission</div>
                                        <div class="summary-value text-2xl font-bold text-green-500 mt-1">{{ $summaryData['no_submission_count'] }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Requirements Section -->
                            <div class="detailed-requirements">
                                <div class="section-title mb-4">
                                    <h3 class="text-base font-semibold text-gray-900 border-b pb-2">DETAILED REQUIREMENTS CHECKLIST</h3>
                                </div>

                                @php
                                    $detailedData = $this->getDetailedRequirements();
                                @endphp

                                @forelse($detailedData['courses_by_program'] as $programId => $programCourses)
                                    @php $program = $programCourses->first()->course->program; @endphp
                                    <div class="program-title bg-gray-100 border-l-4 border-green-500 px-4 py-3 mb-4">
                                        <h4 class="font-bold text-gray-800">{{ $program->program_code }} - {{ $program->program_name }}</h4>
                                    </div>
                                    
                                    @foreach($programCourses as $assignment)
                                        <div class="course-table bg-white border border-gray-200 rounded-lg overflow-hidden mb-6">
                                            <!-- Course Header -->
                                            <div class="course-header bg-gray-50 border-b border-gray-200 px-4 py-3">
                                                <div class="flex justify-between items-center">
                                                    <div class="course-info">
                                                        <span class="font-semibold text-gray-800">{{ $assignment->course->course_code }} - {{ $assignment->course->course_name }}</span>
                                                    </div>
                                                    <div class="course-type">
                                                        <span class="text-sm font-medium text-gray-600 px-2 py-1 ">
                                                            {{ $assignment->course->courseType->name ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Requirements Table -->
                                            <table class="w-full table-fixed">
                                                <thead>
                                                    <tr class="course-header-row bg-green-50 border-b border-green-200">
                                                        <th class="w-3/12 text-left py-3 px-4 text-xs font-semibold text-green-800 uppercase">Requirement</th>
                                                        <th class="w-2/12 text-left py-3 px-4 text-xs font-semibold text-green-800 uppercase">Due Date</th>
                                                        <th class="w-5/12 text-left py-3 px-4 text-xs font-semibold text-green-800 uppercase">Submitted Files</th>
                                                        <th class="w-2/12 text-center py-3 px-4 text-xs font-semibold text-green-800 uppercase">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($assignment->filtered_requirements as $requirement)
                                                        @php
                                                            $key = $assignment->course_id . '_' . $requirement->id;
                                                            $submissions = $detailedData['grouped_submissions'][$key] ?? [];
                                                            $submissionCount = count($submissions);
                                                        @endphp
                                                        
                                                        @if($submissionCount > 0)
                                                            @foreach($submissions as $index => $submission)
                                                                <tr class="req-row border-b border-gray-100 hover:bg-gray-50">
                                                                    <td class="w-3/12 py-3 px-4 align-top">
                                                                        @if($index === 0)
                                                                            <div class="req-name font-semibold text-gray-800">{{ $requirement->name }}</div>
                                                                        @endif
                                                                    </td>
                                                                    
                                                                    <td class="w-2/12 py-3 px-4 align-top">
                                                                        @if($index === 0)
                                                                            <div class="req-due text-xs text-gray-500">{{ $requirement->due->format('M j, Y') }}</div>
                                                                        @endif
                                                                    </td>

                                                                    <td class="w-5/12 py-3 px-4 align-top">
                                                                        <div class="file-list border-l-2 border-green-400 pl-3">
                                                                            @if($submission->media->count() > 0)
                                                                                @foreach($submission->media as $file)
                                                                                    <div class="file-item text-xs text-gray-600 mb-1 break-words">• {{ $file->file_name }}</div>
                                                                                @endforeach
                                                                            @else
                                                                                <div class="no-files text-xs text-gray-400 italic">No files in this submission</div>
                                                                            @endif
                                                                        </div>
                                                                    </td>

                                                                    <td class="w-2/12 py-3 px-4 align-top text-center">
                                                                        @php
                                                                            $statusClass = match(strtolower($submission->status)) {
                                                                                'under_review' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                                                                'revision_needed' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                                                                'approved' => 'bg-green-100 text-green-800 border border-green-200',
                                                                                'rejected' => 'bg-red-100 text-red-800 border border-red-200',
                                                                                default => 'bg-gray-100 text-gray-800 border border-gray-200'
                                                                            };
                                                                        @endphp
                                                                        <div class="flex flex-col items-center space-y-1">
                                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                                                                {{ \App\Models\SubmittedRequirement::statuses()[$submission->status] ?? $submission->status }}
                                                                            </span>
                                                                            @if($submission->submitted_at)
                                                                                <div class="submission-date text-xs text-gray-500">
                                                                                    {{ $submission->submitted_at->format('M j, Y') }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr class="req-row border-b border-gray-100 hover:bg-gray-50">
                                                                <td class="w-3/12 py-3 px-4 align-top">
                                                                    <div class="req-name font-semibold text-gray-800">{{ $requirement->name }}</div>
                                                                </td>
                                                                
                                                                <td class="w-2/12 py-3 px-4 align-top">
                                                                    <div class="req-due text-xs text-gray-500">{{ $requirement->due->format('M j, Y') }}</div>
                                                                </td>

                                                                <td class="w-5/12 py-3 px-4 align-top">
                                                                    <div class="file-list border-l-2 border-gray-300 pl-3">
                                                                        <div class="no-files text-xs text-gray-400 italic">No submission</div>
                                                                    </div>
                                                                </td>

                                                                <td class="w-2/12 py-3 px-4 align-top text-center">
                                                                    <div class="flex flex-col items-center space-y-1">
                                                                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-800">
                                                                            
                                                                        </span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                @empty
                                    <div class="no-data text-center py-8 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                                        <i class="fa-solid fa-table text-3xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500 font-medium">
                                            @if($selectedUser && $selectedSemester)
                                                No requirements found matching the current filter criteria.
                                            @else
                                                No assigned courses found for this semester.
                                            @endif
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <!-- Empty state when no user is selected -->
                        <div class="flex flex-col items-center justify-center bg-white py-8 text-gray-500">
                            <i class="fa-solid fa-user-plus text-3xl text-gray-300 mb-3"></i>
                            <p class="text-sm font-semibold">Select a faculty member to view their report</p>
                            <p class="text-sm text-gray-400 mt-1">Click on the search box above to see all faculty members</p>
                        </div>
                    @endif
                </div>

                <!-- Flash Messages -->
                @if (session()->has('message'))
                    <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ session('message') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('livewire:initialized', () => {
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const searchContainer = document.getElementById('userSearch')?.closest('.relative');
            
            // If click is outside search container, close dropdown
            if (searchContainer && !searchContainer.contains(e.target)) {
                @this.set('showUserDropdown', false);
            }
        });

        // Listen for clear event and manually clear the input
        @this.on('user-cleared', () => {
            const searchInput = document.getElementById('userSearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
        });

        // Prevent dropdown from closing when clicking inside it
        document.addEventListener('livewire:load', () => {
            const searchContainer = document.getElementById('userSearch')?.closest('.relative');
            if (searchContainer) {
                const dropdown = searchContainer.querySelector('.absolute.z-50');
                if (dropdown) {
                    dropdown.addEventListener('click', (e) => {
                        e.stopPropagation();
                    });
                }
            }
        });

        // ADD THIS: Handle opening PDF in new tab (same as report-index)
        @this.on('open-new-tab', (event) => {
            window.open(event.url, '_blank');
        });
    });
    </script>
</div>