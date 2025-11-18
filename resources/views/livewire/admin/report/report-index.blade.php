<div>
    <div>
        <!-- Tab Content -->
        <div class="bg-white rounded-xl p-6">
            <!-- Overview Tab -->
            <div class="space-y-6">
                <!-- Header with Title and Generate Button -->
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Overview Report</h2>
                    <button wire:click="generateReport" 
                            target="_blank"
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

                        <!-- Program Filter -->
                        <div>
                            <label for="selectedProgram" class="block text-sm font-medium text-gray-700 mb-1">
                                Program
                            </label>
                            <select wire:model.live="selectedProgram" id="selectedProgram" 
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->program_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Search Box for Overview Tab -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search Faculty
                            </label>
                            <input 
                                type="text" 
                                wire:model.live="search"
                                id="search"
                                placeholder="Search by name, email, rank, program, or course..."
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Overview Table - Excel-like View -->
                <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 relative" style="min-height: 200px;">
                    <table class="min-w-full border-collapse">
                        <!-- Table Header -->
                        <thead class="bg-green-50 text-gray-700 font-semibold text-sm sticky top-0 z-30">
                            <tr>
                                <th class="p-3 rounded-tl-xl border border-green-500 sticky left-0 bg-green-50 z-40 w-49 min-w-49 relative">
                                    <div class="absolute inset-0"></div>
                                    Faculty
                                </th>
                                <th class="p-3 border border-green-500 sticky left-48 bg-green-50 z-40 w-40 min-w-40 relative">
                                    <div class="absolute inset-0"></div>
                                    Program
                                </th>
                                <th class="p-3 border border-green-500 sticky left-88 bg-green-50 z-40 w-40 min-w-40 relative">
                                    <div class="absolute inset-0"></div>
                                    Course
                                </th>
                                
                                <!-- Requirement Columns -->
                                @if($overviewData['requirements']->count() > 0)
                                    @foreach($overviewData['requirements'] as $requirement)
                                        <th class="p-3 border border-green-500 text-center w-40 min-w-40 max-w-60 @if($loop->last) rounded-tr-xl @endif" title="{{ $requirement->name }}">
                                            <div class="truncate">
                                                {{ \Illuminate\Support\Str::limit($requirement->name, 20) }}
                                            </div>
                                        </th>
                                    @endforeach
                                @else
                                    <!-- Single column when no requirements exist -->
                                    <th class="p-3 border border-green-500 text-center w-40 min-w-40 max-w-60 rounded-tr-xl">
                                        <div class="truncate text-gray-500">
                                            No Requirements
                                        </div>
                                    </th>
                                @endif
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
                                                    @if($user->position)
                                                        <div class="text-xs text-gray-600 font-medium">{{ $user->position }}</div>
                                                    @endif
                                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                                </td>
                                            @endif
                                            
                                            <!-- Program Column -->
                                            <td class="p-3 border border-gray-200 sticky left-48 bg-white z-30 text-sm text-gray-600 relative">
                                                <div class="absolute inset-0 border-l-1 border-r-1 border-gray-200"></div>
                                                <div class="font-medium">{{ $course->program->program_code ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-500 truncate" title="{{ $course->program->program_name ?? 'N/A' }}">
                                                    {{ \Illuminate\Support\Str::limit($course->program->program_name ?? 'N/A', 25) }}
                                                </div>
                                            </td>
                                            
                                            <!-- Course Column -->
                                            <td class="p-3 border border-gray-200 sticky left-88 bg-white z-30 text-sm text-gray-600 relative">
                                                <div class="absolute inset-0 border-l-1 border-r-1 border-gray-200"></div>
                                                <div class="font-medium">{{ $course->course_code }}</div>
                                                <div class="text-xs text-gray-500 truncate" title="{{ $course->course_name }}">
                                                    {{ \Illuminate\Support\Str::limit($course->course_name, 25) }}
                                                </div>
                                            </td>
                                            
                                            <!-- Requirement Status Columns -->
                                            @if($overviewData['requirements']->count() > 0)
                                                @foreach($overviewData['requirements'] as $requirement)
                                                    @php
                                                        $displayText = $this->getSubmissionDisplay(
                                                            $user->id, 
                                                            $requirement->id, 
                                                            $course->id, 
                                                            $overviewData['submissionIndicators'],
                                                            $requirement,
                                                            $overviewData['userCoursesData']
                                                        );
                                                        $badgeClass = $this->getStatusBadgeClass($displayText);
                                                    @endphp
                                                    <td class="p-3 border border-gray-200 text-center">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                            {{ $displayText }}
                                                        </span>
                                                    </td>
                                                @endforeach
                                            @else
                                                <!-- Single "No Requirement" column when no requirements exist -->
                                                <td class="p-3 border border-gray-200 text-center"></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <!-- User with no courses -->
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        
                                        <!-- User Column -->
                                        <td class="p-3 border border-gray-200 sticky left-0 bg-white z-30 relative">
                                            <div class="absolute inset-0 border-l-2 border-r-2 border-gray-300"></div>
                                            <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                            @if($user->position)
                                                <div class="text-xs text-gray-600 font-medium">{{ $user->position }}</div>
                                            @endif
                                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                        </td>
                                        
                                        <!-- Program Column -->
                                        <td class="p-3 border border-gray-200 sticky left-48 bg-white z-30 text-sm text-gray-600 relative">
                                            <div class="absolute inset-0 border-l-2 border-r-2 border-gray-300"></div>
                                            <span class="text-gray-400">No program</span>
                                        </td>
                                        
                                        <!-- Course Column -->
                                        <td class="p-3 border border-gray-200 sticky left-88 bg-white z-30 text-sm text-gray-600 relative">
                                            <div class="absolute inset-0 border-l-2 border-r-2 border-gray-300"></div>
                                            <span class="text-gray-400">No course</span>
                                        </td>
                                        
                                        <!-- Requirement Status Columns -->
                                        @if($overviewData['requirements']->count() > 0)
                                            @foreach($overviewData['requirements'] as $requirement)
                                                @php
                                                    $displayText = 'N/A'; // Users with no courses are automatically not assigned
                                                    $badgeClass = $this->getStatusBadgeClass($displayText);
                                                @endphp
                                                <td class="p-3 border border-gray-200 text-center">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                        {{ $displayText }}
                                                    </span>
                                                </td>
                                            @endforeach
                                        @else
                                            <!-- Single "No Requirement" column when no requirements exist -->
                                            <td class="p-3 border border-gray-200 text-center">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                    No Requirement
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endif
                            @empty
                                <!-- Empty users state -->
                                <tr>
                                    <td colspan="{{ $overviewData['requirements']->count() > 0 ? $overviewData['requirements']->count() + 3 : 4 }}" class="p-8 text-center text-gray-500">
                                        <i class="fa-solid fa-users text-3xl text-gray-300 mb-2"></i>
                                        <p class="text-sm font-semibold">No users found.</p>
                                        @if($search)
                                            <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search filters</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Empty state overlay - Only show when there are no users at all -->
                    @if($overviewData['users']->isEmpty())
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-white py-8 text-gray-500 mt-15">
                        <i class="fa-solid fa-users text-3xl text-gray-300 mb-2"></i>
                        <p class="text-sm font-semibold">No users found.</p>
                        @if($search)
                            <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search filters</p>
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

                <!-- Summary -->
                <div class="mt-4 flex flex-wrap gap-4 items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                    @php
                        $totalCourses = 0;
                        foreach($overviewData['userCoursesData'] as $courses) {
                            $totalCourses += $courses->count();
                        }
                    @endphp
                    <div class="text-xs font-semibold text-gray-700">
                        Total Faculty: {{ $overviewData['users']->total() }} | 
                        Total Courses: {{ $totalCourses }} |
                        Total Requirements: {{ $overviewData['requirements']->count() }} |
                        Semester: {{ $overviewData['semester']->name ?? 'N/A' }}
                        @if($overviewData['requirements']->isEmpty())
                            | <span class="text-amber-600">No requirements defined for this semester</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Message -->
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

    <script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('open-new-tab', (event) => {
            window.open(event.url, '_blank');
        });
    });
    </script>
</div>