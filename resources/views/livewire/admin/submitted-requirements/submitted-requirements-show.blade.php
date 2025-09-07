<div class="flex flex-col md:flex-row gap-4 h-[calc(100vh-6rem)]">
    <!-- Left Panel (40%) -->
    <div class="w-full md:w-2/5 flex flex-col gap-6 overflow-y-auto pr-2 h-full">

        <!-- Requirement Details -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-0 overflow-hidden">
            <!-- Header -->
            <div class="bg-green-700 text-white px-6 py-3 flex items-center rounded-t-xl">
                <i class="fa-solid fa-circle-info mr-2 text-2xl"></i>
                <h2 class="text-xl font-semibold">Requirement Details</h2>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4 text-sm text-gray-500">
                <div>
                    <h3 class="font-semibold uppercase text-xs">Requirement Name</h3>
                    <p class="mt-1 text-gray-900 font-semibold">{{ $submittedRequirement->requirement->name }}</p>
                </div>

                @if($submittedRequirement->requirement->description)
                <div>
                    <h3 class="font-semibold uppercase text-xs">Description</h3>
                    <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $submittedRequirement->requirement->description }}</p>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-semibold uppercase text-xs">Due Date</h3>
                        <p class="mt-1 text-gray-900">{{ $submittedRequirement->requirement->due->format('M j, Y \a\t g:i A') }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold uppercase text-xs">Created By</h3>
                        <p class="mt-1 text-gray-900">{{ $submittedRequirement->requirement->creator->full_name }}</p>
                    </div>
                </div>

                @if($submittedRequirement->reviewed_at)
                <div class="pt-3 border-t border-gray-200">
                    <p class="mt-2 text-xs">
                        Reviewed on {{ $submittedRequirement->reviewed_at->format('M j, Y') }}
                        @if($submittedRequirement->reviewer)
                        by {{ $submittedRequirement->reviewer->full_name }}
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Submitted Files -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="bg-green-700 text-white px-6 py-3 flex items-center justify-between rounded-t-xl">
                <div class="flex items-center">
                    <i class="fa-solid fa-folder-open mr-2 text-2xl"></i>
                    <h2 class="text-xl font-semibold">Submitted Files</h2>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white text-green-700">
                    {{ count($allFiles) }} file(s)
                </span>
            </div>

            <!-- Body -->
            <div class="p-6 flex-1 overflow-y-auto">
                @if(count($allFiles) > 0)
                    <div class="space-y-5">
                        @foreach($allFiles->groupBy('user.full_name') as $userName => $userFiles)
                            <div>
                                <!-- User Name -->
                                <p class="text-md font-semibold text-gray-500 mb-2 flex items-center">
                                    <i class="fa-solid fa-user mr-2 text-sm text-gray-600"></i> {{ $userName }}
                                </p>

                                <!-- User Files -->
                                <div class="space-y-3">
                                    @foreach($userFiles as $file)
                                        @php
                                            $fileIcon = \App\Models\SubmittedRequirement::FILE_ICONS[$file['extension']] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                                            $submissionModel = App\Models\SubmittedRequirement::find($file['submission_id']);
                                            $statusBadgeClasses = $submissionModel ? $submissionModel->status_badge : 'bg-blue-100 text-blue-800';
                                        @endphp
                                        <button wire:click="selectFile('{{ $file['id'] }}')"
                                            class="w-full text-left p-3 rounded-xl transition-all duration-200 flex items-center gap-3
                                                {{ ($selectedFile && $selectedFile['id'] === $file['id']) ? 
                                                    'bg-green-600 text-white shadow-sm' : 
                                                    'border border-gray-200 hover:border-green-700 hover:bg-green-700/5' }}">
                                            
                                            <!-- File Icon -->
                                            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-white">
                                                <i class="fa-solid {{ $fileIcon['icon'] }} text-lg {{ $fileIcon['color'] }}"></i>
                                            </div>
                                            
                                            <!-- File Info -->
                                            <div class="min-w-0 flex-1">
                                                <p class="font-medium truncate {{ ($selectedFile && $selectedFile['id'] === $file['id']) ? 'text-white' : 'text-gray-900' }}">
                                                    {{ $file['file_name'] }}
                                                </p>
                                                <div class="flex items-center gap-2 text-xs mt-1 {{ ($selectedFile && $selectedFile['id'] === $file['id']) ? 'text-gray-200' : 'text-gray-500' }}">
                                                    <i class="fa-solid fa-calendar"></i>
                                                    {{ $file['created_at']->format('M j, Y') }}
                                                </div>
                                            </div>
                                            
                                            <!-- Status -->
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadgeClasses }}">
                                                    {{ $this->formatStatus($file['status']) }}
                                                </span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 flex-1 flex flex-col items-center justify-center">
                        <div class="mx-auto w-16 h-16 rounded-full bg-green-700/10 flex items-center justify-center mb-3">
                            <i class="fa-solid fa-file-arrow-up text-2xl text-green-700"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No files submitted</h3>
                        <p class="text-sm text-gray-500">This requirement hasn't been fulfilled yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Panel (60%) -->
    <div class="w-full md:w-3/5 flex flex-col h-full">
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition flex-1 flex flex-col h-full overflow-hidden">
            
            <!-- Header -->
            <div class=" text-white px-6 py-3 flex items-center justify-between rounded-t-xl" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <div class="flex items-center">
                    <i class="fa-solid fa-eye mr-2 text-2xl"></i>
                    <h2 class="text-xl font-semibold">File Preview</h2>
                </div>
                <div class="flex items-center gap-3">
                    <select wire:model="selectedStatus" class="border-gray-300 rounded-xl shadow-sm text-sm text-gray-700 focus:border-green-700 focus:ring-green-700">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($selectedFile && $selectedFile['status'] === $value) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button wire:click="updateStatus" 
                        class="inline-flex items-center px-5 py-2 text-sm font-medium rounded-full shadow-sm text-green-700 bg-white hover:bg-green-700 hover:border-white border hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-700">
                        Update
                    </button>
                </div>
            </div>

            <!-- File Display -->
            <div class="flex-1 rounded-b-xl bg-green-700/5 border-t border-green-700/20 flex flex-col items-center justify-center overflow-hidden">
                @if($selectedFile)
                    @if($isImage)
                        <div class="p-4 w-full h-full flex items-center justify-center">
                            <img src="{{ $fileUrl }}" alt="{{ $selectedFile['file_name'] }}" class="max-w-full max-h-[70vh] object-contain rounded-xl shadow-sm">
                        </div>
                    @elseif($isPdf)
                        <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0" class="w-full h-full border-0"></iframe>
                    @elseif($isOfficeDoc)
                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}" class="w-full h-full border-0 rounded-xl"></iframe>
                    @else
                        <div class="text-center p-8 max-w-md">
                            @php
                                $fileIcon = \App\Models\SubmittedRequirement::FILE_ICONS[$selectedFile['extension']] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                            @endphp
                            <div class="mx-auto w-20 h-20 rounded-full bg-white flex items-center justify-center mb-4 shadow">
                                <i class="fa-solid {{ $fileIcon['icon'] }} text-3xl {{ $fileIcon['color'] }}"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Preview unavailable</h3>
                            <p class="text-sm text-gray-500 mb-4">This file type cannot be previewed in the browser</p>
                            <a href="{{ $fileUrl }}" target="_blank"
                               class="inline-flex items-center px-4 py-2 border border-green-700 shadow-sm text-sm font-medium rounded-full text-green-700 bg-white hover:bg-gray-100">
                                <i class="fa-solid fa-download mr-2"></i>
                                Download File
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center p-8">
                        <div class="mx-auto w-20 h-20 rounded-full bg-green-700/10 flex items-center justify-center mb-4">
                            <i class="fa-solid fa-file-circle-question text-3xl text-green-700"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No file selected</h3>
                        <p class="text-sm text-gray-500">Select a file from the list to preview it here</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
