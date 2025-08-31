<div class="bg-white rounded-2xl shadow p-6 h-full border border-DEF4C6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="bg-1C7C54/10 rounded-xl">
                <i class="fa-solid fa-calendar-days text-1C7C54 text-2xl"></i>
            </div>
            <h2 class="text-xl md:text-xl font-semibold text-1B512D">Semester</h2>
        </div>
        <a href="{{ route('admin.semesters.index') }}" 
           class="px-4 py-2 rounded-full bg-1C7C54 text-white text-sm font-semibold hover:bg-73E2A7 transition flex items-center shadow">
            <i class="fa-solid fa-calendar-days mr-2"></i>
            Manage
        </a>
    </div>
    
    <!-- Archive Confirmation Modal -->
    @if($showArchiveModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl border border-DEF4C6">
                <h3 class="text-lg font-semibold text-1B512D mb-4">Confirm Archive</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to archive this semester? 
                    It will be deactivated and moved to archived semesters.
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="closeModal" 
                            class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                        Cancel
                    </button>
                    <button wire:click="archiveSemester" 
                            class="px-4 py-2 rounded-full text-sm font-semibold bg-B1CF5F text-1B512D hover:bg-73E2A7 transition shadow">
                        Archive
                    </button>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Current Semester -->
    <div class="mb-8">
        <h4 class="text-sm font-semibold text-1B512D mb-3">Current Semester</h4>
        @if($currentSemester)
            <div class="relative group">
                <button 
                    wire:click="showSemesterFiles('{{ $currentSemester->id }}')"
                    class="w-full text-left flex items-center justify-between p-4 bg-DEF4C6/60 hover:bg-DEF4C6 rounded-xl border border-DEF4C6 transition shadow-sm"
                >
                    <div>
                        <p class="font-semibold text-1B512D">{{ $currentSemester->name }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $currentSemester->start_date->format('M d, Y') }} – 
                            {{ $currentSemester->end_date->format('M d, Y') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-73E2A7 text-1B512D shadow">
                        Active
                    </span>
                </button>
                
                <!-- 3-dot menu -->
                <div class="absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <div class="dropdown dropdown-left">
                        <button type="button" class="btn btn-xs btn-ghost btn-square rounded-full">
                            <i class="fa-solid fa-ellipsis-vertical text-1B512D"></i>
                        </button>
                        <ul class="dropdown-content menu bg-white border border-DEF4C6 rounded-xl w-36 p-2 shadow">
                            <li>
                                <button type="button" 
                                        wire:click="confirmArchive('{{ $currentSemester->id }}')"
                                        class="text-red-600 hover:bg-red-50 rounded-lg text-sm font-semibold">
                                    <i class="fa-solid fa-archive"></i>
                                    Archive
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500 italic">No active semester</p>
        @endif
    </div>
    
    <!-- Archived Semesters -->
    <div>
        <h4 class="text-sm font-semibold text-1B512D mb-3">Archived Semesters</h4>
        @if($archivedSemesters->count() > 0)
            <div class="space-y-2">
                @foreach($archivedSemesters as $archived)
                    <button 
                        wire:click="showSemesterFiles('{{ $archived->id }}')"
                        class="w-full text-left flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition shadow-sm"
                    >
                        <div>
                            <p class="font-semibold text-1B512D">{{ $archived->name }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $archived->start_date->format('M d, Y') }} – 
                                {{ $archived->end_date->format('M d, Y') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700 shadow">
                            Archived
                        </span>
                    </button>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 italic">No archived semesters</p>
        @endif
    </div>
</div>
