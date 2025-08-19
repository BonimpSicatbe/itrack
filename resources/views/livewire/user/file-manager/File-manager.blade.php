<div class="flex flex-col gap-4 h-full">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="text-lg uppercase font-bold">File Manager</div>
        <div class="flex items-center gap-4 text-sm text-gray-600">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-folder text-blue-500"></i>
                <span>{{ $totalFiles }} files</span>
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-hard-drive text-green-500"></i>
                <span>{{ $totalSize }}</span>
            </div>
        </div>
    </div>

    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm">
        <ul>
            <li>
                <a class="flex items-center gap-2">
                    <i class="fa-regular fa-folder"></i>
                    File Manager
                </a>
            </li>
            <li>
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-file"></i>
                    Submitted Files
                </span>
            </li>
        </ul>
    </div>

    {{-- Main Content with File Details at Top Level --}}
    <div class="flex-1 min-h-0 overflow-hidden">
        <div class="flex gap-4 h-full">
            {{-- File Manager Content --}}
            <div class="flex-1 min-h-0 {{ $showFileDetails ? 'pr-0' : '' }}">
                @livewire('user.file-manager.show-file-manager')
            </div>

            {{-- File Details Sidebar - NOW AT TOP LEVEL --}}
            @if($showFileDetails && $selectedFile)
                <div class="w-96 bg-base-100 border border-gray-200 rounded-lg shadow-lg overflow-hidden flex flex-col animate-slide-in-right file-manager">
                    {{-- Compact Header --}}
                    <div class="p-3 border-b bg-gradient-to-r from-primary/5 to-secondary/5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid {{ $this->getFileIcon($selectedFile->submissionFile->file_name ?? '') }} text-lg"></i>
                                <div>
                                    <h3 class="font-bold text-sm">File Details</h3>
                                    <p class="text-xs text-gray-600 truncate max-w-60" title="{{ $selectedFile->submissionFile->file_name ?? 'Unknown File' }}">{{ $selectedFile->submissionFile->file_name ?? 'Unknown File' }}</p>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-circle btn-ghost" wire:click="closeFileDetails">
                                <i class="fa-solid fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Content starts immediately after header --}}
                    <div class="flex-1 overflow-y-auto p-3">
                        <div class="space-y-4">
                            {{-- File Information --}}
                            <div>
                                <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-info-circle text-blue-500 text-xs"></i>
                                    File Information
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between items-start gap-2">
                                        <span class="text-gray-600">File Name:</span>
                                        <span class="font-medium text-right break-all max-w-48">{{ $selectedFile->submissionFile->file_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-600">File Size:</span>
                                        <span class="font-medium">{{ $this->formatFileSize($selectedFile->submissionFile->size ?? 0) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-600">File Type:</span>
                                        <span class="font-medium uppercase">{{ strtoupper(pathinfo($selectedFile->submissionFile->file_name ?? '', PATHINFO_EXTENSION)) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-600">Uploaded:</span>
                                        <span class="font-medium">{{ $selectedFile->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-600">Time:</span>
                                        <span class="font-medium">{{ $selectedFile->created_at->format('H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Requirement Information --}}
                            <div>
                                <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-tasks text-green-500 text-xs"></i>
                                    Requirement Details
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between items-start gap-2">
                                        <span class="text-gray-600">Requirement:</span>
                                        <span class="font-medium text-right max-w-48">{{ $selectedFile->requirement->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="badge {{ $selectedFile->status_badge }} badge-sm">
                                            {{ $selectedFile->status_text }}
                                        </span>
                                    </div>
                                    @if($selectedFile->requirement->description ?? null)
                                        <div>
                                            <span class="text-gray-600 block mb-1">Description:</span>
                                            <div class="bg-gray-50 p-2 rounded text-gray-700 leading-relaxed text-xs">
                                                {{ $selectedFile->requirement->description }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Notes Section --}}
                            @if($selectedFile->notes)
                                <div>
                                    <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                        <i class="fa-solid fa-sticky-note text-yellow-500 text-xs"></i>
                                        Notes
                                    </h4>
                                    <div class="bg-gray-50 p-2 rounded text-gray-700 leading-relaxed text-xs">
                                        {{ $selectedFile->notes }}
                                    </div>
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div>
                                <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-cog text-purple-500 text-xs"></i>
                                    Actions
                                </h4>
                                <div class="space-y-2">
                                    {{-- Download File Button - Always show if file exists --}}
                                    @if($selectedFile->submissionFile)
                                        <button 
                                            wire:click="downloadFile({{ $selectedFile->id }})"
                                            class="btn btn-primary btn-sm w-full"
                                            wire:loading.attr="disabled"
                                            wire:target="downloadFile({{ $selectedFile->id }})"
                                        >
                                            <span wire:loading.remove wire:target="downloadFile({{ $selectedFile->id }})">
                                                <i class="fa-solid fa-download mr-2"></i>
                                                Download File
                                            </span>
                                            <span wire:loading wire:target="downloadFile({{ $selectedFile->id }})">
                                                <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                                                Downloading...
                                            </span>
                                        </button>
                                    @endif

                                    {{-- Open File Button --}}
                                    @if($this->canOpenFile($selectedFile))
                                        <a 
                                            href="{{ $this->getFileUrl($selectedFile) }}" 
                                            target="_blank"
                                            class="btn btn-success btn-sm w-full"
                                        >
                                            <i class="fa-solid fa-external-link-alt mr-2"></i>
                                            <span>Open File</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile backdrop --}}
                <div class="mobile-backdrop md:hidden" wire:click="closeFileDetails"></div>
            @endif
        </div>
    </div>
</div>