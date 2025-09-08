<div class="flex flex-col gap-4 p-4 bg-white rounded-lg shadow-sm">
    {{-- Header --}}
    <div class="flex flex-row items-center justify-between w-full">
        <div class="text-xl font-bold text-gray-800 tracking-wide">Pendings</div>
        <a href="{{ route('user.requirements') }}" class="flex items-center gap-1 hover:text-green-500 text-sm text-green-700 font-semibold hover:underline transition-colors">
            see more <i class="fa-solid fa-chevron-right text-xs"></i>
        </a>
    </div>

    {{-- Horizontal scroll cards --}}
    <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
        @forelse($pendingRequirements as $requirement)
            @php    
                $dueDate = \Carbon\Carbon::parse($requirement->due);
                $isOverdue = $dueDate->isPast();
                $isDueSoon = $dueDate->diffInDays() <= 3;
            @endphp
            
            <div class="flex-shrink-0 w-64 bg-white border border-slate-200 rounded-lg p-4 hover:shadow-md transition-all duration-200 hover:border-slate-300">
                {{-- Header with folder icon and title --}}
                <div class="flex items-center gap-3 mb-3">
                    <div class>
                        <i class="fa-solid fa-folder text-xl" style="color: #1C7C54;"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-slate-800 text-sm truncate" title="{{ $requirement->name }}">
                            {{ $requirement->name }}
                        </h4>
                    </div>
                </div>

                {{-- Content section --}}
                <div class="space-y-3">
                    {{-- Priority indicator --}}
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full" style="background-color: #B1CF5F;"></div>
                        <span class="text-xs text-slate-600 capitalize font-semibold">{{ $requirement->priority }} Priority</span>
                    </div>

                    {{-- Due date section --}}
                    <div class="pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                @if($isOverdue)
                                    <i class="fa-solid fa-exclamation-triangle text-xs text-red-500"></i>
                                @elseif($isDueSoon)
                                    <i class="fa-solid fa-clock text-xs text-amber-500"></i>
                                @else
                                    <i class="fa-solid fa-calendar text-xs" style="color: #1C7C54;"></i>
                                @endif
                                <span class="text-xs font-semibold {{ $isOverdue ? 'text-red-600' : ($isDueSoon ? 'text-amber-600' : '') }}" style="{{ !$isOverdue && !$isDueSoon ? 'text-amber-600' : '' }}">
                                    {{ $dueDate->format('M j, Y') }}
                                </span>
                            </div>
                            
                            {{-- Days remaining indicator - moved inline --}}
                            @if($isOverdue)
                                <span class="text-xs text-red-500 font-medium">{{ $dueDate->diffForHumans() }}</span>
                            @else
                                <span class="text-xs font-medium text-amber-500">{{ $dueDate->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex items-center justify-center w-full text-center">
                <div class="space-y-2">
                    <div class="rounded-full w-fit mx-auto">
                        <i class="fa-solid fa-folder-open text-3xl text-gray-300"></i>
                    </div>
                    <div class="text-green-700 font-semibold text-sm">All caught up!</div>
                    <div class="text-sm text-gray-500">No pending requirements</div>
                </div>
            </div>
        @endforelse
    </div>
</div>