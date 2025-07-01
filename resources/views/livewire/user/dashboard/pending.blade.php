<div class="flex flex-col gap-2 p-4 overflow-hidden bg-white rounded-lg">
    {{-- Header --}}
    <div class="flex flex-row items-center justify-between w-full">
        <div class="text-lg uppercase font-bold">Pending Requirements</div>
        <a href="{{ route('user.pending-task') }}" class="flex items-center text-green-500 hover:text-green-700 text-xs hover:link transition-all">
            See More <i class="fa-solid fa-chevron-right ml-1"></i>
        </a>
    </div>

    {{-- Horizontally scrollable container --}}
    <div class="relative">
        <div class="flex flex-row gap-4 pb-4 overflow-x-auto w-full scrollbar-hide">
            @forelse($pendingRequirements as $requirement)
                @php
                    // Ensure the due date is a Carbon instance
                    $dueDate = \Carbon\Carbon::parse($requirement->due);
                @endphp
                <div class="flex-shrink-0 border rounded-lg p-3 flex flex-col w-[300px] hover:bg-gray-50 transition-all">
                    <div class="text-lg font-bold truncate">{{ $requirement->name }}</div>
                    <div class="text-sm text-gray-600 mt-1">
                        Due: {{ $dueDate->format('M j, Y') }}
                    </div>
                    <div class="text-xs mt-2">
                        Priority: 
                        <span class="font-medium capitalize {{ 
                            $requirement->priority === 'high' ? 'text-red-500' :
                            ($requirement->priority === 'urgent' ? 'text-red-700' : 'text-gray-600')
                        }}">
                            {{ $requirement->priority }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="w-full py-4 text-center text-gray-500">
                    No pending requirements!
                </div>
            @endforelse
        </div>
    </div>
</div>