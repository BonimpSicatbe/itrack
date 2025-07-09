<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 p-2">
    @foreach ($stats as $stat)
        <div class="flex flex-col p-3 rounded-lg shadow-sm hover:shadow transition-all duration-200 {{ $stat['bg_color'] }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500">{{ $stat['title'] }}</p>
                    <p class="text-lg font-bold mt-1">
                        @if(isset($stat['is_total']))
                            {{ $stat['desc'] }}MB
                        @else
                            {{ $stat['count'] }}
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $stat['desc'] }}MB</p>
                </div>
                <div class="p-2 rounded-md {{ $stat['bg_color'] }}">
                    <i class="fa-solid {{ $stat['icon'] }} {{ $stat['icon_color'] }}"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>