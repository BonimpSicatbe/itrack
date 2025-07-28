@php
    function getFileIcon($mimeType)
    {
        if (Str::startsWith($mimeType, 'application/pdf')) {
            return 'fa-file-pdf';
        }
        if (
            Str::startsWith($mimeType, 'application/msword') ||
            Str::startsWith($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
        ) {
            return 'fa-file-word';
        }
        if (
            Str::startsWith($mimeType, 'application/vnd.ms-excel') ||
            Str::startsWith($mimeType, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ) {
            return 'fa-file-excel';
        }
        if (Str::startsWith($mimeType, 'application/zip')) {
            return 'fa-file-archive';
        }
        if (Str::startsWith($mimeType, 'text/plain')) {
            return 'fa-file-lines';
        }
        if (Str::startsWith($mimeType, 'audio/')) {
            return 'fa-file-audio';
        }
        if (Str::startsWith($mimeType, 'video/')) {
            return 'fa-file-video';
        }
        return 'fa-file';
    }
@endphp

<div class="flex flex-col gap-4 w-full" x-data="{ view: 'grid' }">
    <div class="flex flex-col gap-4 w-full bg-white shadow-md rounded-lg p-6">
        {{-- File Manager Header --}}
        <div class="flex flex-row gap-4 items-center justify-between w-full">
            <h2 class="text-2xl font-bold">File Manager</h2>
            <div class="flex flex-row gap-4">
                <button type="button" class="btn btn-sm btn-default btn-square" @click="view = 'list'"
                    :class="{ 'btn-primary': view === 'list' }">
                    <i class="fa-solid fa-list"></i>
                </button>
                <button type="button" class="btn btn-sm btn-default btn-square" @click="view = 'grid'"
                    :class="{ 'btn-primary': view === 'grid' }">
                    <i class="fa-solid fa-th"></i>
                </button>
            </div>
        </div>

        {{-- File Manager Display --}}
        <!-- Grid View -->
        <div x-show="view === 'grid'"
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-2">
            @forelse ($files ?? [] as $media)
                <div
                    class="file-card w-full flex items-center gap-4 bg-gray-50 hover:bg-gray-200 transition-all rounded-lg p-3 truncate">
                    @if (Str::startsWith($media->mime_type, 'image/'))
                        <a href="{{ $media->getUrl() }}" target="_blank" class="w-full">
                            <img src="{{ $media->getUrl() }}" alt="Image"
                                class="h-32 w-full object-cover rounded-md" />
                        </a>
                    @else
                        <a href="{{ $media->getUrl() }}" target="_blank" class="flex items-center justify-center text-center gap-2 h-32 w-full">
                            <i class="fa-solid {{ getFileIcon($media->mime_type) }} fa-2x text-gray-400"></i>
                            {{-- <span class="font-semibold truncate">{{ $media->file_name }}</span> --}}
                        </a>
                    @endif
                </div>
            @empty
                <div
                    class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4 xl:col-span-6 text-center text-gray-500">
                    No files found.
                </div>
            @endforelse
        </div>

        <!-- List View -->
        <div x-show="view === 'list'" class="divide-y divide-gray-200 max-h-[500px] overflow-y-auto">
            @forelse ($files ?? [] as $media)
                <a href="{{ $media->getUrl() }}" target="_blank"
                    class="flex items-center gap-4 p-2 hover:bg-gray-100 transition rounded-md group">
                    @if (Str::startsWith($media->mime_type, 'image/'))
                        <img src="{{ $media->getUrl() }}" alt="Image" class="h-12 w-12 object-cover rounded-md" />
                    @else
                        <span class="h-12 w-12 flex items-center justify-center">
                            <i class="fa-solid {{ getFileIcon($media->mime_type) }} fa-2x text-gray-400"></i>
                        </span>
                    @endif
                    <div class="flex-1 truncate">
                        <span class="font-semibold group-hover:underline">{{ $media->file_name }}</span>
                        <div class="text-xs text-gray-500">{{ $media->mime_type }}</div>
                    </div>
                </a>
            @empty
                <div class="text-center text-gray-500 py-4">No files found.</div>
            @endforelse
        </div>
    </div>
</div>
