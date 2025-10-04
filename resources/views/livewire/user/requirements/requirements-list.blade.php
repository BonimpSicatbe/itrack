<div class="flex flex-col w-full mx-auto min-h-screen text-sm overflow-hidden">
    <!-- Header Container (Fixed) -->
    <div class="mb-4">
        <div class="flex items-center justify-between px-6 py-6 border-b border-gray-200 rounded-xl"
            style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">

            <!-- Left: Title -->
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-white text-2xl"></i>
                <h1 class="text-xl font-bold text-white">
                    @if($selectedFolder)
                        {{ $selectedFolder->name }}
                    @elseif($selectedCourse)
                        Course Requirements
                    @else
                        My Courses
                    @endif
                </h1>
            </div>

            <!-- Back button when viewing course requirements or folder -->
            @if($selectedFolder || $selectedCourse)
                <button wire:click="backToCourses" 
                        class="btn btn-sm btn-outline btn-light text-white border-white hover:bg-white hover:text-green-700">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    @if($selectedFolder)
                        Back to Requirements
                    @else
                        Back to Courses
                    @endif
                </button>
            @endif
        </div>
    </div>

    <!-- Content Area -->
    <div class="w-full bg-white rounded-xl p-6 space-y-4 grow overflow-y-auto"
        style="max-height: calc(100vh - 125px);">

        <!-- Breadcrumb -->
        <div class="mb-4 px-6">
            <div class="text-sm breadcrumbs">
                <ul>
                    <li>
                        <button wire:click="backToCourses" class="text-green-600 hover:text-green-800">
                            <i class="fa-solid fa-graduation-cap mr-2"></i>
                            My Courses
                        </button>
                    </li>
                    @if($selectedCourse)
                        <li>
                            @php
                                $course = $assignedCourses->firstWhere('id', $selectedCourse);
                            @endphp
                            @if($course)
                                @if($selectedFolder)
                                    <!-- When in folder view, make course clickable to go back to course requirements -->
                                    <button wire:click="backToCourseRequirements" class="text-green-600 hover:text-green-800 font-medium">
                                        {{ $course->course_code }}
                                    </button>
                                @else
                                    <!-- When in course requirements view, show course as static -->
                                    <span class="text-green-700 font-medium">{{ $course->course_code }}</span>
                                @endif
                            @else
                                <span class="text-green-700 font-medium">Course</span>
                            @endif
                        </li>
                    @endif
                    @if($selectedFolder)
                        <li>
                            <span class="text-green-700 font-medium">{{ $selectedFolder->name }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        @if($activeSemester)
            <!-- Course Grid View -->
            @if(!$selectedCourse && !$selectedFolder)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse($assignedCourses as $course)
                        <div class="bg-white border-2 border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 hover:border-green-500 cursor-pointer"
                             wire:click="selectCourse({{ $course->id }})">
                            <div class="p-6 text-center">
                                <div class="flex justify-center mb-4">
                                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fa-solid fa-folder text-green-600 text-2xl"></i>
                                    </div>
                                </div>
                                <h3 class="font-bold text-gray-800 text-lg mb-2">{{ $course->course_code }}</h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $course->course_name }}</p>
                                
                                @php
                                    $user = auth()->user();
                                    $requirementsCount = \App\Models\Requirement::where('semester_id', $activeSemester->id)
                                        ->get()
                                        ->filter(function($requirement) use ($user) {
                                            $assignedTo = $requirement->assigned_to ?? [];
                                            $colleges = $assignedTo['colleges'] ?? [];
                                            $departments = $assignedTo['departments'] ?? [];
                                            $selectAllColleges = $assignedTo['selectAllColleges'] ?? false;
                                            $selectAllDepartments = $assignedTo['selectAllDepartments'] ?? false;

                                            if (!$user->college_id || !$user->department_id) {
                                                return false;
                                            }

                                            $userCollegeId = (string)$user->college_id;
                                            $userDepartmentId = (string)$user->department_id;

                                            $collegeAssigned = $selectAllColleges || 
                                                              (is_array($colleges) && in_array($userCollegeId, $colleges));
                                            $departmentAssigned = $selectAllDepartments ||
                                                                (is_array($departments) && in_array($userDepartmentId, $departments));

                                            return $collegeAssigned && $departmentAssigned;
                                        })
                                        ->count();
                                @endphp
                                
                                <div class="text-xs text-gray-500 bg-gray-100 rounded-lg py-2 px-3">
                                    <i class="fa-solid fa-list-check mr-1"></i>
                                    {{ $requirementsCount }} requirement(s)
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-500">
                            <i class="fa-solid fa-folder-open text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-semibold">No courses assigned</p>
                            <p class="text-sm mt-2">You don't have any courses assigned for the current semester.</p>
                        </div>
                    @endforelse
                </div>

            <!-- Folder View -->
            @elseif($selectedFolder)
                <!-- Folder Requirements -->
                <div class="space-y-4">
                    @forelse($folderRequirements as $requirement)
                        <div class="collapse collapse-arrow bg-base-100 border-2 border-gray-300 shadow-sm hover:border-green-500">
                            <input type="checkbox" name="requirements-list-item" class="h-full" />
                            
                            {{-- Title / Collapse Button --}}
                            <div class="collapse-title">
                                <div class="flex flex-row items-center gap-8">
                                    <div class="text-sm font-bold">
                                        <i class="fa-solid fa-clipboard-list min-w-[20px] text-center text-green-500"></i>
                                        {{ $requirement->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 grow">
                                        {{ $requirement->due->format('M j, Y h:i A') }}
                                        @if($requirement->isOverdue() && !$requirement->user_has_submitted)
                                            <span class="badge badge-error text-white ml-2">Overdue</span>
                                        @endif
                                    </div>
                                    @if ($requirement->user_has_submitted)
                                        <button wire:click="toggleMarkAsDone({{ $requirement->id }})"
                                            wire:target="toggleMarkAsDone({{ $requirement->id }})" type="button"
                                            class="btn btn-sm btn-outline z-1 rounded-full {{ $requirement->user_marked_done ? 'btn-warning' : 'btn-success' }}">
                                            <i class="fa-solid fa-check-double min-w-[20px] text-center"></i>
                                            {{ $requirement->user_marked_done ? 'Mark as undone' : 'Mark as done' }}
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline btn-disabled bg-white rounded-full">
                                            <i class="fa-solid fa-check-double"></i>
                                            Mark as Done
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Collapse Content --}}
                            <div class="collapse-content text-sm">
                                <div class="tabs tabs-lift">
                                    {{-- Requirement Details --}}
                                    <input type="radio" name="{{ $requirement->id }}" class="tab focus:ring-0 focus:outline-0"
                                        aria-label="Requirement Details" checked="checked" />
                                    <div class="tab-content space-y-4 border-0 border-t border-base-300 pt-4 rounded-none">
                                        <div class="text-sm font-bold">{{ $requirement->name }}</div>
                                        @if($requirement->description)
                                            <div class="text-sm">{{ $requirement->description }}</div>
                                        @endif
                                        <div class="text-sm">
                                            <span class="font-bold">Due Date: </span>
                                            {{ $requirement->due->format('M j, Y') }}
                                            ({{ $requirement->due->diffForHumans() }})
                                        </div>
                                        
                                        @if($requirement->user_has_submitted)
                                            <div class="flex items-center gap-1 text-green-600">
                                                <i class="fa-solid fa-check-circle"></i>
                                                <span>Submitted</span>
                                            </div>
                                        @endif

                                        @if($requirement->user_marked_done)
                                            <div class="flex items-center gap-1 text-blue-600">
                                                <i class="fa-solid fa-flag-checkered"></i>
                                                <span>Completed</span>
                                            </div>
                                        @endif

                                        @if($requirement->guides->count() > 0)
                                            <div class="divider p-0 m-0"></div>
                                            <h4 class="font-semibold">Guide Files</h4>
                                            <div class="space-y-2">
                                                @foreach($requirement->guides as $guide)
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            @php
                                                                $extension = strtolower(pathinfo($guide->file_name, PATHINFO_EXTENSION));
                                                                $iconInfo = \App\Models\SubmittedRequirement::FILE_ICONS[$extension] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                                                            @endphp
                                                            <i class="fa-solid {{ $iconInfo['icon'] }} {{ $iconInfo['color'] }}"></i>
                                                            <span class="truncate max-w-xs text-xs font-semibold">{{ $guide->file_name }}</span>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <a href="{{ route('guide.download', ['media' => $guide->id]) }}"
                                                                class="text-blue-500 hover:text-blue-700 inline-flex items-center"
                                                                title="Download">
                                                                <i class="fa-solid fa-download text-sm"></i>
                                                            </a>
                                                            @if ($this->isPreviewable($guide->mime_type))
                                                                <a href="{{ route('guide.preview', ['media' => $guide->id]) }}"
                                                                    target="_blank"
                                                                    class="text-green-500 hover:text-green-700 inline-flex items-center"
                                                                    title="View">
                                                                    <i class="fa-solid fa-eye text-sm"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Submit Requirement --}}
                                    <input type="radio" name="{{ $requirement->id }}"
                                        class="tab focus:ring-0 focus:outline-0" aria-label="Submit Requirement"
                                        {{ $this->isTabActive($requirement->id, 'submit') ? 'checked' : '' }} />
                                    <div class="tab-content space-y-4 border-0 border-t border-base-300 pt-4 rounded-none">
                                        <div class="mb-6">
                                            @if ($requirement->user_marked_done)
                                                <div class="alert bg-amber-300 border-amber-300">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-solid fa-circle-info"></i>
                                                        <span>Requirement completed. Click "<b>Done</b>" above to
                                                            undone the requirement and submit additional
                                                            files.</span>
                                                    </div>
                                                </div>
                                            @else
                                                <form wire:submit.prevent="submitRequirement({{ $requirement->id }})"
                                                    class="space-y-4">
                                                    <div>
                                                        <input type="file" wire:model="file"
                                                            class="file-input file-input-bordered w-full"
                                                            wire:loading.attr="disabled">
                                                        @error('file')
                                                            <span class="text-error text-sm">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <!-- Display selected file name -->
                                                    @if ($file)
                                                        <div class="p-3 bg-green-50 rounded-lg border border-gray-400">
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex items-center gap-2">
                                                                    @php
                                                                        $extension = strtolower(
                                                                            $file->getClientOriginalExtension(),
                                                                        );
                                                                        $iconInfo = \App\Models\SubmittedRequirement::FILE_ICONS[$extension] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                                                                    @endphp
                                                                    <i class="fa-solid {{ $iconInfo['icon'] }} {{ $iconInfo['color'] }}"></i>
                                                                    <span class="text-sm font-medium truncate max-w-xs">
                                                                        {{ $file->getClientOriginalName() }}
                                                                    </span>
                                                                </div>
                                                                <button type="button" class="btn btn-xs btn-ghost text-error"
                                                                    wire:click="$set('file', null)" title="Remove file">
                                                                    <i class="fa-solid fa-times"></i>
                                                                </button>
                                                            </div>
                                                            <div class="mt-1 text-xs text-gray-500">
                                                                Size: {{ round($file->getSize() / 1024, 1) }} KB
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <button type="submit"
                                                        class="btn w-full bg-green-600 text-white hover:bg-green-700"
                                                        wire:loading.attr="disabled" :disabled="!$file">
                                                        <span wire:loading.remove>Submit Requirement</span>
                                                        <span wire:loading>
                                                            <i class="fa-solid fa-spinner animate-spin"></i>
                                                            Uploading...
                                                        </span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Previous Submissions --}}
                                    <input type="radio" name="{{ $requirement->id }}"
                                        class="tab focus:ring-0 focus:outline-0" aria-label="Previous Submissions"
                                        {{ $this->isTabActive($requirement->id, 'submissions') ? 'checked' : '' }} />
                                    <div class="tab-content space-y-4 border-0 border-t border-base-300 pt-4 rounded-none">
                                        <div>
                                            @if ($requirement->userSubmissions->count() > 0)
                                                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                                                    <table class="table w-full">
                                                        <thead>
                                                            <tr>
                                                                <th>File</th>
                                                                <th>Submitted At</th>
                                                                <th class="text-center">Status</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($requirement->userSubmissions as $submission)
                                                                <tr class="text-xs">
                                                                    <td>
                                                                        <div class="flex items-center gap-2">
                                                                            @if ($submission->submissionFile)
                                                                                @php
                                                                                    $extension = strtolower(
                                                                                        pathinfo(
                                                                                            $submission->submissionFile->file_name,
                                                                                            PATHINFO_EXTENSION,
                                                                                        ),
                                                                                    );
                                                                                    $iconInfo = \App\Models\SubmittedRequirement::FILE_ICONS[$extension] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                                                                                @endphp
                                                                                <i class="fa-solid {{ $iconInfo['icon'] }} {{ $iconInfo['color'] }}"></i>
                                                                            @else
                                                                                <i class="fa-regular fa-file text-gray-400"></i>
                                                                            @endif
                                                                            <span class="truncate max-w-xs">
                                                                                {{ $submission->submissionFile->file_name ?? 'No file' }}
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td>{{ $submission->submitted_at->format('M j, Y h:i A') }}
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @php
                                                                            $statusColor = \App\Models\SubmittedRequirement::getStatusColor($submission->status);
                                                                            $statusParts = explode(' ', $statusColor);
                                                                            $bgColor = $statusParts[0];
                                                                            $textColor = $statusParts[1] ?? '';
                                                                        @endphp
                                                                        <span class="badge {{ $bgColor }} {{ $textColor }} text-xs rounded-full font-semibold">
                                                                            {{ $submission->status_text }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="flex gap-2 text-center justify-center gap-3">
                                                                            @if ($submission->submissionFile)
                                                                                <a href="{{ route('file.download', ['submission' => $submission->id]) }}"
                                                                                    class="text-sm text-blue-500"
                                                                                    title="Download">
                                                                                    <i class="fa-solid fa-download"></i>
                                                                                </a>
                                                                                @php
                                                                                    $extension = strtolower(
                                                                                        pathinfo(
                                                                                            $submission->submissionFile->file_name,
                                                                                            PATHINFO_EXTENSION,
                                                                                        ),
                                                                                    );
                                                                                    $isPreviewable = in_array($extension, [
                                                                                        'jpg',
                                                                                        'jpeg',
                                                                                        'png',
                                                                                        'gif',
                                                                                        'pdf',
                                                                                    ]);
                                                                                @endphp
                                                                                @if ($isPreviewable)
                                                                                    <a href="{{ route('file.preview', ['submission' => $submission->id]) }}"
                                                                                        target="_blank"
                                                                                        class="text-sm text-green-500"
                                                                                        title="View">
                                                                                        <i class="fa-solid fa-eye"></i>
                                                                                    </a>
                                                                                @endif
                                                                            @endif
                                                                            @if ($submission->status === 'under_review' || $submission->status === 'rejected' || $submission->status === 'revision_needed')
                                                                                <button wire:click="confirmDelete({{ $submission->id }})"
                                                                                    class="text-sm text-red-500"
                                                                                    title="Delete submission">
                                                                                    <i class="fa-solid fa-trash"></i>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-8 text-gray-500">
                                                    <i class="fa-solid fa-folder-open text-gray-300 text-4xl mb-2"></i>
                                                    <p class="text-sm font-semibold">No submissions yet</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-500">
                            <i class="fa-solid fa-folder-open text-4xl mb-2 text-gray-300"></i>
                            <p class="text-lg font-semibold">No requirements in this folder</p>
                        </div>
                    @endforelse
                </div>

            <!-- Course Requirements View -->
            @else
                @php
                    $course = $assignedCourses->firstWhere('id', $selectedCourse);
                @endphp
                
                @if($course)
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                        <h2 class="text-xl font-bold text-green-800">{{ $course->course_code }}</h2>
                        <p class="text-green-700">{{ $course->course_name }}</p>
                        @if($course->description)
                            <p class="text-green-600 text-sm mt-2">{{ $course->description }}</p>
                        @endif
                    </div>
                @endif

                <!-- Display Folders as Clickable Cards -->
                @if(count($organizedRequirements['folders']) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                        @foreach($organizedRequirements['folders'] as $folderData)
                            <div class="bg-white border-2 border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 hover:border-green-500 cursor-pointer"
                                 wire:click="selectFolder({{ $folderData['folder']->id }})">
                                <div class="p-6 text-center">
                                    <div class="flex justify-center mb-4">
                                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <i class="fa-solid fa-folder text-yellow-500 text-2xl"></i>
                                        </div>
                                    </div>
                                    <h3 class="font-bold text-gray-800 text-lg mb-2">{{ $folderData['folder']->name }}</h3>
                                    <div class="text-xs text-gray-500 bg-gray-100 rounded-lg py-2 px-3">
                                        <i class="fa-solid fa-list-check mr-1"></i>
                                        {{ count($folderData['requirements']) }} requirement(s)
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Display Standalone Requirements (without folders) -->
                <div class="space-y-4">
                    @foreach($organizedRequirements['standalone'] as $requirement)
                        <div class="collapse collapse-arrow bg-base-100 border-2 border-gray-300 shadow-sm hover:border-green-500">
                            <input type="checkbox" name="requirements-list-item" class="h-full" />
                            
                            {{-- Title / Collapse Button --}}
                            <div class="collapse-title">
                                <div class="flex flex-row items-center gap-8">
                                    <div class="text-sm font-bold">
                                        <i class="fa-solid fa-clipboard-list min-w-[20px] text-center text-green-500"></i>
                                        {{ $requirement->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 grow">
                                        {{ $requirement->due->format('M j, Y h:i A') }}
                                        @if($requirement->isOverdue() && !$requirement->user_has_submitted)
                                            <span class="badge badge-error text-white ml-2">Overdue</span>
                                        @endif
                                    </div>
                                    @if ($requirement->user_has_submitted)
                                        <button wire:click="toggleMarkAsDone({{ $requirement->id }})"
                                            wire:target="toggleMarkAsDone({{ $requirement->id }})" type="button"
                                            class="btn btn-sm btn-outline z-1 rounded-full {{ $requirement->user_marked_done ? 'btn-warning' : 'btn-success' }}">
                                            <i class="fa-solid fa-check-double min-w-[20px] text-center"></i>
                                            {{ $requirement->user_marked_done ? 'Mark as undone' : 'Mark as done' }}
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline btn-disabled bg-white rounded-full">
                                            <i class="fa-solid fa-check-double"></i>
                                            Mark as Done
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Collapse Content --}}
                            <div class="collapse-content text-sm">
                                <!-- Same requirement content structure as above -->
                                <div class="tabs tabs-lift">
                                    {{-- Requirement Details --}}
                                    <input type="radio" name="{{ $requirement->id }}" class="tab focus:ring-0 focus:outline-0"
                                        aria-label="Requirement Details" checked="checked" />
                                    <div class="tab-content space-y-4 border-0 border-t border-base-300 pt-4 rounded-none">
                                        <div class="text-sm font-bold">{{ $requirement->name }}</div>
                                        @if($requirement->description)
                                            <div class="text-sm">{{ $requirement->description }}</div>
                                        @endif
                                        <div class="text-sm">
                                            <span class="font-bold">Due Date: </span>
                                            {{ $requirement->due->format('M j, Y') }}
                                            ({{ $requirement->due->diffForHumans() }})
                                        </div>
                                        
                                        @if($requirement->user_has_submitted)
                                            <div class="flex items-center gap-1 text-green-600">
                                                <i class="fa-solid fa-check-circle"></i>
                                                <span>Submitted</span>
                                            </div>
                                        @endif

                                        @if($requirement->user_marked_done)
                                            <div class="flex items-center gap-1 text-blue-600">
                                                <i class="fa-solid fa-flag-checkered"></i>
                                                <span>Completed</span>
                                            </div>
                                        @endif

                                        @if($requirement->guides->count() > 0)
                                            <div class="divider p-0 m-0"></div>
                                            <h4 class="font-semibold">Guide Files</h4>
                                            <div class="space-y-2">
                                                @foreach($requirement->guides as $guide)
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            @php
                                                                $extension = strtolower(pathinfo($guide->file_name, PATHINFO_EXTENSION));
                                                                $iconInfo = \App\Models\SubmittedRequirement::FILE_ICONS[$extension] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                                                            @endphp
                                                            <i class="fa-solid {{ $iconInfo['icon'] }} {{ $iconInfo['color'] }}"></i>
                                                            <span class="truncate max-w-xs text-xs font-semibold">{{ $guide->file_name }}</span>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <a href="{{ route('guide.download', ['media' => $guide->id]) }}"
                                                                class="text-blue-500 hover:text-blue-700 inline-flex items-center"
                                                                title="Download">
                                                                <i class="fa-solid fa-download text-sm"></i>
                                                            </a>
                                                            @if ($this->isPreviewable($guide->mime_type))
                                                                <a href="{{ route('guide.preview', ['media' => $guide->id]) }}"
                                                                    target="_blank"
                                                                    class="text-green-500 hover:text-green-700 inline-flex items-center"
                                                                    title="View">
                                                                    <i class="fa-solid fa-eye text-sm"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Submit Requirement --}}
                                    <input type="radio" name="{{ $requirement->id }}"
                                        class="tab focus:ring-0 focus:outline-0" aria-label="Submit Requirement"
                                        {{ $this->isTabActive($requirement->id, 'submit') ? 'checked' : '' }} />
                                    <div class="tab-content space-y-4 border-0 border-t border-base-300 pt-4 rounded-none">
                                        <div class="mb-6">
                                            @if ($requirement->user_marked_done)
                                                <div class="alert bg-amber-300 border-amber-300">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-solid fa-circle-info"></i>
                                                        <span>Requirement completed. Click "<b>Done</b>" above to
                                                            undone the requirement and submit additional
                                                            files.</span>
                                                    </div>
                                                </div>
                                            @else
                                                <form wire:submit.prevent="submitRequirement({{ $requirement->id }})"
                                                    class="space-y-4">
                                                    <div>
                                                        <input type="file" wire:model="file"
                                                            class="file-input file-input-bordered w-full"
                                                            wire:loading.attr="disabled">
                                                        @error('file')
                                                            <span class="text-error text-sm">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <!-- Display selected file name -->
                                                    @if ($file)
                                                        <div class="p-3 bg-green-50 rounded-lg border border-gray-400">
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex items-center gap-2">
                                                                    @php
                                                                        $extension = strtolower(
                                                                            $file->getClientOriginalExtension(),
                                                                        );
                                                                        $iconInfo = \App\Models\SubmittedRequirement::FILE_ICONS[$extension] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                                                                    @endphp
                                                                    <i class="fa-solid {{ $iconInfo['icon'] }} {{ $iconInfo['color'] }}"></i>
                                                                    <span class="text-sm font-medium truncate max-w-xs">
                                                                        {{ $file->getClientOriginalName() }}
                                                                    </span>
                                                                </div>
                                                                <button type="button" class="btn btn-xs btn-ghost text-error"
                                                                    wire:click="$set('file', null)" title="Remove file">
                                                                    <i class="fa-solid fa-times"></i>
                                                                </button>
                                                            </div>
                                                            <div class="mt-1 text-xs text-gray-500">
                                                                Size: {{ round($file->getSize() / 1024, 1) }} KB
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <button type="submit"
                                                        class="btn w-full bg-green-600 text-white hover:bg-green-700"
                                                        wire:loading.attr="disabled" :disabled="!$file">
                                                        <span wire:loading.remove>Submit Requirement</span>
                                                        <span wire:loading>
                                                            <i class="fa-solid fa-spinner animate-spin"></i>
                                                            Uploading...
                                                        </span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Previous Submissions --}}
                                    <input type="radio" name="{{ $requirement->id }}"
                                        class="tab focus:ring-0 focus:outline-0" aria-label="Previous Submissions"
                                        {{ $this->isTabActive($requirement->id, 'submissions') ? 'checked' : '' }} />
                                    <div class="tab-content space-y-4 border-0 border-t border-base-300 pt-4 rounded-none">
                                        <div>
                                            @if ($requirement->userSubmissions->count() > 0)
                                                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                                                    <table class="table w-full">
                                                        <thead>
                                                            <tr>
                                                                <th>File</th>
                                                                <th>Submitted At</th>
                                                                <th class="text-center">Status</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($requirement->userSubmissions as $submission)
                                                                <tr class="text-xs">
                                                                    <td>
                                                                        <div class="flex items-center gap-2">
                                                                            @if ($submission->submissionFile)
                                                                                @php
                                                                                    $extension = strtolower(
                                                                                        pathinfo(
                                                                                            $submission->submissionFile->file_name,
                                                                                            PATHINFO_EXTENSION,
                                                                                        ),
                                                                                    );
                                                                                    $iconInfo = \App\Models\SubmittedRequirement::FILE_ICONS[$extension] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                                                                                @endphp
                                                                                <i class="fa-solid {{ $iconInfo['icon'] }} {{ $iconInfo['color'] }}"></i>
                                                                            @else
                                                                                <i class="fa-regular fa-file text-gray-400"></i>
                                                                            @endif
                                                                            <span class="truncate max-w-xs">
                                                                                {{ $submission->submissionFile->file_name ?? 'No file' }}
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td>{{ $submission->submitted_at->format('M j, Y h:i A') }}
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @php
                                                                            $statusColor = \App\Models\SubmittedRequirement::getStatusColor($submission->status);
                                                                            $statusParts = explode(' ', $statusColor);
                                                                            $bgColor = $statusParts[0];
                                                                            $textColor = $statusParts[1] ?? '';
                                                                        @endphp
                                                                        <span class="badge {{ $bgColor }} {{ $textColor }} text-xs rounded-full font-semibold">
                                                                            {{ $submission->status_text }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="flex gap-2 text-center justify-center gap-3">
                                                                            @if ($submission->submissionFile)
                                                                                <a href="{{ route('file.download', ['submission' => $submission->id]) }}"
                                                                                    class="text-sm text-blue-500"
                                                                                    title="Download">
                                                                                    <i class="fa-solid fa-download"></i>
                                                                                </a>
                                                                                @php
                                                                                    $extension = strtolower(
                                                                                        pathinfo(
                                                                                            $submission->submissionFile->file_name,
                                                                                            PATHINFO_EXTENSION,
                                                                                        ),
                                                                                    );
                                                                                    $isPreviewable = in_array($extension, [
                                                                                        'jpg',
                                                                                        'jpeg',
                                                                                        'png',
                                                                                        'gif',
                                                                                        'pdf',
                                                                                    ]);
                                                                                @endphp
                                                                                @if ($isPreviewable)
                                                                                    <a href="{{ route('file.preview', ['submission' => $submission->id]) }}"
                                                                                        target="_blank"
                                                                                        class="text-sm text-green-500"
                                                                                        title="View">
                                                                                        <i class="fa-solid fa-eye"></i>
                                                                                    </a>
                                                                                @endif
                                                                            @endif
                                                                            @if ($submission->status === 'under_review' || $submission->status === 'rejected' || $submission->status === 'revision_needed')
                                                                                <button wire:click="confirmDelete({{ $submission->id }})"
                                                                                    class="text-sm text-red-500"
                                                                                    title="Delete submission">
                                                                                    <i class="fa-solid fa-trash"></i>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-8 text-gray-500">
                                                    <i class="fa-solid fa-folder-open text-gray-300 text-4xl mb-2"></i>
                                                    <p class="text-sm font-semibold">No submissions yet</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Show message if no requirements at all -->
                @if(count($organizedRequirements['folders']) === 0 && count($organizedRequirements['standalone']) === 0)
                    <div class="text-center py-12 text-gray-500 font-semibold">
                        <i class="fa-solid fa-folder-open text-4xl mb-2 text-gray-300"></i>
                        <p>No requirements found</p>
                    </div>
                @endif
            @endif
        @else
            <!-- No Active Semester -->
            <div class="flex items-center p-4 bg-amber-100 border border-amber-300 text-amber-800 rounded-lg">
                <i class="fa-solid fa-triangle-exclamation text-lg mr-3"></i>
                <div>
                    <h3 class="font-bold">No active semester</h3>
                    <div class="text-sm">Courses and requirements will be available once you have an active semester.</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <x-modal name="delete-submission-confirmation-modal" :show="$showDeleteModal" maxWidth="md">
            <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete this submission?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. The submitted file will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="cancelDelete"
                        class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteSubmission"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteSubmission">
                            <i class="fa-solid fa-trash mr-2"></i> Delete
                        </span>
                        <span wire:loading wire:target="deleteSubmission">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>