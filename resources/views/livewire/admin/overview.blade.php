<div class="stats stats-vertical sm:stats-horizontal shadow text-white rounded-xl" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
    @foreach ($stats as $stat)
        <div class="stat">
            <div class="stat-figure text-2xl">
                <i class="fa-solid {{ $stat['icon'] }} text-white"></i>
            </div>
            <div class="stat-title text-white">{{ $stat['title'] }}</div>
            <div class="stat-value text-white">{{ $stat['count'] }}</div>
            @isset($stat['description'])
                <div class="stat-desc text-white/80">{{ $stat['description'] }}</div>
            @endisset
        </div>
    @endforeach
</div>