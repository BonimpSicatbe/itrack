<div class="bg-white rounded-lg shadow-sm p-4">
    <div class="flex divide-x divide-gray-200">
        @foreach ($stats as $stat)
            <div class="flex-1 px-4 first:pl-0 last:pr-0">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">{{ $stat['title'] }}</p>
                        <div class="mt-1">
                            @if(isset($stat['is_total']))
                                <p class="text-2xl font-bold text-gray-900">{{ $stat['desc'] }}MB</p>
                            @else
                                <p class="text-2xl font-bold text-gray-900">{{ $stat['count'] }}</p>
                                <p class="text-xs text-gray-500">{{ $stat['desc'] }}MB</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-center w-8 h-8">
                        <i class="fa-solid {{ $stat['icon'] }} {{ $stat['icon_color'] }} text-xl" style="vertical-align: middle; line-height: 1;"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>