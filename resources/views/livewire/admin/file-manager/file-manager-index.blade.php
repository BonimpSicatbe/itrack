<div>
    @php
        use App\Models\SubmittedRequirement;
    @endphp

    <!-- Two Column Layout -->
    <div class="flex flex-col lg:flex-row gap-3 w-full">
        <!-- Left: File Manager -->
        <div class="{{ (!$selectedFile && $showSemesterPanel) || $selectedFile ? 'lg:w-3/4' : 'w-full' }} h-[calc(100vh-6rem)] overflow-y-auto" style="padding-right: 10px;">
            <!-- HEADER -->
            <div class="flex justify-between items-center text-white p-4 rounded-xl shadow-md mb-2" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <div class="flex items-center gap-3">
                    <div class="pl-3 bg-1C7C54/10 rounded-xl">
                        <i class="fa-solid fa-file text-white text-2xl"></i>
                    </div>
                    <h2 class="text-xl md:text-xl font-semibold">File Manager</h2>

                    <!-- Current Status -->
                    @if($activeSemester)
                        <span class="ml-3 px-4 py-1.5 rounded-full text-sm font-semibold bg-white/20 text-white">
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
                        class="px-3 py-2 rounded-xl bg-white text-1C7C54 font-medium text-sm shadow-sm hover:bg-73E2A7 transition flex items-center gap-2"
                        wire:click="togglePanel"
                        title="{{ $showSemesterPanel ? 'Hide Semester Panel' : 'Show Semester Panel' }}">
                        <span>Semester</span>
                        <i class="fas fa-chevron-{{ $showSemesterPanel ? 'left' : 'right' }} text-xs"></i>
                    </button>
                </div>
            </div>
            <!-- END HEADER -->

            <div class="w-full bg-white shadow-md rounded-xl p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4  pb-4">
                    <!-- Left: Search -->
                    <div class="flex items-center w-full md:w-auto">
                        <div class="relative max-w-md w-full md:w-sm">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-gray-500 text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                class="block w-sm p-2 pl-9 text-sm text-1B512D border border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 rounded-xl" 
                                placeholder="{{ $groupBy && !$selectedGroup ? 'Search ' . $groupBy . 's...' : 'Search files or users...' }}"
                            >
                        </div>
                    </div>

                    <!-- Right: Group By Buttons -->
                    <div class="ml-auto border border-gray-300 shadow-sm rounded-xl bg-white font-semibold p-1">
                        <!-- Inner container with vertical dividers -->
                        <div class="flex flex-wrap items-center divide-x divide-gray-300 overflow-hidden rounded-lg">
                            <button
                                wire:click="setGroup('user')"
                                class="px-4 py-2 text-sm font-semibold transition-colors 
                                    {{ $groupBy === 'user' ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-green-600/20 hover:text-1B512D' }}"
                            >
                                <i class="fa-solid fa-user mr-2"></i>
                                User
                            </button>
                            <button
                                wire:click="setGroup('college')"
                                class="px-4 py-2 text-sm font-semibold transition-colors 
                                    {{ $groupBy === 'college' ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-green-600/20 hover:text-1B512D' }}"
                            >
                                <i class="fa-solid fa-building mr-2"></i>
                                College
                            </button>
                            <button
                                wire:click="setGroup('department')"
                                class="px-4 py-2 text-sm font-semibold transition-colors 
                                    {{ $groupBy === 'department' ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-1C7C54 hover:bg-green-600/20 hover:text-1B512D' }}"
                            >
                                <i class="fa-solid fa-people-group mr-2"></i>
                                Department
                            </button>
                            @if($groupBy)
                            <button
                                wire:click="clearGroupFilter"
                                class="px-4 py-2 text-sm font-semibold transition-colors bg-white text-1C7C54 hover:bg-green-600/20 hover:text-1B512D"
                            >
                                <i class="fa-solid fa-times mr-2"></i>
                                Clear
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Breadcrumb Navigation --}}
                @if(count($breadcrumbs) > 0)
                <div class="flex items-center text-sm text-green-700 bg-green-50 border rounded-xl p-3 mb-4 overflow-x-auto whitespace-nowrap scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                    @foreach($breadcrumbs as $index => $crumb)
                        @if(!$loop->last)
                            <button 
                                wire:click="{{ $crumb['type'] === 'file' ? 'clearSelection' : ($crumb['type'] === 'root' ? 'clearGroupFilter' : 'navigateToFolder(\'' . $crumb['type'] . '\', \'' . $crumb['id'] . '\')') }}"
                                class="hover:text-amber-200 transition-colors {{ $crumb['type'] === 'file' ? 'cursor-default' : '' }} max-w-[200px] truncate"
                                title="{{ $crumb['name'] }}"
                                {{ $crumb['type'] === 'file' ? 'disabled' : '' }}
                            >
                                {{ $crumb['name'] }}
                            </button>
                            <span class="mx-2">
                                <i class="fa-solid fa-chevron-right text-xs text-gray-800 mb-2"></i>
                            </span>
                        @else
                            <span 
                                class="text-green-700 font-medium max-w-[250px] truncate" 
                                title="{{ $crumb['name'] }}">
                                {{ $crumb['name'] }}
                            </span>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- Group Selection --}}
                @if($groupBy && !$selectedGroup)
                <div class="overflow-y-auto" style="max-height: calc(100vh - 250px);">
                    @if($viewMode === 'list')
                        <!-- List View for Groups -->
                        <div class="flex flex-col gap-2 ml-2 mr-2 mb-2">
                            <div class="grid grid-cols-12 gap-4 px-4 py-3 bg-green-700 rounded-xl text-sm font-semibold text-white">
                                <div class="col-span-10">{{ ucfirst($groupBy) }} Name</div>
                                <div class="col-span-2">Files Count</div>
                            </div>
                            
                            @forelse ($groupedItems as $groupId => $group)
                                <button wire:click="selectGroup('{{ $groupId }}')" 
                                class="grid grid-cols-12 gap-4 p-4 bg-white rounded-xl border border-gray-300 hover:bg-green-50 cursor-pointer">
                                    <div class="col-span-10 flex items-center gap-3">
                                        <i class="fas fa-folder-open text-green-700 text-xl"></i>
                                        <span class="text-sm font-semibold text-1B512D truncate" title="{{ $group['name'] }}">
                                            {{ $group['name'] }}
                                        </span>
                                    </div>
                                    <div class="col-span-2 flex items-center">
                                        <span class="px-2 py-1 text-xs bg-DEF4C6 text-1C7C54 font-semibold rounded-full">
                                            {{ $group['count'] }} {{ $group['count'] == 1 ? 'file' : 'files' }}
                                        </span>
                                    </div>
                                </button>
                            @empty
                                <div class="flex flex-col items-center justify-center py-8 text-gray-500 col-span-12">
                                    <i class="fas fa-folder-open text-3xl text-gray-300 mb-2"></i>
                                    <p class="text-sm font-semibold text-gray-500">No {{ $groupBy }}s found.</p>
                                    @if($search)
                                        <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    @else
                        <!-- Grid View for Groups -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-9 gap-8 mb-6 mt-2">
                            @forelse ($groupedItems as $groupId => $group)
                                <button wire:click="selectGroup('{{ $groupId }}')" 
                                class="flex flex-col items-center text-center gap-3 group transition-all cursor-pointer">
                                
                                    <!-- Folder Icon with Count -->
                                    <div class="relative">
                                        <i class="fas fa-folder-open text-8xl text-green-700 hover:text-green-800"></i>

                                        <!-- Count Badge -->
                                        <span class="absolute -top-0.5 -right-3 bg-DEF4C6 text-gray-800 text-sm font-bold 
                                                    rounded-full w-8 h-8 flex items-center justify-center shadow-md 
                                                    group-hover:bg-2A7F3F">
                                            {{ $group['count'] }}
                                        </span>
                                    </div>

                                    <!-- Group Name -->
                                    <h3 class="font-semibold text-1B512D truncate text-sm max-w-[115px] group-hover:text-2A7F3F" 
                                        title="{{ $group['name'] }}">
                                        {{ $group['name'] }}
                                    </h3>
                                </button>
                            @empty
                                <!-- Empty State -->
                                <div class="flex flex-col items-center justify-center py-10 text-gray-500 col-span-full">
                                    <i class="fas fa-folder-open text-3xl text-gray-300 mb-2"></i>
                                    <p class="text-sm font-semibold text-gray-500">No {{ $groupBy }}s found.</p>
                                    @if($search)
                                        <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>
                @endif

                {{-- Only show files if we should display them --}}
                @if($shouldDisplayFiles)
                    {{-- Files List --}}

                    {{-- GRID VIEW --}}
                    @if ($viewMode === 'grid')
                        <div class="grid gap-6 mt-6"
                            style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
                            @forelse ($files as $media)
                                @php
                                    $extension     = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                                    $fileIcon      = SubmittedRequirement::FILE_ICONS[$extension]['icon'] ?? SubmittedRequirement::FILE_ICONS['default']['icon'];
                                    $fileColor     = SubmittedRequirement::FILE_ICONS[$extension]['color'] ?? SubmittedRequirement::FILE_ICONS['default']['color'];

                                    $isImage       = str_starts_with($media->mime_type, 'image/');
                                    $isPdf         = $media->mime_type === 'application/pdf';
                                    $isOfficeDoc   = in_array($extension, ['doc','docx','xls','xlsx','ppt','pptx']);
                                    $isPreviewable = $isImage || $isPdf || $isOfficeDoc;

                                    $fileUrl       = route('file.preview', [
                                        'submission' => $media->model_id,
                                        'file'       => $media->id
                                    ]);
                                @endphp

                                <!-- File Card -->
                                <div 
                                    class="file-card group relative flex flex-col bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all cursor-pointer"
                                    wire:click="selectFile('{{ $media->id }}')"
                                    ondblclick="window.open('{{ $fileUrl }}', '_blank')"
                                >
                                    <!-- File Preview -->
                                    <div class="h-40 w-full bg-gray-50 flex items-center justify-center overflow-hidden rounded-t-2xl relative">
                                        @if ($isImage)
                                            <img src="{{ $fileUrl }}" alt="{{ $media->file_name }}"
                                                class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105">
                                        @elseif ($isPdf)
                                            <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=0"
                                                    class="w-full h-full transition-transform duration-200 group-hover:scale-105 rounded-t-2xl"
                                                    frameborder="0"></iframe>
                                        @elseif ($isOfficeDoc)
                                            <iframe src="https://docs.google.com/gview?url={{ urlencode($media->getUrl()) }}&embedded=true"
                                                    class="w-full h-full rounded-t-2xl"
                                                    frameborder="0"></iframe>
                                        @else
                                            <div class="flex flex-col items-center justify-center text-center p-4">
                                                <i class="fa-solid {{ $fileIcon }} {{ $fileColor }} text-4xl mb-2"></i>
                                                <p class="text-xs text-gray-600 font-semibold">{{ strtoupper($extension) }} File</p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- File Info -->
                                    <div class="p-4 flex-1 flex flex-col">
                                        <div class="truncate text-sm font-semibold text-gray-900">{{ $media->file_name }}</div>
                                        <div class="text-xs text-gray-500 font-medium">
                                            @if ($media->model && $media->model->user)
                                                {{ $media->model->user->full_name }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400 mt-auto">
                                            @if ($media->size >= 1048576)
                                                {{ number_format($media->size / 1048576, 1) }} MB
                                            @elseif ($media->size >= 1024)
                                                {{ number_format($media->size / 1024, 1) }} KB
                                            @else
                                                {{ $media->size }} B
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <!-- Empty State -->
                                <div class="col-span-full text-center text-gray-500 py-10">
                                    <i class="fa-solid fa-folder-open text-3xl text-gray-300 mb-2"></i>
                                    <p class="text-sm font-semibold text-gray-500">No files found.</p>
                                    @if($search)
                                        <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    @endif

                    <!-- List View -->
                    @if($viewMode === 'list')
                    <div class="flex flex-col gap-2 mb-2">                            
                        <!-- Column Headers -->
                        <div class="grid grid-cols-12 gap-4 px-4 py-3 bg-green-700 rounded-xl text-sm font-semibold text-white">
                            <div class="col-span-4">File</div>
                            <div class="col-span-4">Uploaded By</div>
                            <div class="col-span-2">Date</div>
                            <div class="col-span-2">Size</div>
                        </div>
                        
                        <!-- File Items -->
                        @forelse ($files as $media)
                            @php
                                $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                                $extension = strtolower($extension);
                                $fileIcon = SubmittedRequirement::FILE_ICONS[$extension]['icon'] ?? SubmittedRequirement::FILE_ICONS['default']['icon'];
                                $fileColor = SubmittedRequirement::FILE_ICONS[$extension]['color'] ?? SubmittedRequirement::FILE_ICONS['default']['color'];
                            @endphp
                            <div 
                                wire:click="selectFile('{{ $media->id }}')"
                                ondblclick="window.open('{{ route('file.preview', ['submission' => $media->model_id, 'file' => $media->id]) }}', '_blank')"
                                class="grid grid-cols-12 gap-4 p-4 text-gray-500 bg-white rounded-xl border border-gray-300 hover:bg-green-50 transition cursor-pointer items-center">
                                <!-- File Icon & Name -->
                                <div class="col-span-4 flex items-center gap-3">
                                    <div class="w-8 h-8 flex items-center justify-center">
                                        <i class="fas {{ $fileIcon }} {{ $fileColor }} text-xl"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-500 truncate" title="{{ $media->file_name }}">
                                        {{ $media->file_name }}
                                    </span>
                                </div>
                                
                                <!-- Uploaded By -->
                                <div class="col-span-4 text-sm text-gray-600 truncate" title="{{ $media->model && $media->model->user ? $media->model->user->full_name : 'Unknown' }}">
                                    @if($media->model && $media->model->user)
                                        {{ $media->model->user->full_name }}
                                        @if($media->model->user->college)
                                            <span class="text-xs block">({{ $media->model->user->college->name }})</span>
                                        @endif
                                    @else
                                        Unknown
                                    @endif
                                </div>
                                
                                <!-- Date -->
                                <div class="col-span-2 text-sm text-gray-600">
                                    {{ $media->created_at->format('M d, Y') }}
                                </div>
                                
                                <!-- Size -->
                                <div class="col-span-2 text-sm text-gray-600">
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
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-gray-500 col-span-12">
                                <i class="fa-solid fa-folder-open text-3xl text-gray-300 mb-2"></i>
                                <p class="text-sm font-semibold text-gray-500">No files found.</p>
                                @if($search)
                                    <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                                @endif
                            </div>
                        @endforelse
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
                <div class="bg-white rounded-xl border border-gray-200 shadow-md p-5 h-full flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-green-800 text-2xl"></i>
                            <h3 class="text-xl font-semibold text-green-800">File Details</h3>
                        </div>
                        <button wire:click="clearSelection" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                            <i class="fa-solid fa-times text-gray-600 text-sm"></i>
                        </button>
                    </div>

                    <div class="flex flex-col gap-4 text-sm">
                        <div>
                            <p class="font-semibold text-gray-800 uppercase text-xs">File Name</p>
                            <p class="text-gray-500 font-semibold break-all">{{ $selectedFile->file_name }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 uppercase text-xs">File Type</p>
                            <p class="text-gray-500 font-semibold">{{ $selectedFile->mime_type }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 uppercase text-xs">Size</p>
                            <p class="text-gray-500 font-semibold">
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
                            <p class="font-semibold text-gray-800 uppercase text-xs">Uploaded At</p>
                            <p class="text-gray-500 font-semibold">{{ $selectedFile->created_at->format('M d, Y g:i A') }}</p>
                        </div>

                        {{-- Requirement Info --}}
                        @if($selectedFile->model && $selectedFile->model->requirement)
                        <div class="pt-3 border-t-2 border-gray-300 text-sm">
                            <p class="font-semibold text-gray-800 uppercase text-xs">Requirement</p>
                            <p class="text-gray-500 font-semibold">{{ $selectedFile->model->requirement->name }}</p>
                            <div class="grid grid-cols-2 gap-2 mt-4">
                                <div>
                                    <p class="text-gray-800 font-semibold uppercase text-xs">Due Date</p>
                                    <p class="text-gray-500 font-semibold text-sm">{{ $selectedFile->model->requirement->due->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-800 font-semibold uppercase text-xs">Status</p>
                                    <p class="text-gray-500 capitalize text-sm font-semibold">{{ $selectedFile->model->status }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Uploader Info --}}
                        @if($selectedFile->model && $selectedFile->model->user)
                        <div class="pt-3 border-t-2 border-gray-300">
                            <p class="font-semibold text-gray-800 uppercase text-xs">Uploaded By</p>
                            <p class="text-gray-500 text-sm font-semibold">{{ $selectedFile->model->user->full_name }}</p>
                            <div class="grid grid-cols-2 gap-2 mt-4">
                                @if($selectedFile->model->user->department)
                                <div>
                                    <p class="text-gray-800 font-semibold text-xs uppercase">Department</p>
                                    <p class="text-gray-500 font-semibold text-sm">{{ $selectedFile->model->user->department->name }}</p>
                                </div>
                                @endif
                                @if($selectedFile->model->user->college)
                                <div>
                                    <p class="text-gray-800 font-semibold text-xs uppercase">College</p>
                                    <p class="text-gray-500 font-semibold text-sm">{{ $selectedFile->model->user->college->name }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2 pt-5 border-t-2 border-gray-300 mt-auto">
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