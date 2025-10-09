<div class="rounded-xl shadow-sm px-6 py-4" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
    @if($currentSemester)
        <div class="flex items-center justify-between">
            {{-- Left side: Semester info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex items-center justify-center w-10 h-10 bg-green-50 rounded-xl">
                        <i class="fas fa-calendar-alt text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white text-lg truncate">
                            {{ $currentSemester->name }}
                        </h3>
                        <p class="text-sm text-gray-300">
                            {{ $currentSemester->start_date->format('M d') }} - {{ $currentSemester->end_date->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right side: Status and progress --}}
            <div class="flex items-center gap-4">
                {{-- Active status badge --}}
                <div class="flex items-center gap-2">
                    <div class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </div>
                    <span class="text-sm font-medium text-white whitespace-nowrap">Active</span>
                </div>

                {{-- Progress section --}}
                <div class="flex items-center gap-2">
                    <div class="text-sm font-semibold text-white whitespace-nowrap">
                        {{ number_format($semesterProgress, 0) }}%
                    </div>
                    <div class="w-20 bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-500 ease-out {{ $this->progressColor }}" 
                            style="width: {{ $semesterProgress }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status alert (only if needed) --}}
        @if($daysRemaining <= 7 && $daysRemaining > 0)
            <div class="mt-3 flex items-center gap-2 p-2 bg-orange-100 rounded-xl border-l-4 border-orange-400">
                <i class="fas fa-exclamation-triangle text-orange-500 text-xs"></i>
                <span class="text-xs text-orange-700 font-medium">
                    Semester ending soon
                </span>
            </div>
        @elseif($daysRemaining <= 0)
            <div class="mt-3 flex items-center gap-2 p-2 bg-red-100 rounded-xl border-l-4 border-red-400">
                <i class="fas fa-exclamation-triangle text-red-500 text-xs"></i>
                <span class="text-xs text-red-700 font-medium">
                    Semester has ended
                </span>
            </div>
        @endif
    @else
        {{-- No Active Semester - Compact version --}}
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center">
                <i class="fas fa-triangle-exclamation text-amber-400 text-2xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-white">No Active Semester</h3>
                <p class="text-sm text-gray-100">Progress, pending, and recent data will be displayed here once a semester is active.</p>
            </div>
        </div>
    @endif
</div>