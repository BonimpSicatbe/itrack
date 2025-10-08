<div>
    <!-- Overview Category - Excel-like View -->
    <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 relative">
        <table class="min-w-full border-collapse">
            <!-- Table Header -->
            <thead class="bg-green-700 text-white font-semibold text-sm sticky top-0 z-30">
                <tr>
                    <!-- Fixed Columns -->
                    <th class="p-3 border border-green-600 sticky left-0 bg-green-700 z-40 w-21 min-w-21 border-green-800 relative">
                        <div class="absolute inset-0 border-green-800"></div>
                        ID
                    </th>
                    <th class="p-3 border border-green-600 sticky left-20 bg-green-700 z-40 w-49 min-w-49 border-green-800 relative">
                        <div class="absolute inset-0 border-l-1 border-white"></div>
                        User
                    </th>
                    <th class="p-3 border border-green-600 sticky left-68 bg-green-700 z-40 w-40 min-w-40 border-green-800 relative">
                        <div class="absolute inset-0 border-l-1 border-r-1 border-white"></div>
                        Course
                    </th>
                    
                    <!-- Requirement Columns -->
                    @foreach($overviewData['requirements'] as $requirement)
                        <th class="p-3 border border-green-600 text-center w-40 min-w-40 max-w-60" title="{{ $requirement->name }}">
                            <div class="truncate">
                                {{ \Illuminate\Support\Str::limit($requirement->name, 20) }}
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="bg-white">
                @forelse($overviewData['users'] as $userIndex => $user)
                    @php
                        $courses = $overviewData['userCoursesData'][$user->id] ?? collect();
                        $rowspan = $this->getUserRowspan($user->id, $overviewData['userCoursesData']);
                        $hasCourses = $courses->isNotEmpty();
                    @endphp
                    
                    @if($hasCourses)
                        @foreach($courses as $courseIndex => $course)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <!-- ID Column -->
                                @if($courseIndex === 0)
                                    <td class="p-3 border border-gray-200 sticky left-0 bg-white z-30 text-sm text-gray-600 align-top relative"
                                        rowspan="{{ $rowspan }}">
                                        <div class="absolute inset-0 border-gray-300"></div>
                                        {{ $user->id }}
                                    </td>
                                @endif
                                
                                <!-- User Column -->
                                @if($courseIndex === 0)
                                    <td class="p-3 border border-gray-200 sticky left-20 bg-white z-30 align-top relative"
                                        rowspan="{{ $rowspan }}">
                                        <div class="absolute inset-0 border-l-1 border-gray-300"></div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </td>
                                @endif
                                
                                <!-- Course Column -->
                                <td class="p-3 border border-gray-200 sticky left-68 bg-white z-30 text-sm text-gray-600 relative">
                                    <div class="absolute inset-0 border-l-1 border-r-1 border-gray-200"></div>
                                    <div class="font-medium">{{ $course->course_code }}</div>
                                    <div class="text-xs text-gray-500 truncate" title="{{ $course->course_name }}">
                                        {{ \Illuminate\Support\Str::limit($course->course_name, 25) }}
                                    </div>
                                </td>
                                
                                <!-- Requirement Status Columns -->
                                @foreach($overviewData['requirements'] as $requirement)
                                    @php
                                        $displayText = $this->getSubmissionDisplay($user->id, $requirement->id, $course->id, $overviewData['submissionIndicators']);
                                        $badgeClass = $this->getStatusBadgeClass(strtolower($displayText));
                                    @endphp
                                    <td class="p-3 border border-gray-200 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                            {{ $displayText }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @else
                        <!-- User with no courses -->
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <!-- ID Column -->
                            <td class="p-3 border border-gray-200 sticky left-0 bg-white z-30 text-sm text-gray-600 relative">
                                <div class="absolute inset-0 border-r-2 border-gray-300"></div>
                                {{ $user->id }}
                            </td>
                            
                            <!-- User Column -->
                            <td class="p-3 border border-gray-200 sticky left-20 bg-white z-30 relative">
                                <div class="absolute inset-0 border-l-2 border-r-2 border-gray-300"></div>
                                <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </td>
                            
                            <!-- Course Column -->
                            <td class="p-3 border border-gray-200 sticky left-68 bg-white z-30 text-sm text-gray-600 relative">
                                <div class="absolute inset-0 border-l-2 border-r-2 border-gray-300"></div>
                                <span class="text-gray-400">No course</span>
                            </td>
                            
                            <!-- Requirement Status Columns -->
                            @foreach($overviewData['requirements'] as $requirement)
                                @php
                                    $displayText = '';
                                    $badgeClass = $this->getStatusBadgeClass('not_submitted');
                                @endphp
                                <td class="p-3 border border-gray-200 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        {{ $displayText }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ count($overviewData['requirements']) + 3 }}" class="text-center py-8 text-gray-500">
                            <i class="fa-solid fa-users text-3xl text-gray-300 mb-2"></i>
                            <p class="text-sm font-semibold">No users found.</p>
                            @if($search)
                                <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Summary -->
    <div class="mt-4 flex flex-wrap gap-4 items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
        @php
            $totalCourses = 0;
            foreach($overviewData['userCoursesData'] as $courses) {
                $totalCourses += $courses->count();
            }
        @endphp
        <div class="text-xs font-semibold text-gray-700">
            Total Users: {{ $overviewData['users']->count() }} | 
            Total Courses: {{ $totalCourses }} |
            Total Requirements: {{ $overviewData['requirements']->count() }}
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs">Submitted</span>
            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs">Not Submitted</span>
        </div>
    </div>
</div>