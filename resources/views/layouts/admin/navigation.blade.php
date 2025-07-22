<ul class="w-full flex flex-row gap-2 items-center flex-wrap">
    @foreach ($navLinks as $index => $navlink)
        @if ($index === count($navLinks) - 2)
            {{-- Spacer before the last two items --}}
            <li class="flex-1"></li>
        @endif

        <li>
            @if ($index === count($navLinks) - 1)
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-ghost capitalize">
                        <i class="fa-solid fa-{{ $navlink['icon'] }}"></i>
                        <span>{{ $navlink['label'] }}</span>
                    </button>
                </form>
            @else
                <a
                    href="{{ route($navlink['route']) }}"
                    class="btn btn-sm btn-ghost capitalize {{ request()->routeIs($navlink['route']) ? 'btn-active' : '' }}"
                >
                    <i class="fa-solid fa-{{ $navlink['icon'] }}"></i>
                    <span>{{ $navlink['label'] }}</span>
                </a>
            @endif
        </li>
    @endforeach
</ul>
