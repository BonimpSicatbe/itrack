<x-user.app-layout>
    <div class="flex flex-col w-full max-w-7xl mx-auto bg-gray-50 min-h-screen p-6">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Archive Header -->
            <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-box-archive text-white text-2xl"></i>
                    <h1 class="text-2xl font-bold text-white">Archive</h1>
                </div>
                <a href="{{ route('user.file-manager') }}" class="flex items-center gap-2 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left text-white text-sm"></i>
                    <span class="text-white text-sm font-medium">Back to File Manager</span>
                </a>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white border-b border-gray-200 px-8 py-4">
                <form method="GET" action="{{ route('user.archive') }}" class="flex items-center gap-4 flex-wrap">
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        </div>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Search files by name..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        >
                    </div>
                    
                    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">All Status</option>
                        <option value="approved" {{ ($statusFilter ?? '') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="revision_needed" {{ ($statusFilter ?? '') == 'revision_needed' ? 'selected' : '' }}>Revision Needed</option>
                        <option value="rejected" {{ ($statusFilter ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="under_review" {{ ($statusFilter ?? '') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                    </select>
                    
                    @if(isset($semesters) && $semesters->count() > 0)
                        <select name="semester" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">All Semesters</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ ($semesterFilter ?? '') == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->name }} ({{ $semester->start_date->format('M Y') }} - {{ $semester->end_date->format('M Y') }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                    
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-filter mr-2"></i>Apply Filters
                    </button>
                    
                    @if(($search ?? '') || ($statusFilter ?? '') || ($semesterFilter ?? ''))
                        <a href="{{ route('user.archive') }}" class="text-gray-600 hover:text-gray-800 underline">
                            Clear Filters
                        </a>
                    @endif
                </form>
            </div>

            <!-- Archived Files Section -->
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">Archived Files</h2>
                    <!-- FIXED LINE 67: Added null check for $files -->
                    <span class="text-sm text-gray-500">{{ isset($files) ? $files->count() : 0 }} files found</span>
                </div>
                
                @if(isset($semesters) && $semesters->count() > 0)
                    @if(isset($files) && $files->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($files as $file)
                                <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start gap-3 mb-3">
                                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                            @if($file->submissionFile)
                                                @php
                                                    $extension = pathinfo($file->submissionFile->file_name, PATHINFO_EXTENSION);
                                                    $icon = match(strtolower($extension)) {
                                                        'pdf' => 'fa-file-pdf text-red-600',
                                                        'doc', 'docx' => 'fa-file-word text-blue-600',
                                                        'xls', 'xlsx' => 'fa-file-excel text-green-600',
                                                        'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image text-purple-600',
                                                        default => 'fa-file text-gray-600',
                                                    };
                                                @endphp
                                                <i class="fa-solid {{ $icon }}"></i>
                                            @else
                                                <i class="fa-solid fa-file text-gray-600"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-medium text-gray-900 truncate" title="{{ $file->submissionFile->file_name ?? 'Unknown File' }}">
                                                {{ $file->submissionFile->file_name ?? 'Unknown File' }}
                                            </h4>
                                            <p class="text-sm text-gray-500 truncate">
                                                {{ $file->requirement->name ?? 'No requirement' }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                {{ $file->requirement->semester->name ?? '' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2 text-sm text-gray-600">
                                        <div class="flex justify-between">
                                            <span>Status:</span>
                                            <span class="font-medium capitalize">
                                                @switch($file->status)
                                                    @case('approved')
                                                        <span class="text-green-600">Approved</span>
                                                        @break
                                                    @case('revision_needed')
                                                        <span class="text-yellow-600">Revision Needed</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="text-red-600">Rejected</span>
                                                        @break
                                                    @case('under_review')
                                                        <span class="text-blue-600">Under Review</span>
                                                        @break
                                                    @default
                                                        {{ $file->status }}
                                                @endswitch
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Size:</span>
                                            <span class="font-medium">
                                                @if($file->submissionFile && $file->submissionFile->size)
                                                    {{ number_format($file->submissionFile->size / 1024, 1) }} KB
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Submitted:</span>
                                            <span class="font-medium">{{ $file->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex gap-2">
                                        @if($file->submissionFile && $file->getFileUrl())
                                            <a href="{{ $file->getFileUrl() }}" 
                                               target="_blank"
                                               class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-lg transition-colors flex items-center justify-center">
                                                <i class="fa-solid fa-eye mr-2"></i>
                                                View
                                            </a>
                                            <a href="{{ $file->getFileUrl() }}" 
                                               download
                                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition-colors flex items-center justify-center">
                                                <i class="fa-solid fa-download mr-2"></i>
                                                Download
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <i class="fa-solid fa-box-open text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-600">No archived files found</h3>
                            <p class="text-gray-500 mt-2">
                                @if(($search ?? '') || ($statusFilter ?? '') || ($semesterFilter ?? ''))
                                    Try adjusting your filters to see more results.
                                @else
                                    No files found in archived semesters.
                                @endif
                            </p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <i class="fa-solid fa-box-open text-4xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-600">No archived semesters yet</h3>
                        <p class="text-gray-500 mt-2">When semesters are archived, they will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-user.app-layout>