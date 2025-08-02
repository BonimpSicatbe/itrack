<div class="bg-white rounded-lg shadow p-4 h-full">
    <h3 class="text-lg font-bold mb-4">Semesters</h3>
    
    <!-- Current Semester -->
    <div class="mb-6">
        <h4 class="text-sm font-medium text-gray-500 mb-2">Current Semester</h4>
        @if($currentSemester)
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div>
                    <p class="font-medium">{{ $currentSemester->name }}</p>
                    <p class="text-sm text-gray-600">
                        {{ $currentSemester->start_date->format('M d, Y') }} - 
                        {{ $currentSemester->end_date->format('M d, Y') }}
                    </p>
                </div>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Active</span>
            </div>
        @else
            <p class="text-sm text-gray-500">No active semester</p>
        @endif
    </div>
    
    <!-- Previous Semester -->
    <div>
        <h4 class="text-sm font-medium text-gray-500 mb-2">Previous Semester</h4>
        @if($previousSemester)
            <button 
                wire:click="activateSemester('{{ $previousSemester->id }}')"
                class="w-full text-left flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition"
            >
                <div>
                    <p class="font-medium">{{ $previousSemester->name }}</p>
                    <p class="text-sm text-gray-600">
                        {{ $previousSemester->start_date->format('M d, Y') }} - 
                        {{ $previousSemester->end_date->format('M d, Y') }}
                    </p>
                </div>
                <span class="text-blue-600 hover:text-blue-800">
                    <i class="fa-solid fa-rotate-left"></i>
                </span>
            </button>
        @else
            <p class="text-sm text-gray-500">No previous semester</p>
        @endif
    </div>
</div>