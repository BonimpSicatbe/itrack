<div>
    <div class="bg-white rounded-xl p-6">
        <!-- Header with Title and Generate Button -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Custom Year Report</h2>
            <button wire:click="generatePdf" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Generate PDF Report
                <i class="fa-regular fa-file-export ml-2 text-lg"></i>
            </button>
        </div>
        
        <!-- Filters Section -->
        <div class="bg-gray-50 rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Start Date (Year-Month) -->
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                        Start Period
                    </label>
                    <input type="month" 
                           wire:model.live="startDate" 
                           id="startDate"
                           class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- End Date (Year-Month) -->
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                        End Period
                    </label>
                    <input type="month" 
                           wire:model.live="endDate" 
                           id="endDate"
                           class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- College Filter -->
                <div>
                    <label for="selectedCollege" class="block text-sm font-medium text-gray-700 mb-1">
                        College
                    </label>
                    <select wire:model.live="selectedCollege" id="selectedCollege" 
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}">{{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Faculty -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        Search Faculty
                    </label>
                    <input type="text" 
                           wire:model.live="search"
                           id="search"
                           placeholder="Type to search faculty..."
                           class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Generate Report Button -->
            <div class="mt-4 flex justify-end">
                <button wire:click="generateReport" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fa-solid fa-refresh mr-2"></i>
                    Generate Report
                </button>
            </div>
        </div>

        <!-- Summary Statistics -->
        @if(!empty($summaryStats))
        <div class="mb-6">
            <div class="section-title mb-4">
                <h3 class="text-base font-semibold text-gray-900 border-b pb-2">REPORT SUMMARY</h3>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-4">
                <div class="bg-white border border-green-500 rounded-lg p-4 text-center">
                    <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Date Range</div>
                    <div class="text-sm font-bold text-green-600 mt-1">{{ $summaryStats['date_range']['formatted'] }}</div>
                </div>
                <div class="bg-white border border-green-500 rounded-lg p-4 text-center">
                    <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Total Faculty</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ $summaryStats['total_faculty'] }}</div>
                </div>
                <div class="bg-white border border-green-500 rounded-lg p-4 text-center">
                    <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Total Courses</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ $summaryStats['total_courses'] }}</div>
                </div>
                <div class="bg-white border border-green-500 rounded-lg p-4 text-center">
                    <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Total Submissions</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ $summaryStats['total_submissions'] }}</div>
                </div>
                <div class="bg-white border border-green-500 rounded-lg p-4 text-center">
                    <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Approved</div>
                    <div class="text-2xl font-bold text-green-700 mt-1">{{ $summaryStats['total_approved'] }}</div>
                </div>
                <div class="bg-white border border-green-500 rounded-lg p-4 text-center">
                    <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Submission Rate</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ $summaryStats['overall_submission_rate'] }}%</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Report Data -->
        <div class="overflow-x-auto bg-white rounded-xl border border-gray-200">
            @if(count($reportData) > 0)
                @foreach($reportData as $facultyData)
                    @php $faculty = $facultyData['faculty']; @endphp
                    <div class="faculty-section border-b border-gray-200 last:border-b-0">
                        <!-- Faculty Header -->
                        <div class="faculty-header bg-gray-50 border-b border-gray-200 px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Left Section -->
                                <div class="faculty-info-left">
                                    <h4 class="font-bold text-gray-800 text-lg">{{ $faculty->full_name }}</h4>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <!-- Teaching Date Range -->
                                        @if($faculty->teaching_started_at)
                                            <div class="mb-1">
                                                <span class="font-medium">Teaching Period:</span> 
                                                {{ \Carbon\Carbon::parse($faculty->teaching_started_at)->format('F j, Y') }} - 
                                                @if($faculty->teaching_ended_at)
                                                    {{ \Carbon\Carbon::parse($faculty->teaching_ended_at)->format('F j, Y') }}
                                                @else
                                                    <span class="text-green-600 font-semibold">Present</span>
                                                @endif
                                            </div>
                                        @endif
                                        <!-- Submission Rate -->
                                        <div class="font-semibold text-green-600">
                                            {{ $facultyData['submission_rate'] }}% Submission Rate
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Section -->
                                <div class="faculty-info-right">
                                    <div class="text-sm text-gray-600">
                                        <!-- Position -->
                                        @if($faculty->position)
                                            <div class="mb-1">
                                                <span class="font-medium">Position:</span> {{ $faculty->position }}
                                            </div>
                                        @endif
                                        <!-- Email -->
                                        <div class="mb-1">
                                            <span class="font-medium">Email:</span> {{ $faculty->email }}
                                        </div>
                                        <!-- College -->
                                        <div>
                                            <span class="font-medium">College:</span> {{ $faculty->college->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Courses and Requirements -->
                        <div class="courses-container">
                            @foreach($facultyData['courses'] as $courseData)
                                @php $assignment = $courseData['assignment']; @endphp
                                <div class="course-item border-b border-gray-100 last:border-b-0">
                                    <div class="course-header bg-blue-50 px-6 py-3">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="font-semibold text-gray-800">
                                                    {{ $assignment->course->course_code }} - {{ $assignment->course->course_name }}
                                                </span>
                                                <span class="text-sm text-gray-600 ml-2">
                                                    ({{ $assignment->semester->name }} • {{ $assignment->course->program->program_name }})
                                                </span>
                                            </div>
                                            <div class="text-sm font-medium text-blue-600">
                                                {{ $courseData['total_submissions'] }}/{{ count($courseData['requirements']) }} Submitted
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Requirements List -->
                                    <div class="requirements-list px-6 py-4">
                                        <div class="grid grid-cols-1 gap-3">
                                            @foreach($courseData['requirements'] as $reqData)
                                                @php 
                                                    $requirement = $reqData['requirement'];
                                                    $hasSubmission = $reqData['submission_count'] > 0;
                                                @endphp
                                                <div class="requirement-item flex justify-between items-center p-3 border border-gray-200 rounded-lg {{ $hasSubmission ? 'bg-green-50 border-green-200' : 'bg-gray-50' }}">
                                                    <div class="requirement-info">
                                                        <div class="font-medium text-gray-800">{{ $requirement->name }}</div>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            Due: {{ $requirement->due->format('M j, Y') }}
                                                        </div>
                                                    </div>
                                                    <div class="requirement-status">
                                                        @if($hasSubmission)
                                                            @php 
                                                                $submission = $reqData['submissions']->first();
                                                                $statusClass = match(strtolower($submission->status)) {
                                                                    'under_review' => 'bg-blue-100 text-blue-800',
                                                                    'revision_needed' => 'bg-yellow-100 text-yellow-800',
                                                                    'approved' => 'bg-green-100 text-green-800',
                                                                    'rejected' => 'bg-red-100 text-red-800',
                                                                    default => 'bg-gray-100 text-gray-800'
                                                                };
                                                            @endphp
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                                                {{ \App\Models\SubmittedRequirement::statuses()[$submission->status] ?? $submission->status }}
                                                            </span>
                                                            <div class="text-xs text-gray-500 text-center mt-1">
                                                                {{ $submission->submitted_at->format('M j, Y') }}
                                                            </div>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                No Submission
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @elseif(!empty($summaryStats))
                <div class="text-center py-12">
                    <i class="fa-solid fa-search text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 font-medium">No data found for the selected filters</p>
                    <p class="text-sm text-gray-400 mt-1">Try adjusting your date range or search criteria</p>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fa-solid fa-chart-line text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 font-medium">Generate a report to view data</p>
                    <p class="text-sm text-gray-400 mt-1">Select a date range and click "Generate Report"</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Handle opening PDF in new tab
            Livewire.on('open-new-tab', (event) => {
                window.open(event.url, '_blank');
            });
        });
    </script>
</div>