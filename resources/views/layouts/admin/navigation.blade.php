<nav class="bg-gray" x-data="{ unreadCount: {{ $unreadCount }} }" 
     @notification-read.window="if (unreadCount > 0) unreadCount--"
     @notifications-marked-read.window="unreadCount = 0">
    <div class="container mx-auto px-6 pt-6 pb-3">
        <ul class="w-full flex flex-row gap-2 items-center flex-wrap">
            @foreach ($navLinks as $index => $navlink)
                @if ($index === count($navLinks) - 3)
                    {{-- Spacer before the last two items --}}
                    <li class="flex-1"></li>
                @endif

                <li>
                    @if ($index === count($navLinks) - 1)
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-ghost capitalize flex items-center gap-2">
                                <i class="fa-solid fa-{{ $navlink['icon'] }}"></i>
                                <span class="hidden sm:inline">{{ $navlink['label'] }}</span>
                            </button>
                        </form>
                    @else
                        <a
                            href="{{ route($navlink['route']) }}"
                            class="btn btn-sm btn-ghost capitalize flex items-center gap-2 {{ request()->routeIs($navlink['route']) ? 'btn-active' : '' }} relative"
                        >
                            <i class="fa-solid fa-{{ $navlink['icon'] }}"></i>
                            <span class="hidden sm:inline">{{ $navlink['label'] }}</span>
                            
                            {{-- Notification badge --}}
                            @if (isset($navlink['badge']) && $navlink['badge'] > 0)
                                <span x-show="unreadCount > 0" 
                                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                                      x-text="unreadCount > 99 ? '99+' : unreadCount">
                                </span>
                            @endif
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</nav>