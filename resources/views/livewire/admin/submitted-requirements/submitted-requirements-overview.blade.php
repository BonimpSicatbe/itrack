<div>
    <!-- Overview Category - Excel-like View -->
    <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 relative" style="min-height: 200px;">
        <table class="min-w-full border-collapse">
            <!-- Table Header -->
            <thead class="bg-green-600 text-white font-semibold text-sm sticky top-0 z-30">
                <tr>
                    <th class="p-3 border border-green-500 sticky left-0 bg-green-600 z-40 w-49 min-w-49 relative">
                        <div class="absolute inset-0"></div>
                        User
                    </th>
                    <th class="p-3 border border-green-500 sticky left-48 bg-green-600 z-40 w-40 min-w-40  relative">
                        <div class="absolute inset-0 "></div>
                        Course
                    </th>
                    
                    <!-- Requirement Columns -->
                    @foreach($overviewData['requirements'] as $requirement)
                        <th class="p-3 border border-green-500 text-center w-40 min-w-40 max-w-60" title="{{ $requirement->name }}">
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
                                
                                <!-- User Column -->
                                @if($courseIndex === 0)
                                    <td class="p-3 border border-gray-200 sticky left-0 bg-white z-30 align-top relative"
                                        rowspan="{{ $rowspan }}">
                                        <div class="absolute inset-0 "></div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </td>
                                @endif
                                
                                <!-- Course Column -->
                                <td class="p-3 border border-gray-200 sticky left-48 bg-white z-30 text-sm text-gray-600 relative">
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
                            
                            <!-- User Column -->
                            <td class="p-3 border border-gray-200 sticky left-0 bg-white z-30 relative">
                                <div class="absolute inset-0 border-l-2 border-r-2 border-gray-300"></div>
                                <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </td>
                            
                            <!-- Course Column -->
                            <td class="p-3 border border-gray-200 sticky left-48 bg-white z-30 text-sm text-gray-600 relative">
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
                @endforelse
            </tbody>
        </table>

        <!-- Empty state overlay -->
        @if($overviewData['users']->isEmpty())
        <div class="absolute inset-0 flex flex-col items-center justify-center bg-white py-8 text-gray-500 mt-15">
            <i class="fa-solid fa-users text-3xl text-gray-300 mb-2"></i>
            <p class="text-sm font-semibold">No users found.</p>
            @if($search)
                <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
            @endif
        </div>
        @endif
    </div>

    <!-- Summary
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
    </div> -->
</div>