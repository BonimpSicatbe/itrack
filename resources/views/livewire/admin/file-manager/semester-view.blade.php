<div class="bg-white rounded-xl shadow p-3 h-full"> 
    <!-- Header -->
    <div class="flex justify-between items-center mb-6 p-4 rounded-xl text-white shadow"
         style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-2 rounded-xl">
                <i class="fa-solid fa-calendar-days text-white text-2xl"></i>
            </div>
            <h2 class="text-xl md:text-xl font-semibold">Semester</h2>
        </div>
        <a href="{{ route('admin.management.index', ['tab' => 'semesters']) }}" 
           class="px-4 py-2 rounded-xl bg-white text-1C7C54 text-sm font-semibold hover:bg-gray-100 transition flex items-center shadow">
            Manage
            <i class="fa-solid fa-arrow-up-right-from-square ml-2"></i>
        </a>
    </div>
    
    <!-- Current Semester -->
    <div class="mb-8">
        <h4 class="text-sm font-semibold text-1B512D mb-3">Current Semester</h4>
        @if($currentSemester)
            <div class="relative group">
                <button 
                    wire:click="showSemesterFiles('{{ $currentSemester->id }}')"
                    class="w-full text-left flex items-center justify-between p-4 rounded-xl border-2 border-green-600 transition shadow-sm"
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
            </div>
        @else
            <p class="text-sm text-gray-500 italic">No active semester</p>
        @endif
    </div>
    
    <!-- Previous Semesters -->
    <div>
        <h4 class="text-sm font-semibold text-1B512D mb-3">Previous Semesters</h4>
        @if($previousSemesters->count() > 0)
            <div class="space-y-2">
                @foreach($previousSemesters as $previous)
                    <button 
                        wire:click="showSemesterFiles('{{ $previous->id }}')"
                        class="w-full text-left flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition shadow-sm"
                    >
                        <div>
                            <p class="font-semibold text-1B512D">{{ $previous->name }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $previous->start_date->format('M d, Y') }} – 
                                {{ $previous->end_date->format('M d, Y') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700 shadow">
                            Inactive
                        </span>
                    </button>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 italic">No previous semesters</p>
        @endif
    </div>
</div>
