<div class="flex flex-col md:flex-row gap-4 h-[calc(100vh-6rem)]">
    <!-- Left Panel (40%) -->
    <div class="w-full md:w-2/5 flex flex-col gap-4 h-full">
        <!-- Requirement Details with Course Submission Files -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition flex-1 flex flex-col overflow-hidden min-h-0">
            <!-- Header with Back Button -->
            <div class="px-6 py-3 flex-shrink-0" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <button wire:click="goBackToIndex" 
                        class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white rounded-lg transition-all duration-200 hover:bg-white/20 hover:shadow-md hover:scale-105 flex-shrink-0">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto min-h-0">
                <!-- Requirement Details Title -->
                <div class="px-6 pt-5 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-green-700 text-xl"></i>
                        <h2 class="text-lg font-semibold text-gray-800">Requirement Details</h2>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="px-6 pb-4 space-y-3">
                <!-- Context Breadcrumb -->
                @if($user && $course)
                <div class="pb-4 border-b border-gray-200">
                    <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-2">Submission Context</h4>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-folder text-green-600 text-sm"></i>
                            <span class="text-sm text-gray-500">{{ $requirement->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user text-blue-600 text-sm"></i>
                            <span class="text-sm text-gray-500">{{ $user->full_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-book text-purple-600 text-sm"></i>
                            <span class="text-sm text-gray-500">{{ $course->course_code }} - {{ $course->course_name }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <div>
                    <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Requirement Name</h4>
                    <p class="text-sm text-gray-500">{{ $requirement->name }}</p>
                </div>

                @if($requirement->description)
                <div>
                    <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Description</h4>
                    <p class="text-sm text-gray-500 whitespace-pre-line">{{ $requirement->description }}</p>
                </div>
                @endif

                <div>
                    <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Due Date</h4>
                    <p class="text-sm text-gray-500">{{ $requirement->due->format('M j, Y \a\t g:i A') }}</p>
                </div>

                <div>
                    <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Created By</h4>
                    <p class="text-sm text-gray-500">{{ $requirement->creator->full_name }}</p>
                </div>
                
                <div>
                    <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-1">Files Submitted</h4>
                    <p class="text-sm text-gray-500">{{ count($allFiles) }} file(s)</p>
                </div>
            </div>

            <!-- Course Submission Files Section -->
            <div class="border-t border-gray-200 flex-1 flex flex-col overflow-hidden">
                <!-- Files Header -->
                <div class="text-gray-800 px-6 py-3 flex items-center justify-between bg-gray-50">
                    <div class="flex items-center">
                        <i class="fa-solid fa-files mr-2 text-lg"></i>
                        <h3 class="text-base font-semibold">
                            @if($user && $course)
                                Course Submission Files
                            @else
                                No Files Available
                            @endif
                        </h3>
                    </div>
                    @if($user && $course)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                        {{ count($allFiles) }} file(s)
                    </span>
                    @endif
                </div>

                <!-- Files Body -->
                <div class="p-6 flex-1 overflow-y-auto">
                    @if($user && $course)
                        @if(count($allFiles) > 0)
                            <div class="space-y-2">
                                @foreach($allFiles as $file)
                                    @php
                                        $fileIcon = \App\Models\SubmittedRequirement::FILE_ICONS[$file['extension']] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                                        $submissionModel = App\Models\SubmittedRequirement::find($file['submission_id']);
                                        $statusBadgeClasses = $submissionModel ? $submissionModel->status_badge : 'bg-blue-100 text-blue-800';
                                    @endphp
                                    <button wire:click="selectFile('{{ $file['id'] }}')"
                                        class="w-full text-left p-2.5 rounded-lg transition-all duration-200 flex items-center gap-2.5
                                            {{ ($selectedFile && $selectedFile['id'] === $file['id']) ? 
                                                'bg-green-600 text-white shadow-sm' : 
                                                'border-1 border-gray-300 hover:border-green-700 hover:border-2' }}">
                                        <!-- File Icon -->
                                        <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center bg-white">
                                            <i class="fa-solid {{ $fileIcon['icon'] }} text-base {{ $fileIcon['color'] }}"></i>
                                        </div>
                                        <!-- File Info -->
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium truncate text-sm {{ ($selectedFile && $selectedFile['id'] === $file['id']) ? 'text-white' : 'text-gray-900' }}">
                                                {{ $file['file_name'] }}
                                            </p>
                                            <div class="flex items-center gap-2 text-xs mt-0.5 {{ ($selectedFile && $selectedFile['id'] === $file['id']) ? 'text-gray-200' : 'text-gray-500' }}">
                                                <i class="fa-solid fa-calendar"></i>
                                                {{ $file['created_at']->format('M j, Y') }}
                                                <i class="fa-solid fa-file"></i>
                                                {{ $file['size'] }}
                                            </div>
                                        </div>
                                        <!-- Status -->
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusBadgeClasses }}">
                                                {{ $this->formatStatus($file['status']) }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="mx-auto w-16 h-16 rounded-full bg-green-700/10 flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-file-arrow-up text-2xl text-green-700"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">
                                    No files submitted
                                </h3>
                                <p class="text-sm text-gray-500">
                                    No files have been submitted for this requirement in the selected course
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                <i class="fa-solid fa-folder-open text-2xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">
                                Incomplete Selection
                            </h3>
                            <p class="text-sm text-gray-500">
                                Please select a specific user and course to view submitted files
                            </p>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel (60%) - File Preview -->
    <div class="w-full md:w-3/5 flex flex-col h-full">
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition flex-1 flex flex-col overflow-hidden min-h-0">
            <!-- Header -->
            <div class="px-6 py-4 flex items-center justify-between flex-shrink-0" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-eye text-white text-xl"></i>
                    <h2 class="text-lg font-semibold text-white">
                        @if($selectedFile)
                            File Preview
                        @else
                            No File Selected
                        @endif
                    </h2>
                </div>
                @if($selectedFile)
                    <div class="flex items-center gap-3">
                        <select wire:model="selectedStatus" class="border-gray-300 rounded-lg shadow-sm text-sm text-gray-700 focus:border-green-700 focus:ring-green-700 bg-white">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedFile['status'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <button wire:click="updateStatus" 
                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg shadow-sm text-green-700 bg-white hover:bg-green-700 hover:text-white border border-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-700">
                            <i class="fa-solid fa-check mr-1.5"></i>
                            Update
                        </button>
                    </div>
                @endif
            </div>

            <!-- File Display -->
            <div class="flex-1 bg-gray-50 flex flex-col items-center justify-center overflow-hidden">
                @if($selectedFile)
                    @if($isImage)
                        <div class="p-6 w-full h-full flex items-center justify-center bg-white">
                            <img src="{{ $fileUrl }}" alt="{{ $selectedFile['file_name'] }}" class="max-w-full max-h-full object-contain rounded-lg shadow-sm">
                        </div>
                    @elseif($isPdf)
                        <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0" class="w-full h-full border-0 bg-white"></iframe>
                    @elseif($isOfficeDoc)
                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}" class="w-full h-full border-0 bg-white"></iframe>
                    @else
                        <div class="text-center p-8 max-w-md bg-white rounded-xl shadow-sm">
                            @php
                                $fileIcon = \App\Models\SubmittedRequirement::FILE_ICONS[$selectedFile['extension']] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                            @endphp
                            <div class="mx-auto w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                <i class="fa-solid {{ $fileIcon['icon'] }} text-3xl {{ $fileIcon['color'] }}"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Preview unavailable</h3>
                            <p class="text-sm text-gray-500 mb-4">This file type cannot be previewed in the browser</p>
                            <a href="{{ $fileUrl }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 border border-green-700 shadow-sm text-sm font-medium rounded-lg text-green-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-download mr-2"></i>
                                Download File
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center p-8 max-w-md">
                        <div class="mx-auto w-20 h-20 rounded-full bg-green-700/10 flex items-center justify-center mb-4">
                            <i class="fa-solid fa-file-circle-question text-3xl text-green-700"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            @if($user && $course && count($allFiles) > 0)
                                Select a file to preview
                            @elseif($user && $course)
                                No files available
                            @else
                                Incomplete selection
                            @endif
                        </h3>
                        <p class="text-sm text-gray-500">
                            @if($user && $course && count($allFiles) > 0)
                                Choose a file from the list to view its contents
                            @elseif($user && $course)
                                No files have been submitted for this selection
                            @else
                                Please select a user and course to view files
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>