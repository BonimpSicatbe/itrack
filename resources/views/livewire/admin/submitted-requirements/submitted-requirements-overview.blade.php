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
                                        <div class="text-sm font-medium text-gray-900">{{ $user->getFullNameAttribute() }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->position}}</div>
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
                                        $isSubmitted = $displayText === 'Submitted';
                                        $submissionUrl = $isSubmitted ? 
                                            $this->getSubmissionUrl($requirement->id, $user->id, $course->id) : '#';
                                    @endphp
                                    <td class="p-3 border border-gray-200 text-center">
                                        @if($isSubmitted)
                                            <a href="{{ $submissionUrl }}" 
                                               class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }} hover:bg-green-200 hover:text-green-900 transition-colors cursor-pointer"
                                               title="View submission details">
                                                {{ $displayText }}
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                {{ $displayText }}
                                            </span>
                                        @endif
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

    <!-- Pagination Controls -->
    @if($overviewData['users']->hasPages())
    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
        
        <div class="text-sm text-gray-700">
            Showing {{ $overviewData['users']->firstItem() ?? 0 }} to {{ $overviewData['users']->lastItem() ?? 0 }} 
            of {{ $overviewData['users']->total() }} results
        </div>
        
        <div class="flex gap-1">
            <!-- Previous Page -->
            @if($overviewData['users']->onFirstPage())
            <span class="px-3 py-1 bg-gray-200 text-gray-500 rounded-md text-sm cursor-not-allowed">
                Previous
            </span>
            @else
            <button wire:click="previousPage" class="px-3 py-1 bg-white border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50 transition-colors">
                Previous
            </button>
            @endif

            <!-- Page Numbers -->
            @foreach($overviewData['users']->getUrlRange(1, $overviewData['users']->lastPage()) as $page => $url)
                @if($page == $overviewData['users']->currentPage())
                <span class="px-3 py-1 bg-green-600 text-white rounded-md text-sm font-medium">
                    {{ $page }}
                </span>
                @else
                <button wire:click="gotoPage({{ $page }})" class="px-3 py-1 bg-white border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50 transition-colors">
                    {{ $page }}
                </button>
                @endif
            @endforeach

            <!-- Next Page -->
            @if($overviewData['users']->hasMorePages())
            <button wire:click="nextPage" class="px-3 py-1 bg-white border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50 transition-colors">
                Next
            </button>
            @else
            <span class="px-3 py-1 bg-gray-200 text-gray-500 rounded-md text-sm cursor-not-allowed">
                Next
            </span>
            @endif
        </div>
    </div>
    @else
    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200 text-sm text-gray-700">
        Showing {{ $overviewData['users']->count() }} results
    </div>
    @endif

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