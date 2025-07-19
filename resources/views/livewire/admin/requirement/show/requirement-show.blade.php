<div class="flex flex-col gap-4 p-4">
    {{-- header (action header) --}}
    <div class="flex flex-row items-center gap-2">
        <div class="text-lg font-black uppercase">Requirement Details</div>
        <div class="grow"></div>
        <button type="button" class="btn btn-sm btn-info btn-ghost">
            <i class="fa-solid fa-edit block sm:hidden"></i>
            <span class="hidden sm:inline">Edit</span>
        </button>
        {{-- <button type="button" class="btn btn-sm btn-success">
            <i class="fa-solid fa-check block sm:hidden"></i>
            <span class="hidden sm:inline">Confirm</span>
        </button>
        <button type="button" class="btn btn-sm btn-default">
            <i class="fa-solid fa-xmark block sm:hidden"></i>
            <span class="hidden sm:inline">Cancel</span>
        </button> --}}
    </div>

    {{-- requirement details --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        {{-- Requirement Name --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Name</span>
            <span class="text-base font-medium text-gray-900">{{ $requirement->name ?? 'N/A' }}</span>
        </div>
        {{-- Requirement Description --}}
        <div class="col-span-2 flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Description</span>
            <span class="text-base text-gray-700">{{ $requirement->description ?? 'N/A' }}</span>
        </div>
        {{-- Requirement Due --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Due</span>
            <span class="text-base text-gray-900">{{ $requirement->due->format('M d, Y h:i A') ?? 'N/A' }}</span>
        </div>
        {{-- Requirement Assigned To --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Assigned To</span>
            <span class="text-base text-gray-900">{{ $requirement->assigned_to ?? 'N/A' }}</span>
        </div>
        {{-- Requirement Status --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Status</span>
            <span
                class="inline-block px-2 py-1 rounded text-xs font-bold
                @if ($requirement->status_color === 'success') bg-green-100 text-green-800
                @elseif($requirement->status_color === 'warning') bg-yellow-100 text-yellow-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ $requirement->status ?? 'N/A' }}
            </span>
        </div>
        {{-- Requirement Priority --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Priority</span>
            <span
                class="inline-block px-2 py-1 rounded text-xs font-bold
                @if ($requirement->priority_color === 'info') bg-blue-100 text-blue-800
                @elseif($requirement->priority_color === 'warning') bg-yellow-100 text-yellow-800
                @elseif($requirement->priority_color === 'error') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ $requirement->priority ?? 'N/A' }}
            </span>
        </div>
        {{-- Requirement Created By --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Created By</span>
            <span class="text-base text-gray-900">{{ $requirement->created_by ?? 'N/A' }}</span>
        </div>
        {{-- Requirement Updated By --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Updated By</span>
            <span class="text-base text-gray-900">{{ $requirement->updated_by ?? 'N/A' }}</span>
        </div>
        {{-- Requirement Archived By --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Archived By</span>
            <span class="text-base text-gray-900">{{ $requirement->archived_by ?? 'N/A' }}</span>
        </div>
        {{-- Requirement Created At --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Created At</span>
            <span class="text-base text-gray-900">
                {{ $requirement->created_at ? \Carbon\Carbon::parse($requirement->created_at)->format('M d, Y h:i A') : 'N/A' }}
            </span>
        </div>
        {{-- Requirement Updated At --}}
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-gray-400 uppercase">Updated At</span>
            <span class="text-base text-gray-900">
                {{ $requirement->updated_at ? \Carbon\Carbon::parse($requirement->updated_at)->format('M d, Y h:i A') : 'N/A' }}
            </span>
        </div>
    </div>
</div>
