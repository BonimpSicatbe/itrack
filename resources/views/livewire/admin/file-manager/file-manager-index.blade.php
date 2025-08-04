<div>
    @php
        function getFileIcon($mimeType)
        {
            if (Str::startsWith($mimeType, 'application/pdf')) {
                return 'fa-file-pdf text-red-500';
            }
            if (
                Str::startsWith($mimeType, 'application/msword') ||
                Str::startsWith($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ) {
                return 'fa-file-word text-blue-500';
            }
            if (
                Str::startsWith($mimeType, 'application/vnd.ms-excel') ||
                Str::startsWith($mimeType, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ) {
                return 'fa-file-excel text-green-500';
            }
            if (Str::startsWith($mimeType, 'application/zip')) {
                return 'fa-file-archive text-yellow-500';
            }
            if (Str::startsWith($mimeType, 'text/plain')) {
                return 'fa-file-lines text-gray-500';
            }
            if (Str::startsWith($mimeType, 'audio/')) {
                return 'fa-file-audio text-purple-500';
            }
            if (Str::startsWith($mimeType, 'video/')) {
                return 'fa-file-video text-indigo-500';
            }
            if (Str::startsWith($mimeType, 'image/')) {
                return 'fa-file-image text-pink-500';
            }
            return 'fa-file text-gray-400';
        }
        
        function isPreviewable($mimeType)
        {
            return Str::startsWith($mimeType, 'image/') || 
                   Str::startsWith($mimeType, 'application/pdf') ||
                   Str::startsWith($mimeType, 'text/');
        }
    @endphp

    <div class="flex flex-col gap-4 w-full">
        <div class="flex flex-col gap-4 w-full bg-white shadow-md rounded-lg p-6">
            {{-- File Manager Header --}}
            <div class="flex flex-col gap-4">
                <div class="flex flex-row gap-4 items-center justify-between w-full">
                    <div class="flex items-center gap-4">
                        <h2 class="text-2xl font-bold">File Manager</h2>
                        @if($activeSemester = \App\Models\Semester::getActiveSemester())
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Current Semester: {{ $activeSemester->name }}
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-row gap-4">
                        <a href="{{ route('admin.semesters.index') }}" 
                        class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-calendar-days mr-2"></i>
                            Manage Semesters
                        </a>
                        <button type="button" class="btn btn-sm btn-default btn-square" 
                            wire:click="setViewMode('list')"
                            @class([
                                'btn-primary' => $viewMode === 'list',
                                'btn-default' => $viewMode !== 'list'
                            ])>
                            <i class="fa-solid fa-list"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-default btn-square" 
                            wire:click="setViewMode('grid')"
                            @class([
                                'btn-primary' => $viewMode === 'grid',
                                'btn-default' => $viewMode !== 'grid'
                            ])>
                            <i class="fa-solid fa-th"></i>
                        </button>
                    </div>
                </div>

                {{-- Search and Filter Bar --}}
                <div class="flex flex-col md:flex-row gap-4 items-stretch">
                    <div class="flex-1 relative">
                        <input type="text" wire:model.live.debounce.300ms="search" 
                            placeholder="Search files..." 
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fa-solid fa-search"></i>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" class="btn btn-sm btn-default"
                            wire:click="setGroup('user')"
                            @class([
                                'btn-primary' => $groupBy === 'user',
                                'btn-default' => $groupBy !== 'user'
                            ])>
                            <i class="fa-solid fa-user mr-2"></i>
                            By User
                        </button>
                        <button type="button" class="btn btn-sm btn-default"
                            wire:click="setGroup('college')"
                            @class([
                                'btn-primary' => $groupBy === 'college',
                                'btn-default' => $groupBy !== 'college'
                            ])>
                            <i class="fa-solid fa-building mr-2"></i>
                            By College
                        </button>
                        <button type="button" class="btn btn-sm btn-default"
                            wire:click="setGroup('department')"
                            @class([
                                'btn-primary' => $groupBy === 'department',
                                'btn-default' => $groupBy !== 'department'
                            ])>
                            <i class="fa-solid fa-people-group mr-2"></i>
                            By Department
                        </button>
                        @if($groupBy)
                        <button type="button" class="btn btn-sm btn-default"
                            wire:click="clearGroupFilter">
                            <i class="fa-solid fa-times mr-2"></i>
                            Clear
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Group Selection --}}
                @if($groupBy && !$selectedGroup)
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-medium mb-2">Select {{ ucfirst($groupBy) }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        @forelse($groups as $group)
                            <button wire:click="selectGroup('{{ $group->id }}')"
                                class="flex items-center justify-between p-3 bg-white hover:bg-gray-100 rounded-lg border">
                                <span class="truncate">
                                    @if($groupBy === 'user')
                                        {{ $group->full_name }}
                                    @else
                                        {{ $group->name }}
                                    @endif
                                </span>
                                <span class="ml-2 px-2 py-1 bg-gray-100 text-xs rounded-full">
                                    {{ $group->files_count }} files
                                </span>
                            </button>
                        @empty
                            <div class="col-span-full text-center text-gray-500 py-4">
                                No {{ $groupBy }}s with files found
                            </div>
                        @endforelse
                    </div>
                </div>
                @endif

                {{-- Current Filter Indicator --}}
                @if($selectedGroupName)
                <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-lg">
                    <span class="font-medium">Showing files for:</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                        {{ ucfirst($groupBy) }}: {{ $selectedGroupName }}
                    </span>
                    <button wire:click="clearGroupFilter" class="ml-auto text-blue-600 hover:text-blue-800">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
                @endif
            </div>

            <div class="flex gap-6">
                {{-- Files List --}}
                <div class="{{ $selectedFile ? 'w-2/3' : 'w-full' }}">
                    <!-- Grid View -->
                    @if($viewMode === 'grid')
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-2">
                        @forelse ($files as $media)
                            <div
                                class="file-card w-full flex flex-col items-center gap-2 bg-gray-50 hover:bg-gray-200 transition-all rounded-lg p-3 cursor-pointer"
                                wire:click="selectFile('{{ $media->id }}')"
                                ondblclick="window.location.href='{{ route('file.preview', ['submission' => $media->model_id]) }}'">
                                <div class="flex items-center justify-center text-center gap-2 h-32 w-full">
                                    <i class="fa-solid {{ getFileIcon($media->mime_type) }} fa-3x"></i>
                                </div>
                                <div class="w-full truncate text-center text-sm font-medium">
                                    {{ $media->file_name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($media->model && $media->model->user)
                                        {{ $media->model->user->full_name }}
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div
                                class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4 xl:col-span-6 text-center text-gray-500 py-8">
                                No files found.
                            </div>
                        @endforelse
                    </div>
                    @endif

                    <!-- List View -->
                    @if($viewMode === 'list')
                    <div class="divide-y divide-gray-200 max-h-[500px] overflow-y-auto">
                        @forelse ($files as $media)
                            <div wire:click="selectFile('{{ $media->id }}')"
                                ondblclick="window.location.href='{{ route('file.preview', ['submission' => $media->model_id]) }}'"
                                class="flex items-center gap-4 p-2 hover:bg-gray-100 transition rounded-md cursor-pointer">
                                <span class="h-12 w-12 flex items-center justify-center">
                                    <i class="fa-solid {{ getFileIcon($media->mime_type) }} fa-2x"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold truncate">{{ $media->file_name }}</span>
                                        <span class="text-xs px-2 py-1 bg-gray-100 rounded-full whitespace-nowrap">
                                            {{ $media->mime_type }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 truncate">
                                        @if($media->model && $media->model->user)
                                            {{ $media->model->user->full_name }}
                                            @if($media->model->user->department)
                                                • {{ $media->model->user->department->name }}
                                            @endif
                                            @if($media->model->user->college)
                                                • {{ $media->model->user->college->name }}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 whitespace-nowrap">
                                    {{ $media->created_at->format('M d, Y') }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-8">No files found.</div>
                        @endforelse
                    </div>
                    @endif

                    {{-- Pagination --}}
                    @if($files->hasPages())
                    <div class="mt-4">
                        {{ $files->links() }}
                    </div>
                    @endif
                </div>

                {{-- File Details Panel --}}
                @if($selectedFile)
                <div class="w-1/3 bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">File Details</h3>
                        <button wire:click="clearSelection" class="text-gray-500 hover:text-gray-700">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>

                    <div class="flex flex-col gap-4">

                        {{-- File Details --}}
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">File Name</p>
                                <p class="text-gray-800 break-all">{{ $selectedFile->file_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">File Type</p>
                                <p class="text-gray-800">{{ $selectedFile->mime_type }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Size</p>
                                <p class="text-gray-800">
                                    @if($selectedFile->size >= 1073741824)
                                        {{ number_format($selectedFile->size / 1073741824, 2) }} GB
                                    @elseif($selectedFile->size >= 1048576)
                                        {{ number_format($selectedFile->size / 1048576, 2) }} MB
                                    @elseif($selectedFile->size >= 1024)
                                        {{ number_format($selectedFile->size / 1024, 2) }} KB
                                    @else
                                        {{ $selectedFile->size }} bytes
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Uploaded At</p>
                                <p class="text-gray-800">{{ $selectedFile->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                            
                            {{-- Requirement Information --}}
                            @if($selectedFile->model && $selectedFile->model->requirement)
                            <div class="pt-2 border-t">
                                <p class="text-sm font-medium text-gray-500">Requirement</p>
                                <p class="text-gray-800 font-medium">{{ $selectedFile->model->requirement->name }}</p>
                                @if($selectedFile->model->requirement->description)
                                    <p class="text-sm text-gray-600 mt-1">{{ $selectedFile->model->requirement->description }}</p>
                                @endif
                                <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                                    <div>
                                        <p class="text-gray-500">Due Date</p>
                                        <p class="text-gray-800">{{ $selectedFile->model->requirement->due->format('M d, Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Status</p>
                                        <p class="text-gray-800 capitalize">{{ $selectedFile->model->status }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            {{-- Uploader Information --}}
                            @if($selectedFile->model && $selectedFile->model->user)
                            <div class="pt-2 border-t">
                                <p class="text-sm font-medium text-gray-500">Uploaded By</p>
                                <p class="text-gray-800">{{ $selectedFile->model->user->full_name }}</p>
                                <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                                    @if($selectedFile->model->user->department)
                                    <div>
                                        <p class="text-gray-500">Department</p>
                                        <p class="text-gray-800">{{ $selectedFile->model->user->department->name }}</p>
                                    </div>
                                    @endif
                                    @if($selectedFile->model->user->college)
                                    <div>
                                        <p class="text-gray-500">College</p>
                                        <p class="text-gray-800">{{ $selectedFile->model->user->college->name }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 pt-4 border-t">
                            <a href="{{ $selectedFile->getUrl() }}" 
                            target="_blank"
                            class="btn btn-sm btn-primary flex-1">
                                <i class="fa-solid fa-download mr-2"></i>
                                Download
                            </a>
                            @if(isPreviewable($selectedFile->mime_type))
                            <a href="{{ route('file.preview', ['submission' => $selectedFile->model_id]) }}" 
                            target="_blank"
                            class="btn btn-sm btn-secondary flex-1">
                                <i class="fa-solid fa-eye mr-2"></i>
                                Open File
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>