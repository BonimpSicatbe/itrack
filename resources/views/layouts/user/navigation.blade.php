<ul class="menu bg-white rounded-box w-56 min-w-[20%] h-full">
    <li class="menu-title">iTrack</li>
    @foreach ($navLinks as $index => $navlink)
        @if ($index === count($navLinks) - 2)
            <li class="flex-1 bg-white"></li>
        @endif

        @if ($navlink['label'] === 'Logout')
            <form method="POST" action="{{ route($navlink['route']) }}">
                @csrf
                <li class="">
                    <button type="submit"
                        class="w-full text-left {{ Route::currentRouteName() === $navlink['route'] ? 'menu-active' : '' }}">
                        <i class="fa-solid fa-{{ $navlink['icon'] }} min-w-[20px] text-center"></i>
                        {{ $navlink['label'] }}
                    </button>
                </li>
            </form>
        @else
            <li>
                <a href="{{ route($navlink['route']) }}"
                    class="{{ Route::currentRouteName() === $navlink['route'] ? 'menu-active' : '' }}">
                    <i class="fa-solid fa-{{ $navlink['icon'] }} min-w-[20px] text-center"></i>
                    {{ $navlink['label'] }}
                </a>
            </li>
        @endif
    @endforeach
</ul>
