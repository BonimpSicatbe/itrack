<div class="flex flex-col md:flex-row w-[92%] mx-auto gap-6 h-[calc(100vh-6rem)]">
    <!-- Left Panel (40%) -->
    <div class="w-full md:w-2/5 flex flex-col gap-6 overflow-y-auto pr-2 h-full">
        <!-- Requirement Details -->
        <div class="bg-white rounded-2xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-1B512D">
                    <i class="fa-solid fa-circle-info mr-2"></i> Requirement Details
                </h2>
            </div>

            <div class="space-y-4 text-sm">
                <div>
                    <h3 class="font-semibold text-gray-500">Requirement Name</h3>
                    <p class="mt-1 text-gray-900 font-semibold">{{ $requirement->name }}</p>
                </div>

                @if($requirement->description)
                <div>
                    <h3 class="font-semibold text-gray-500">Description</h3>
                    <p class="mt-1 text-gray-700 whitespace-pre-line">{{ $requirement->description }}</p>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-semibold text-gray-500">Due Date</h3>
                        <p class="mt-1 text-gray-700">{{ $requirement->due->format('M j, Y \a\t g:i A') }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-500">Created By</h3>
                        <p class="mt-1 text-gray-700">{{ $requirement->creator->full_name }}</p>
                    </div>
                </div>
                
                <!-- Reviewed Information -->
                @if($selectedFile && $selectedFile['reviewed_at'])
                <div class="pt-3 border-t border-DEF4C6">
                    <p class="mt-2 text-xs text-gray-500">
                        Reviewed on {{ \Carbon\Carbon::parse($selectedFile['reviewed_at'])->format('M j, Y') }}
                        @if($selectedFile['reviewer'])
                        by {{ $selectedFile['reviewer']['full_name'] }}
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Submitted Files -->
        <div class="bg-white rounded-2xl shadow-md p-6 flex-1 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-1B512D">
                    <i class="fa-solid fa-folder-open mr-2"></i> Submitted Files
                </h2>
                <span class="text-sm text-gray-500">{{ count($allFiles) }} file(s)</span>
            </div>

            @if(count($allFiles) > 0)
                <div class="space-y-5 flex-1 overflow-y-auto pr-1">
                    @php
                        // Group files by user name using collection method
                        $groupedFiles = $allFiles->groupBy(function($file) {
                            return $file['user']['full_name'] ?? 'Unknown User';
                        });
                    @endphp
                    
                    @foreach($groupedFiles as $userName => $userFiles)
                        <div>
                            <!-- User Name -->
                            <h3 class="text-sm font-semibold text-1B512D mb-2 flex items-center">
                                <i class="fa-solid fa-user mr-2 text-gray-600"></i> {{ $userName }}
                            </h3>

                            <!-- User Files -->
                            <div class="space-y-3">
                                @foreach($userFiles as $file)
                                    @php
                                        $fileIcon = \App\Models\SubmittedRequirement::FILE_ICONS[$file['extension']] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                                        // Get the status badge classes using the model method
                                        $submissionModel = App\Models\SubmittedRequirement::find($file['submission_id']);
                                        $statusBadgeClasses = $submissionModel ? $submissionModel->status_badge : 'bg-blue-100 text-blue-800';
                                    @endphp
                                    <button wire:click="selectFile('{{ $file['id'] }}')"
                                    class="w-full text-left p-3 rounded-xl transition-all duration-200 flex items-center gap-3
                                        {{ ($selectedFile && $selectedFile['id'] === $file['id']) ? 
                                            'bg-gray-600 border border-gray-900 text-white shadow-sm' : 
                                            'border border-gray-200 hover:border-1B512D hover:bg-73E2A7/10' }}">
                                        <!-- File Icon -->
                                        <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-white">
                                            <i class="fa-solid {{ $fileIcon['icon'] }} text-lg {{ $fileIcon['color'] }}"></i>
                                        </div>
                                        <!-- File Info -->
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium truncate {{ ($selectedFile && $selectedFile['id'] === $file['id']) ? 'text-white' : 'text-gray-900' }}">
                                                {{ $file['file_name'] }}
                                            </p>
                                            <div class="flex items-center gap-2 text-xs mt-1 {{ ($selectedFile && $selectedFile['id'] === $file['id']) ? 'text-gray-300' : 'text-gray-500' }}">
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
                    <div class="mx-auto w-16 h-16 rounded-full bg-DEF4C6 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-file-arrow-up text-2xl text-1B512D"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No files submitted</h3>
                    <p class="text-gray-500">This requirement hasn't been fulfilled yet</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Panel (60%) -->
    <div class="w-full md:w-3/5 flex flex-col h-full">
        <div class="bg-white rounded-2xl shadow-md p-6 flex-1 flex flex-col h-full">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-1B512D">
                    <i class="fa-solid fa-eye mr-2"></i> File Preview
                </h2>
                @if($selectedFile)
                    <div class="flex items-center gap-3">
                        <select wire:model="selectedStatus" class="border-gray-300 rounded-xl shadow-sm focus:border-1B512D focus:ring-1B512D text-sm">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedFile['status'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <button wire:click="updateStatus" 
                            class="inline-flex items-center px-3.5 py-1.5 text-sm font-medium rounded-full shadow-sm text-white bg-1C7C54 hover:bg-1B512D focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-1B512D">
                            Update
                        </button>
                    </div>
                @endif
            </div>

            <!-- File Display -->
            <div class="flex-1 rounded-xl bg-DEF4C6/40 border border-DEF4C6 flex flex-col items-center justify-center overflow-hidden">
                @if($selectedFile)
                    @if($isImage)
                        <div class="p-4 w-full h-full flex items-center justify-center">
                            <img src="{{ $fileUrl }}" alt="{{ $selectedFile['file_name'] }}" class="max-w-full max-h-[70vh] object-contain rounded-xl shadow-sm">
                        </div>
                    @elseif($isPdf)
                        <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0" class="w-full h-full border-0"></iframe>
                    @elseif($isOfficeDoc)
                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}" class="w-full h-full border-0"></iframe>
                    @else
                        <div class="text-center p-8 max-w-md">
                            @php
                                $fileIcon = \App\Models\SubmittedRequirement::FILE_ICONS[$selectedFile['extension']] ?? \App\Models\SubmittedRequirement::FILE_ICONS['default'];
                            @endphp
                            <div class="mx-auto w-20 h-20 rounded-full bg-white flex items-center justify-center mb-4">
                                <i class="fa-solid {{ $fileIcon['icon'] }} text-3xl {{ $fileIcon['color'] }}"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Preview unavailable</h3>
                            <p class="text-gray-500 mb-4">This file type cannot be previewed in the browser</p>
                            <a href="{{ $fileUrl }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 border border-1C7C54 shadow-sm text-sm font-medium rounded-full text-1B512D bg-white hover:bg-73E2A7/20">
                                <i class="fa-solid fa-download mr-2"></i>
                                Download File
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center p-8">
                        <div class="mx-auto w-20 h-20 rounded-full bg-DEF4C6 flex items-center justify-center mb-4">
                            <i class="fa-solid fa-file-circle-question text-3xl text-1B512D"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No files submitted</h3>
                        <p class="text-gray-500">No files uploaded for this requirement yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>