<div>
    @php
        use App\Models\SubmittedRequirement;
    @endphp

    <!-- Two Column Layout -->
    <div class="flex flex-col lg:flex-row gap-4 w-full">

        <!-- Left: File Manager -->
        <div class="{{ (!$selectedFile && $showSemesterPanel) || $selectedFile ? 'lg:w-3/4' : 'w-full' }} h-[calc(100vh-6rem)] overflow-y-auto" style="padding-right: 10px;">
            <!-- HEADER -->
            <div class="flex justify-between items-center text-white p-4 rounded-2xl shadow-md mb-2" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <div class="flex items-center gap-3">
                    <div class="pl-3 bg-1C7C54/10 rounded-xl">
                        <i class="fa-solid fa-file text-white text-2xl"></i>
                    </div>
                    <h2 class="text-xl md:text-xl font-semibold">File Manager</h2>

                    <!-- Current Status -->
                    @if($activeSemester)
                        <span class="ml-3 px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white">
                            {{ $selectedSemester ? 'Viewing: ' : 'Current Semester: ' }}{{ $activeSemester->name }}
                        </span>
                    @else
                        <span class="ml-3 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            No Active Semester
                        </span>
                    @endif
                </div>

                <!-- Right Controls: View toggle + Semester toggle -->
                <div class="flex items-center gap-2">
                    <!-- View toggle buttons -->
                    <div class="flex items-center gap-1 bg-white/20 p-1 rounded-xl">
                        <!-- List Toggle -->
                        <button 
                            wire:click="setViewMode('list')" 
                            class="p-2 rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                            title="List view"
                        >
                            <i class="fas fa-list"></i>
                        </button>
                        <!-- Grid Toggle -->
                        <button 
                            wire:click="setViewMode('grid')" 
                            class="p-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-white text-1C7C54 shadow-sm' : 'hover:bg-white/20 text-white' }}"
                            title="Grid view"
                        >
                            <i class="fas fa-th"></i>
                        </button>
                    </div>

                    <!-- Semester Toggle -->
                    <button 
                        type="button" 
                        class="px-3 py-2 rounded-lg bg-white text-1C7C54 font-medium text-sm shadow-sm hover:bg-73E2A7 transition flex items-center gap-2"
                        wire:click="togglePanel"
                        title="{{ $showSemesterPanel ? 'Hide Semester Panel' : 'Show Semester Panel' }}">
                        <span>Semester</span>
                        <i class="fas fa-chevron-{{ $showSemesterPanel ? 'left' : 'right' }} text-xs"></i>
                    </button>
                </div>
            </div>
            <!-- END HEADER -->

            <div class="w-full bg-white shadow-md rounded-2xl p-6">
                {{-- Removed the old header section --}}

                {{-- Filter Bar --}}
                <div class="bg-DEF4C6 p-4 rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-sm mb-6">
                    
                    <!-- Group By Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            wire:click="setGroup('user')"
                            class="px-4 py-2 text-sm rounded-xl font-medium transition-colors 
                                   {{ $groupBy === 'user' ? 'bg-1C7C54 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-73E2A7 hover:text-1B512D' }}"
                        >
                            <i class="fa-solid fa-user mr-2"></i>
                            By User
                        </button>
                        <button
                            wire:click="setGroup('college')"
                            class="px-4 py-2 text-sm rounded-xl font-medium transition-colors 
                                   {{ $groupBy === 'college' ? 'bg-1C7C54 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-73E2A7 hover:text-1B512D' }}"
                        >
                            <i class="fa-solid fa-building mr-2"></i>
                            By College
                        </button>
                        <button
                            wire:click="setGroup('department')"
                            class="px-4 py-2 text-sm rounded-xl font-medium transition-colors 
                                   {{ $groupBy === 'department' ? 'bg-1C7C54 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-73E2A7 hover:text-1B512D' }}"
                        >
                            <i class="fa-solid fa-people-group mr-2"></i>
                            By Department
                        </button>
                        @if($groupBy)
                        <button
                            wire:click="clearGroupFilter"
                            class="px-4 py-2 text-sm rounded-xl font-medium transition-colors bg-white text-1C7C54 hover:bg-73E2A7 hover:text-1B512D"
                        >
                            <i class="fa-solid fa-times mr-2"></i>
                            Clear
                        </button>
                        @endif
                    </div>

                    <!-- Search Bar -->
                    <div class="relative max-w-md w-[300px]">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-1C7C54 text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            class="block w-[300px] p-2 pl-9 text-sm text-1B512D border border-DEF4C6 rounded-xl bg-white focus:ring-1C7C54 focus:border-1C7C54" 
                            placeholder="Search files or users..."
                        >
                    </div>
                </div>

                {{-- Breadcrumb Navigation --}}
                @if(count($breadcrumbs) > 0)
                <div class="flex items-center text-sm text-gray-500 mb-4 overflow-x-auto whitespace-nowrap scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                    @foreach($breadcrumbs as $index => $crumb)
                        @if(!$loop->last)
                            <button 
                                wire:click="{{ $crumb['type'] === 'file' ? 'clearSelection' : ($crumb['type'] === 'root' ? 'clearGroupFilter' : 'navigateToFolder(\'' . $crumb['type'] . '\', \'' . $crumb['id'] . '\')') }}"
                                class="hover:text-blue-600 transition-colors {{ $crumb['type'] === 'file' ? 'cursor-default' : '' }} max-w-[200px] truncate"
                                title="{{ $crumb['name'] }}"
                                {{ $crumb['type'] === 'file' ? 'disabled' : '' }}
                            >
                                {{ $crumb['name'] }}
                            </button>
                            <span class="mx-2">/</span>
                        @else
                            <span 
                                class="text-gray-800 font-medium max-w-[250px] truncate" 
                                title="{{ $crumb['name'] }}">
                                {{ $crumb['name'] }}
                            </span>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- Group Selection --}}
                @if($groupBy && !$selectedGroup)
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
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

                {{-- Only show files if we should display them --}}
                @if($shouldDisplayFiles)
                    {{-- Files List --}}
                    {{-- GRID VIEW --}}
                    @if($viewMode === 'grid')
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-5 mt-4">
                        @forelse ($files as $media)
                            @php
                                $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                                $extension = strtolower($extension);
                                $fileIcon = SubmittedRequirement::FILE_ICONS[$extension]['icon'] ?? SubmittedRequirement::FILE_ICONS['default']['icon'];
                                $fileColor = SubmittedRequirement::FILE_ICONS[$extension]['color'] ?? SubmittedRequirement::FILE_ICONS['default']['color'];

                                $isImage = str_starts_with($media->mime_type, 'image/');
                                $isPdf = $media->mime_type === 'application/pdf';
                                $isOfficeDoc = in_array($extension, ['doc','docx','xls','xlsx','ppt','pptx']);
                                $isPreviewable = $isImage || $isPdf || $isOfficeDoc;
                                $fileUrl = route('file.preview', ['submission' => $media->model_id, 'file' => $media->id]);
                            @endphp
                            
                            <div class="file-card group relative flex flex-col bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-md transition-all cursor-pointer"
                            wire:click="selectFile('{{ $media->id }}')"
                            ondblclick="window.open('{{ route('file.preview', ['submission' => $media->model_id, 'file' => $media->id]) }}', '_blank')">
                                
                                <!-- File Preview -->
                                <div class="h-36 w-full bg-gray-50 flex items-center justify-center overflow-hidden rounded-t-2xl relative">
                                    @if($isImage)
                                        <img src="{{ $fileUrl }}" alt="{{ $media->file_name }}" 
                                            class="w-full h-full object-cover transition-transform group-hover:scale-105">
                                    @elseif($isPdf)
                                        <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=0" 
                                                class="w-full h-full group-hover:scale-105 transition-transform rounded-t-2xl" 
                                                frameborder="0"></iframe>
                                    @elseif($isOfficeDoc)
                                        <iframe src="https://docs.google.com/gview?url={{ urlencode($media->getUrl()) }}&embedded=true" 
                                                class="w-full h-full rounded-t-2xl" 
                                                frameborder="0"></iframe>
                                    @else
                                        <div class="flex flex-col items-center justify-center text-center p-4">
                                            <i class="fa-solid {{ $fileIcon }} {{ $fileColor }} text-3xl mb-2"></i>
                                            <p class="text-xs text-gray-600 font-semibold">{{ strtoupper($extension) }} File</p>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- File Info -->
                                <div class="p-3 flex-1 flex flex-col">
                                    <div class="truncate text-sm font-semibold text-gray-900">{{ $media->file_name }}</div>
                                    <div class="text-xs text-gray-500 font-medium">
                                        @if($media->model && $media->model->user)
                                            {{ $media->model->user->full_name }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400 mt-auto">
                                        @if($media->size >= 1048576)
                                            {{ number_format($media->size / 1048576, 1) }} MB
                                        @elseif($media->size >= 1024)
                                            {{ number_format($media->size / 1024, 1) }} KB
                                        @else
                                            {{ $media->size }} B
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center text-gray-500 py-8">
                                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-file-circle-exclamation text-gray-400 text-xl"></i>
                                </div>
                                <p class="text-sm font-semibold">No files found.</p>
                            </div>
                        @endforelse
                    </div>
                    @endif



                    <!-- List View -->
                    @if($viewMode === 'list')
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($files as $media)
                                    @php
                                        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                                        $extension = strtolower($extension);
                                        $fileIcon = SubmittedRequirement::FILE_ICONS[$extension]['icon'] ?? SubmittedRequirement::FILE_ICONS['default']['icon'];
                                        $fileColor = SubmittedRequirement::FILE_ICONS[$extension]['color'] ?? SubmittedRequirement::FILE_ICONS['default']['color'];
                                    @endphp
                                    <tr 
                                        wire:click="selectFile('{{ $media->id }}')"
                                        ondblclick="window.open('{{ route('file.preview', ['submission' => $media->model_id, 'file' => $media->id]) }}', '_blank')"
                                        class="hover:bg-gray-50 transition cursor-pointer">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="h-8 w-8 flex items-center justify-center mr-3">
                                                    <i class="fa-solid {{ $fileIcon }} {{ $fileColor }}"></i>
                                                </span>
                                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">
                                                    {{ $media->file_name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if($media->model && $media->model->user)
                                                    {{ $media->model->user->full_name }}
                                                @else
                                                    Unknown
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                {{ $media->created_at->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                @if($media->size >= 1073741824)
                                                    {{ number_format($media->size / 1073741824, 2) }} GB
                                                @elseif($media->size >= 1048576)
                                                    {{ number_format($media->size / 1048576, 2) }} MB
                                                @elseif($media->size >= 1024)
                                                    {{ number_format($media->size / 1024, 2) }} KB
                                                @else
                                                    {{ $media->size }} bytes
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                            No files found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @endif

                    {{-- Pagination --}}
                    @if($files->hasPages())
                    <div class="mt-4">
                       {{ $files->links('livewire.pagination') }}
                    </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Right: Semester Panel OR File Details -->
        @if((!$selectedFile && $showSemesterPanel) || $selectedFile)
        <div class="w-full lg:w-1/4 flex-shrink-0 ">
            <div class="sticky top-4 h-[calc(100vh-6rem)] overflow-y-auto">

                {{-- Semester Panel (when no file selected) --}}
                @if(!$selectedFile && $showSemesterPanel)
                <div class="w-full h-full">
                    @livewire('admin.file-manager.semester-view')
                </div>
                @endif

                {{-- FILE DETAILS --}}
                @if($selectedFile)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-md p-5 h-full flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-1C7C54">File Details</h3>
                        <button wire:click="clearSelection" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                            <i class="fa-solid fa-times text-gray-600 text-sm"></i>
                        </button>
                    </div>

                    <div class="flex flex-col gap-4 text-sm">
                        <div>
                            <p class="font-semibold text-gray-500">File Name</p>
                            <p class="text-gray-800 break-all">{{ $selectedFile->file_name }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500">File Type</p>
                            <p class="text-gray-800">{{ $selectedFile->mime_type }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500">Size</p>
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
                            <p class="font-semibold text-gray-500">Uploaded At</p>
                            <p class="text-gray-800">{{ $selectedFile->created_at->format('M d, Y g:i A') }}</p>
                        </div>

                        {{-- Requirement Info --}}
                        @if($selectedFile->model && $selectedFile->model->requirement)
                        <div class="pt-3 border-t">
                            <p class="font-semibold text-gray-500">Requirement</p>
                            <p class="text-gray-800 font-semibold">{{ $selectedFile->model->requirement->name }}</p>
                            @if($selectedFile->model->requirement->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $selectedFile->model->requirement->description }}</p>
                            @endif
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <div>
                                    <p class="text-gray-500 font-semibold">Due Date</p>
                                    <p class="text-gray-800">{{ $selectedFile->model->requirement->due->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-semibold">Status</p>
                                    <p class="text-gray-800 capitalize">{{ $selectedFile->model->status }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Uploader Info --}}
                        @if($selectedFile->model && $selectedFile->model->user)
                        <div class="pt-3 border-t">
                            <p class="font-semibold text-gray-500">Uploaded By</p>
                            <p class="text-gray-800">{{ $selectedFile->model->user->full_name }}</p>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                @if($selectedFile->model->user->department)
                                <div>
                                    <p class="text-gray-500 font-semibold">Department</p>
                                    <p class="text-gray-800">{{ $selectedFile->model->user->department->name }}</p>
                                </div>
                                @endif
                                @if($selectedFile->model->user->college)
                                <div>
                                    <p class="text-gray-500 font-semibold">College</p>
                                    <p class="text-gray-800">{{ $selectedFile->model->user->college->name }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2 pt-5 border-t mt-auto">
                        <a href="{{ $selectedFile->getUrl() }}" target="_blank"
                        class="flex-1 px-4 py-2 bg-1C7C54 text-white rounded-full text-sm font-semibold shadow-sm hover:bg-73E2A7 transition flex items-center justify-center">
                            <i class="fa-solid fa-download mr-2"></i> Download
                        </a>
                        @php
                            $isPreviewable = Str::startsWith($selectedFile->mime_type, 'image/') || 
                                            Str::startsWith($selectedFile->mime_type, 'application/pdf') ||
                                            Str::startsWith($selectedFile->mime_type, 'text/');
                        @endphp
                        @if($isPreviewable)
                        <a href="{{ route('file.preview', ['submission' => $selectedFile->model_id]) }}" target="_blank"
                        class="flex-1 px-4 py-2 bg-DEF4C6 text-1B512D rounded-full text-sm font-semibold shadow-sm hover:bg-B1CF5F transition flex items-center justify-center">
                            <i class="fa-solid fa-eye mr-2"></i> Open File
                        </a>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif
    </div>
</div>