<div class="flex flex-col gap-2 px-6 py-4 bg-white rounded-xl shadow-sm">
    {{-- Header --}}
    <div class="flex flex-row items-center justify-between w-full">
        <div class="text-xl font-bold text-gray-800 tracking-wide">Pending Folders</div>
        <a href="{{ route('user.requirements') }}" class="flex items-center gap-1 hover:text-green-500 text-sm text-green-700 font-semibold hover:underline transition-colors">
            see more <i class="fa-solid fa-chevron-right text-xs"></i>
        </a>
    </div>  

    {{-- Horizontal scroll cards --}}
    <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
        @forelse($pendingFoldersByCourse as $folderData)
            @php    
                $folder = $folderData['folder'];
                $course = $folderData['course'];
                $requirementsCount = $folderData['requirements_count'];
                $earliestDue = $folderData['earliest_due'];
                
                $dueDate = $earliestDue ? \Carbon\Carbon::parse($earliestDue) : null;
                
                if ($dueDate) {
                    $isOverdue = $dueDate->isPast();
                    $isDueSoon = $dueDate->diffInDays() <= 3;
                }

                // Determine folder type for special handling
                $isMidtermFolder = $folder->id == 3;
                $isFinalsFolder = $folder->id == 7;
            @endphp
            
            <div class="flex-shrink-0 w-72 bg-white border border-slate-200 rounded-xl p-4 hover:border-2 hover:border-green-500 transition-colors cursor-pointer"
            onclick="window.location='{{ route('user.requirements') }}?course={{ $course->id }}&folder={{ $folder->id }}'">
                {{-- Header with folder icon and title --}}
                <div class="flex items-center gap-3 mb-3">
                    <div>
                        @if($isMidtermFolder || $isFinalsFolder)
                            <i class="fa-solid fa-folder-open text-xl text-yellow-500"></i>
                        @else
                            <i class="fa-solid fa-folder text-xl text-yellow-500"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-slate-800 text-sm truncate" title="{{ $folder->name }}">
                            {{ $folder->name }}
                            
                        </h4>
                        <p class="text-xs text-slate-500 truncate" title="{{ $course->course_code }} - {{ $course->course_name }}">
                            {{ $course->course_code }}
                        </p>
                    </div>
                </div>

                {{-- Content section --}}
                <div class="space-y-3">
                    {{-- Requirements count --}}
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-list text-slate-500 text-xs"></i>
                        <span class="text-xs text-slate-600 font-medium">Pending Requirements:</span>
                        <span class="text-xs text-slate-700 font-semibold">{{ $requirementsCount }}</span>
                    </div>

                    {{-- Due date section --}}
                    @if($dueDate)
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
                                    <span class="text-xs font-semibold {{ $isOverdue ? 'text-red-600' : ($isDueSoon ? 'text-amber-600' : '') }}" style="{{ !$isOverdue && !$isDueSoon ? 'color: #1C7C54;' : '' }}">
                                        {{ $dueDate->format('M j, Y') }}
                                    </span>
                                </div>
                                
                                {{-- Days remaining indicator --}}
                                @if($isOverdue)
                                    <span class="text-xs text-red-500 font-medium">Overdue</span>
                                @else
                                    <span class="text-xs font-medium text-amber-500">{{ $dueDate->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="pt-2 border-t border-slate-100">
                            <div class="text-xs text-slate-500 italic">No due date set</div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex items-center justify-center w-full text-center">
                <div class="space-y-2">
                    <div class="rounded-full w-fit mx-auto">
                        <i class="fa-solid fa-folder-open text-3xl text-gray-300"></i>
                    </div>
                    <div class="text-sm font-semibold text-gray-500">No pending requirements</div>
                    <div class="text-xs text-gray-400">All requirements are completed or submitted!</div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Information Panel about Auto-Removal --}}
    @if($pendingFoldersByCourse->count() > 0)
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-lightbulb text-blue-500"></i>
                <div class="text-xs text-blue-700">
                    <span class="font-semibold">Smart Completion:</span> Midterm and Finals folders are automatically removed from pending when you submit either the TOS+Examinations partnership OR the Rubrics requirement.
                </div>
            </div>
        </div>
    @endif
</div>