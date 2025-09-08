<div class="flex flex-col p-4 overflow-hidden bg-white rounded-lg">
    
    {{-- Progress Bar --}}
    @if($totalSubmissions > 0)
        <div class="w-full bg-gray-200 rounded-full h-4 mb-4 overflow-hidden">
            @foreach($statusPercentagesRaw as $status => $percentage)
                @if($percentage > 0)
                    <div 
                        class="h-4 float-left {{ $this->getStatusColor($status) }}"
                        style="width: {{ $percentage }}%"
                        title="{{ $this->getStatusLabel($status) }}: {{ $statusPercentages[$status] }}%"
                    ></div>
                @endif
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-3 text-xs">
            @foreach($statusPercentages as $status => $percentage)
                @if($statusPercentagesRaw[$status] > 0)
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full {{ $this->getStatusColor($status) }}"></span>
                        <span>{{ $this->getStatusLabel($status) }}: {{ $percentage }}% ({{ $statusCounts[$status] }})</span>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <i class="fa-solid fa-inbox text-4xl mb-2"></i>
            <p>No submissions yet</p>
        </div>
    @endif
</div>