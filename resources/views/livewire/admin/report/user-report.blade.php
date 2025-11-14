<div class="space-y-6">
    <h2 class="text-lg font-semibold text-gray-900">Users Report</h2>
    
    <!-- Search Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <!-- Semester Filter -->
            <div>
                <label for="userReportSemester" class="block text-sm font-medium text-gray-700 mb-1">
                    Semester
                </label>
                <select wire:model="selectedSemester" id="userReportSemester" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- User Search -->
            <div>
                <label for="userSearch" class="block text-sm font-medium text-gray-700 mb-1">
                    Search User
                </label>
                <input 
                    type="text" 
                    wire:model="search"
                    id="userSearch"
                    placeholder="Search by name or email..."
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                >
            </div>

            <!-- Search Button -->
            <div class="flex items-end">
                <button wire:click="searchUser" 
                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fa-solid fa-search mr-2"></i>
                    Search User
                </button>
            </div>
        </div>
    </div>

    <!-- User Report Display -->
    @if($selectedUser && !empty($userReportData))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <!-- Action Button -->
            <div class="flex justify-end mb-6">
                <button wire:click="generateUserReport" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fa-solid fa-file-pdf mr-2"></i>
                    Generate PDF Report
                </button>
            </div>

            <!-- Report Header -->
            <div class="text-center mb-8 border-b border-gray-200 pb-6">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-gray-900">FACULTY END-OF-SEMESTER REPORT</h3>
                </div>
                
                <!-- Faculty Details -->
                <div class="text-left mb-6">
                    <h4 class="font-semibold text-gray-900 mb-2">Faculty Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium">Name:</span> {{ $selectedUser->full_name }}
                        </div>
                        <div>
                            <span class="font-medium">Email:</span> {{ $selectedUser->email }}
                        </div>
                        <div>
                            <span class="font-medium">College:</span> {{ $selectedUser->college->name ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="font-medium">Semester:</span> {{ $userReportData['semester']->name }}
                        </div>
                    </div>
                </div>

                <!-- Overall Submission Summary -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3 text-center">OVERALL SUBMISSION SUMMARY</h4>
                    <div class="grid grid-cols-4 gap-4 text-center">
                        <div class="bg-white rounded p-3 border border-gray-200">
                            <div class="text-2xl font-bold text-gray-900">{{ $userReportData['total_requirements'] }}</div>
                            <div class="text-xs text-gray-600">TOTAL REQUIREMENTS</div>
                        </div>
                        <div class="bg-white rounded p-3 border border-gray-200">
                            <div class="text-2xl font-bold text-blue-600">{{ $userReportData['submitted_count'] }}</div>
                            <div class="text-xs text-gray-600">SUBMITTED</div>
                        </div>
                        <div class="bg-white rounded p-3 border border-gray-200">
                            <div class="text-2xl font-bold text-green-600">{{ $userReportData['approved_count'] }}</div>
                            <div class="text-xs text-gray-600">APPROVED</div>
                        </div>
                        <div class="bg-white rounded p-3 border border-gray-200">
                            <div class="text-2xl font-bold text-amber-600">{{ $userReportData['no_submission_count'] }}</div>
                            <div class="text-xs text-gray-600">NO SUBMISSION</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Requirements Checklist -->
            <div class="mb-6">
                <h4 class="font-semibold text-gray-900 mb-4">DETAILED REQUIREMENTS CHECKLIST</h4>
                
                @foreach($userReportData['programs'] as $programData)
                    <!-- Program Header -->
                    <div class="mb-4">
                        <h5 class="font-semibold text-gray-800 text-lg">
                            {{ $programData['program']->program_code }} - {{ $programData['program']->program_name }}
                        </h5>
                    </div>

                    @foreach($programData['courses'] as $courseData)
                        <!-- Course Header -->
                        <div class="mb-3 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h6 class="font-semibold text-gray-700">
                                {{ $courseData['course']->course_code }} - {{ $courseData['course']->course_name }}
                            </h6>
                            <p class="text-sm text-gray-600">{{ $courseData['course']->type ?? 'Core Course' }}</p>
                        </div>

                        <!-- Requirements Table -->
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full border-collapse border border-gray-300 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Requirement</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Due Date</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Submitted Files</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($courseData['requirements'] as $requirementData)
                                        <tr class="hover:bg-gray-50">
                                            <td class="border border-gray-300 px-4 py-2 text-gray-700">
                                                {{ $requirementData['requirement']->name }}
                                            </td>
                                            <td class="border border-gray-300 px-4 py-2 text-gray-600">
                                                {{ $requirementData['requirement']->due_date ? $requirementData['requirement']->due_date->format('M d, Y') : 'No due date' }}
                                            </td>
                                            <td class="border border-gray-300 px-4 py-2 text-gray-600">
                                                @if(!empty($requirementData['files']))
                                                    <ul class="list-disc list-inside space-y-1">
                                                        @foreach($requirementData['files'] as $file)
                                                            <li class="text-xs">{{ $file }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-gray-400">No submission</span>
                                                @endif
                                            </td>
                                            <td class="border border-gray-300 px-4 py-2">
                                                @if($requirementData['status'] === 'APPROVED')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        APPROVED
                                                    </span>
                                                @elseif($requirementData['status'] === 'UNDER REVIEW')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        UNDER REVIEW
                                                    </span>
                                                    @if($requirementData['submitted_at'])
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            {{ $requirementData['submitted_at']->format('M d, Y') }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                        NO SUBMISSION
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                    <div class="border-t border-gray-300 my-6"></div>
                @endforeach
            </div>

            <!-- Report Footer -->
            <div class="text-center text-xs text-gray-500 border-t border-gray-200 pt-4">
                <p>Generated By: iTrack</p>
                <p>Generated On: {{ now()->format('l, F d, Y \a\t h:i A') }}</p>
            </div>
        </div>
    @elseif($selectedUser === null && $search)
        <!-- No user found message -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <i class="fa-solid fa-user-slash text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 font-semibold">No user found with the provided search criteria.</p>
            <p class="text-sm text-gray-400 mt-2">Please try searching with a different name or email address.</p>
        </div>
    @else
        <!-- Initial state - instructions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <i class="fa-solid fa-users text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 font-semibold">Search for a user to generate their report</p>
            <p class="text-sm text-gray-400 mt-2">Enter a name or email address above and click "Search User" to view the faculty report</p>
        </div>
    @endif
</div>