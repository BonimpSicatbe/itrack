<div>
    <div>
        <!-- Tab Content -->
        <div class="bg-white rounded-xl p-6">
            <!-- Requirement Report Tab -->
            <div class="space-y-6">
                <!-- Header with Title and Generate Button -->
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Requirement Report</h2>
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
                                <option value="">Select Semester</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hybrid Search Box for Requirements -->
                        <div class="relative">
                            <label for="requirementSearch" class="block text-sm font-medium text-gray-700 mb-1">
                                Search Requirements
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live="search"
                                    wire:focus="showDropdown"
                                    id="requirementSearch"
                                    placeholder="{{ $selectedSemester ? 'Click to see requirements for ' . ($semesters->firstWhere('id', $selectedSemester)->name ?? 'selected semester') : 'Please select a semester first' }}"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ !$selectedSemester ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    {{ !$selectedSemester ? 'disabled' : '' }}
                                >
                                @if($selectedRequirement)
                                    <button 
                                        wire:click="clearRequirementSelection"
                                        type="button"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>

                            @if(!$selectedSemester)
                                <p class="text-xs text-red-500 mt-1">Please select a semester first to search requirements</p>
                            @endif

                            <!-- Requirement Dropdown -->
                            @if($selectedSemester && $showRequirementDropdown && $requirementSearchResults->count() > 0)
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                    <div class="py-1">
                                        @if(empty($search))
                                            <div class="px-4 py-2 text-xs text-gray-500 bg-gray-50 border-b">
                                                Showing all requirements for {{ $semesters->firstWhere('id', $selectedSemester)->name }}
                                            </div>
                                        @endif
                                        @foreach($requirementSearchResults as $requirement)
                                            <button 
                                                wire:click="selectRequirement({{ $requirement->id }})"
                                                type="button"
                                                wire:key="requirement-{{ $requirement->id }}"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 border-b border-gray-100 last:border-b-0"
                                            >
                                                <div class="font-medium">{{ $requirement->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $requirement->semester->name ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-600">Due: {{ $requirement->due->format('M j, Y') }}</div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($selectedSemester && $showRequirementDropdown && $requirementSearchResults->count() === 0 && !empty($search))
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-xl shadow-lg">
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                        No requirements found for "{{ $search }}" in {{ $semesters->firstWhere('id', $selectedSemester)->name }}
                                    </div>
                                </div>
                            @endif

                            @if($selectedSemester && $showRequirementDropdown && $requirementSearchResults->count() === 0 && empty($search))
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-xl shadow-lg">
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                        No requirements found for {{ $semesters->firstWhere('id', $selectedSemester)->name }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Submission Status Filter -->
                        <div>
                            <label for="submissionFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                Submission Filter
                            </label>
                            <select wire:model.live="submissionFilter" id="submissionFilter" 
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="all">All Submissions</option>
                                <option value="with_submission">With Submission</option>
                                <option value="no_submission">No Submission</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Requirement Details Table -->
                <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 relative">
                    @if($selectedRequirement)
                        <!-- Display selected requirement information -->
                        <div class="p-6">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Requirement Name:</p>
                                        <p class="text-base font-semibold text-gray-900">{{ $selectedRequirement->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Semester:</p>
                                        <p class="text-sm text-gray-900">{{ $selectedRequirement->semester->name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Due Date:</p>
                                        <p class="text-sm text-gray-900">{{ $selectedRequirement->due->format('F j, Y') }}</p>
                                    </div>
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
                                        <div class="summary-name text-xs font-semibold text-gray-700 uppercase tracking-wide">Total Faculty</div>
                                        <div class="summary-value text-2xl font-bold text-green-600 mt-1">{{ $summaryData['total_instructors'] }}</div>
                                    </div>
                                    <div class="summary-item bg-white border border-green-500 rounded-lg p-4 text-center">
                                        <div class="summary-name text-xs font-semibold text-gray-700 uppercase tracking-wide">Submitted</div>
                                        <div class="summary-value text-2xl font-bold text-green-500 mt-1">{{ $summaryData['submitted_count'] }}</div>
                                    </div>
                                    <div class="summary-item bg-white border border-green-500 rounded-lg p-4 text-center">
                                        <div class="summary-name text-xs font-semibold text-gray-700 uppercase tracking-wide">No Submission</div>
                                        <div class="summary-value text-2xl font-bold text-green-700 mt-1">{{ $summaryData['no_submission_count'] }}</div>
                                    </div>
                                    <div class="summary-item bg-white border border-green-500 rounded-lg p-4 text-center">
                                        <div class="summary-name text-xs font-semibold text-gray-700 uppercase tracking-wide">Completion Rate</div>
                                        <div class="summary-value text-2xl font-bold text-green-500 mt-1">{{ $summaryData['completion_rate'] }}%</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Submissions Section -->
                            <div class="detailed-submissions">
                                <div class="section-title mb-4">
                                    <h3 class="text-base font-semibold text-gray-900 border-b pb-2">FACULTY SUBMISSIONS BY COURSE</h3>
                                </div>

                                @php
                                    $detailedData = $this->getDetailedSubmissions();
                                @endphp

                                @if(count($detailedData['instructors_with_courses']) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="w-full table-auto">
                                            <thead>
                                                <tr class="bg-green-50 border-b border-green-200">
                                                    <th class="w-2/5 text-left py-3 px-4 text-xs font-semibold text-green-800 uppercase">Faculty Information</th>
                                                    <th class="w-2/5 text-left py-3 px-4 text-xs font-semibold text-green-800 uppercase">Course Details</th>
                                                    <th class="w-1/5 text-center py-3 px-4 text-xs font-semibold text-green-800 uppercase">Submission</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($detailedData['instructors_with_courses'] as $instructorData)
                                                    @php
                                                        $instructor = $instructorData['instructor'];
                                                        $courseSubmissions = $instructorData['courseSubmissions'];
                                                        $isFirstCourse = true;
                                                        // Format middle name for display
                                                        $formattedMiddleName = $instructor->middlename ? substr(trim($instructor->middlename), 0, 1) . '.' : '';
                                                    @endphp
                                                    
                                                    @foreach($courseSubmissions as $courseData)
                                                        @php
                                                            $course = $courseData['course'];
                                                            $submission = $courseData['submission'];
                                                        @endphp
                                                        
                                                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                                                            <td class="py-3 px-4 align-top">
                                                                @if($isFirstCourse)
                                                                    <div class="font-semibold text-gray-800">{{ $instructor->firstname }} {{ $formattedMiddleName }} {{ $instructor->lastname }} {{ $instructor->extensionname }}</div>
                                                                    <div class="text-xs text-gray-500 mt-1">{{ $instructor->position }}</div>
                                                                    <div class="text-xs text-gray-500 mt-1">{{ $instructor->email }}</div>
                                                                    <div class="text-xs text-gray-600">{{ $instructor->college->name ?? 'N/A' }}</div>
                                                                    @php $isFirstCourse = false; @endphp
                                                                @endif
                                                            </td>
                                                            <td class="py-3 px-4 align-top">
                                                                <div class="course-info">
                                                                    <div class="font-semibold text-green-600 text-sm">{{ $course->course_code }}</div>
                                                                    <div class="text-sm text-gray-800">{{ $course->course_name }}</div>
                                                                    <div class="text-xs text-gray-500 mt-1">
                                                                        {{ $course->program->program_code ?? 'N/A' }} - {{ $course->program->program_name ?? 'N/A' }}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="py-3 px-4 align-top text-center">
                                                                @if($submission)
                                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                                        Submitted
                                                                    </span>
                                                                    @if($submission->submitted_at)
                                                                        <div class="text-xs text-gray-500 mt-1">
                                                                            {{ $submission->submitted_at->format('M j, Y') }}
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                                        No Submission
                                                                    </span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="no-data text-center py-8 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                                        <i class="fa-solid fa-table text-3xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500 font-medium">
                                            @if($selectedRequirement && $selectedSemester)
                                                No faculty submissions found matching the current filter criteria.
                                            @else
                                                No faculty with course assignments found for this semester.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Empty state when no requirement is selected -->
                        <div class="flex flex-col items-center justify-center bg-white py-8 text-gray-500">
                            <i class="fa-solid fa-clipboard-list text-3xl text-gray-300 mb-3"></i>
                            <p class="text-sm font-semibold">Select a requirement to view its report</p>
                            <p class="text-sm text-gray-400 mt-1">
                                @if($selectedSemester)
                                    Click on the search box above to see requirements for {{ $semesters->firstWhere('id', $selectedSemester)->name }}
                                @else
                                    Please select a semester first
                                @endif
                            </p>
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
            const searchContainer = document.getElementById('requirementSearch')?.closest('.relative');
            
            // If click is outside search container, close dropdown
            if (searchContainer && !searchContainer.contains(e.target)) {
                @this.set('showRequirementDropdown', false);
            }
        });

        // Listen for clear event and manually clear the input
        @this.on('requirement-cleared', () => {
            const searchInput = document.getElementById('requirementSearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
        });

        // Prevent dropdown from closing when clicking inside it
        document.addEventListener('livewire:load', () => {
            const searchContainer = document.getElementById('requirementSearch')?.closest('.relative');
            if (searchContainer) {
                const dropdown = searchContainer.querySelector('.absolute.z-50');
                if (dropdown) {
                    dropdown.addEventListener('click', (e) => {
                        e.stopPropagation();
                    });
                }
            }
        });

        // Handle opening PDF in new tab
        @this.on('open-new-tab', (event) => {
            window.open(event.url, '_blank');
        });
    });
    </script>
</div>