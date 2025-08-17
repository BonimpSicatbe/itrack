<div class="flex flex-col md:flex-row gap-6 h-[calc(100vh-6rem)]">
    <!-- Left side (30%) - Combined scrollable panel with matching height -->
    <div class="w-full md:w-2/4 flex flex-col gap-6 overflow-y-auto pr-2 h-full">
        <!-- Requirement Details Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Requirement Details</h2>
            </div>
            
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Requirement Name</h3>
                    <p class="mt-1 text-gray-900 font-medium">{{ $submittedRequirement->requirement->name }}</p>
                </div>
                
                @if($submittedRequirement->requirement->description)
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Description</h3>
                    <p class="mt-1 text-gray-700 whitespace-pre-line">{{ $submittedRequirement->requirement->description }}</p>
                </div>
                @endif
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Due Date</h3>
                        <p class="mt-1 text-gray-700">{{ $submittedRequirement->requirement->due->format('M j, Y \a\t g:i A') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Created By</h3>
                        <p class="mt-1 text-gray-700">{{ $submittedRequirement->requirement->creator->full_name }}</p>
                    </div>
                </div>
                
                @if($submittedRequirement->reviewed_at)
                <div class="pt-2 border-t border-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Feedback</h3>
                    <p class="mt-1 text-gray-700">{{ $submittedRequirement->admin_notes ?? 'No feedback provided' }}</p>
                    <p class="mt-2 text-xs text-gray-500">
                        Reviewed on {{ $submittedRequirement->reviewed_at->format('M j, Y') }}
                        @if($submittedRequirement->reviewer)
                        by {{ $submittedRequirement->reviewer->full_name }}
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Submitted Files Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex-1">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Submitted Files</h2>
                <span class="text-sm text-gray-500">{{ count($allFiles) }} file(s)</span>
            </div>
            
            @if(count($allFiles) > 0)
                <div class="space-y-3 h-[calc(100%-3rem)] overflow-y-auto">
                    @foreach($allFiles as $file)
                        <button wire:click="selectFile('{{ $file['id'] }}')"
                        class="w-full text-left p-3 rounded-lg transition-all duration-200
                                {{ $selectedFile['id'] === $file['id'] ? 
                                    'bg-blue-50 border border-blue-200 shadow-sm' : 
                                    'border border-gray-100 hover:border-gray-200 hover:bg-gray-50' }}">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center
                                    @if($file['status'] == 'approved') bg-green-50 text-green-600
                                    @elseif($file['status'] == 'rejected') bg-red-50 text-red-600
                                    @else bg-blue-50 text-blue-600 @endif">
                                    <i class="fa-solid {{ getFileIcon($file['mime_type']) }} text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-900 truncate">{{ $file['file_name'] }}</p>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 mt-1">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-user text-gray-400"></i>
                                            {{ $submittedRequirement->user->full_name }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-calendar text-gray-400"></i>
                                            {{ $file['created_at']->format('M j, Y') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center gap-1 capitalize px-2 py-1 rounded text-xs
                                        @if($file['status'] == 'approved') text-green-600 bg-green-50
                                        @elseif($file['status'] == 'rejected') text-red-600 bg-red-50
                                        @else text-blue-600 bg-blue-50 @endif">
                                        {{ $this->formatStatus($file['status']) }}
                                    </span>
                                </div>
                                <div class="flex-shrink-0 text-gray-400">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 h-full flex flex-col items-center justify-center">
                    <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-file-arrow-up text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No files submitted</h3>
                    <p class="text-gray-500">This requirement hasn't been fulfilled yet</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right side (70%) - File Preview Panel -->
    <div class="w-full md:w-2/3 flex flex-col h-full">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex-1 flex flex-col h-full">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800">File Preview</h2>
                <div class="flex items-center gap-3">
                    <select wire:model="selectedStatus" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ $selectedFile['status'] === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button wire:click="updateStatus" 
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Status
                    </button>
                </div>
            </div>
            
            <div class="flex-1 rounded-lg bg-gray-50 border border-gray-200 flex flex-col items-center justify-center overflow-hidden">
                @if($selectedFile)
                    @if($isImage)
                        <div class="p-4 w-full h-full flex items-center justify-center">
                            <img src="{{ $fileUrl }}" 
                                alt="{{ $selectedFile['file_name'] }}"
                                class="max-w-full max-h-[70vh] object-contain rounded shadow-sm">
                        </div>
                    @elseif($isPdf)
                        <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0" 
                                class="w-full h-full border-0"
                                title="PDF Document: {{ $selectedFile['file_name'] }}"></iframe>
                    @elseif($isOfficeDoc)
                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}" 
                                class="w-full h-full border-0"
                                frameborder="0"
                                title="Office Document: {{ $selectedFile['file_name'] }}"></iframe>
                    @else
                        <div class="text-center p-8 max-w-md">
                            <div class="mx-auto w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                <i class="fa-solid {{ getFileIcon($selectedFile['mime_type']) }} text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Preview unavailable</h3>
                            <p class="text-gray-500 mb-4">This file type cannot be previewed in the browser</p>
                            <a href="{{ $fileUrl }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fa-solid fa-download mr-2"></i>
                                Download File
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center p-8">
                        <div class="mx-auto w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                            <i class="fa-solid fa-file-circle-question text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No file selected</h3>
                        <p class="text-gray-500">Select a file from the list to preview it here</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@php
function getFileIcon($mimeType)
{
    $icons = [
        'pdf' => 'fa-file-pdf',
        'word' => 'fa-file-word',
        'excel' => 'fa-file-excel',
        'powerpoint' => 'fa-file-powerpoint',
        'archive' => 'fa-file-zipper',
        'text' => 'fa-file-lines',
        'audio' => 'fa-file-audio',
        'video' => 'fa-file-video',
        'image' => 'fa-file-image',
        'code' => 'fa-file-code',
        'default' => 'fa-file'
    ];

    if (str_starts_with($mimeType, 'application/pdf')) return $icons['pdf'];
    if (str_starts_with($mimeType, 'application/msword') || 
        str_starts_with($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')) return $icons['word'];
    if (str_starts_with($mimeType, 'application/vnd.ms-excel') || 
        str_starts_with($mimeType, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) return $icons['excel'];
    if (str_starts_with($mimeType, 'application/vnd.ms-powerpoint') || 
        str_starts_with($mimeType, 'application/vnd.openxmlformats-officedocument.presentationml.presentation')) return $icons['powerpoint'];
    if (str_starts_with($mimeType, 'application/zip') || 
        str_starts_with($mimeType, 'application/x-rar-compressed') || 
        str_starts_with($mimeType, 'application/x-tar')) return $icons['archive'];
    if (str_starts_with($mimeType, 'text/')) return $icons['text'];
    if (str_starts_with($mimeType, 'audio/')) return $icons['audio'];
    if (str_starts_with($mimeType, 'video/')) return $icons['video'];
    if (str_starts_with($mimeType, 'image/')) return $icons['image'];
    if (str_starts_with($mimeType, 'application/json') || 
        str_starts_with($mimeType, 'application/javascript') || 
        str_starts_with($mimeType, 'text/x-')) return $icons['code'];
    
    return $icons['default'];
}
@endphp