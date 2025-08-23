<div class="bg-white rounded-lg shadow p-4 h-full">
    <!-- Header with title and button on same line -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">Semesters</h3>
        <a href="{{ route('admin.semesters.index') }}" 
           class="btn btn-sm btn-primary">
            <i class="fa-solid fa-calendar-days mr-2"></i>
            Manage Semesters
        </a>
    </div>
    
    <!-- Archive Confirmation Modal -->
    @if($showArchiveModal)
        <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
                <h3 class="text-lg font-bold mb-4">Confirm Archive</h3>
                <p class="mb-6">Are you sure you want to archive this semester? This will deactivate it and move it to archived semesters.</p>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="closeModal" 
                            class="btn btn-ghost hover:bg-gray-100">
                        Cancel
                    </button>
                    <button wire:click="archiveSemester" 
                            class="btn btn-warning">
                        Archive Semester
                    </button>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Current Semester -->
    <div class="mb-6">
        <h4 class="text-sm font-medium text-gray-500 mb-2">Current Semester</h4>
        @if($currentSemester)
            <div class="relative group">
                <button 
                    wire:click="showSemesterFiles('{{ $currentSemester->id }}')"
                    class="w-full text-left flex items-center justify-between p-3 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition"
                >
                    <div>
                        <p class="font-medium">{{ $currentSemester->name }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $currentSemester->start_date->format('M d, Y') }} - 
                            {{ $currentSemester->end_date->format('M d, Y') }}
                        </p>
                    </div>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Active</span>
                </button>
                
                <!-- 3-dot menu -->
                <div class="absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <div class="dropdown dropdown-end">
                        <button type="button" class="btn btn-xs btn-ghost btn-square">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <ul class="dropdown-content menu bg-base-100 rounded-box w-32 p-2 shadow">
                            <li>
                                <button type="button" 
                                    wire:click="confirmArchive('{{ $currentSemester->id }}')"
                                    class="text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-archive"></i>
                                    Archive
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500">No active semester</p>
        @endif
    </div>
    
    <!-- Archived Semesters -->
    <div>
        <h4 class="text-sm font-medium text-gray-500 mb-2">Archived Semesters</h4>
        @if($archivedSemesters->count() > 0)
            <div class="space-y-2">
                @foreach($archivedSemesters as $archived)
                    <button 
                        wire:click="showSemesterFiles('{{ $archived->id }}')"
                        class="w-full text-left flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition"
                    >
                        <div>
                            <p class="font-medium">{{ $archived->name }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $archived->start_date->format('M d, Y') }} - 
                                {{ $archived->end_date->format('M d, Y') }}
                            </p>
                        </div>
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Archived</span>
                    </button>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">No archived semesters</p>
        @endif
    </div>
</div>