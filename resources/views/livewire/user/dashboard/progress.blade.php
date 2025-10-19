<div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-4">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-xl font-semibold text-gray-800">Submission Progress</h3>
        <div class="text-sm text-gray-500">
            {{ $totalSubmissions }} total submission{{ $totalSubmissions !== 1 ? 's' : '' }}
        </div>
    </div>

    @if($totalSubmissions > 0)
        {{-- Progress Bar --}}
        <div class="mb-6">
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden shadow-inner flex">
                @foreach($statusCounts as $status => $count)
                    @if($count > 0)
                        <div 
                            class="h-full {{ $this->getStatusColor($status) }}"
                            style="width: {{ $statusPercentages[$status] }}%"
                            title="{{ $this->getStatusLabel($status) }}: {{ $statusPercentages[$status] }}%"
                        ></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Status Breakdown --}}
        <div>
            <div class="grid grid-cols-3 gap-3">
                @foreach($statusCounts as $status => $count)
                    @if($count > 0)
                        <div class="flex items-center justify-between text-sm p-2 bg-gray-50 rounded">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full {{ $this->getStatusColor($status) }}"></span>
                                <span class="text-sm">{{ $this->getStatusLabel($status) }}</span>
                            </div>
                            <div class="text-gray-600">
                                {{ $count }} ({{ $statusPercentages[$status] }}%)
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-8">
            <div class="mx-auto flex items-center justify-center mb-4">
                <i class="fa-solid fa-folder-open text-gray-300 text-3xl"></i>
            </div>
            <h4 class="text-gray-500 font-semibold mb-2 text-sm">No submissions yet</h4>
            <p class="text-gray-500 text-xs">Your submission progress will appear here once you start submitting requirements.</p>
        </div>
    @endif
</div>