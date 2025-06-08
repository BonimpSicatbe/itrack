<div wire:poll.2s class="w-full bg-white stats stats-vertical sm:stats-horizontal shadow">
    @foreach ($stats as $stat)
        <div class="stat">
            <div class="stat-figure text-2xl">
                <i class="fa-solid {{ $stat['icon'] }} text-{{ $stat['color'] }}"></i>
            </div>
            <div class="stat-title">
                <span>{{ $stat['title'] }}</span>
            </div>
            <div class="stat-value text-{{ $stat['color'] }}">{{ $stat['count'] }}</div>
            <div class="stat-desc lowercase">
                @if ($stat['desc'] >= 1024)
                    {{ number_format($stat['desc'] / 1024, 2) }} GB
                @else
                    {{ $stat['desc'] }} MB
                @endif
                of
                @if ($totalFiles >= 1024)
                    {{ number_format($totalFiles / 1024, 2) }} GB
                @else
                    {{ $totalFiles }} MB
                @endif
            </div>
        </div>
    @endforeach
</div>
