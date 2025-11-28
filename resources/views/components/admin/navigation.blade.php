<nav class="shadow-sm sticky top-0 z-50 bg-white"
     x-data="{
        unreadCount: {{ $unreadCount }},
        mobileMenuOpen: false,
        showUserMenu: false
     }"
     @notification-read.window="if (unreadCount > 0) unreadCount--"
     @notifications-marked-read.window="unreadCount = 0"
     @notification-unread.window="unreadCount++"
     @notifications-marked-unread.window="unreadCount = $event.detail.count"
     @click.away="showUserMenu = false"
     @keydown.escape="showUserMenu = false; mobileMenuOpen = false">

    <div class="px-4 sm:px-6">
        {{-- Desktop Navigation --}}
        <div class="hidden lg:flex items-center justify-between py-4">
            {{-- Logo/Brand --}}
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-3 pr-6">
                    <img src="{{ asset('images/logo-title.png') }}" alt="iTrack Logo" class="w-auto h-8 object-contain" loading="lazy">
                </div>

                {{-- Main Navigation --}}
                <div class="flex items-center space-x-1">
                    @foreach ($navLinks['main'] as $navlink)
                        <div class="relative group"
                            x-data="{ isActive: {{ request()->routeIs($navlink['group'] ?? $navlink['route']) ? 'true' : 'false' }} }">
                            <a href="{{ route($navlink['route']) }}"
                            class="px-4 py-2 rounded-lg text-xs font-medium transition-all duration-200 flex items-center space-x-2 relative
                                    {{ request()->routeIs($navlink['group'] ?? $navlink['route'])
                                        ? 'bg-green-50 text-green-700 shadow-sm'
                                        : 'text-gray-700 hover:bg-gray-300 hover:text-gray-900' }}"
                            :aria-current="isActive ? 'page' : 'false'">
                                <i class="fa-solid fa-{{ $navlink['icon'] }} text-sm" aria-hidden="true"></i>
                                <span>{{ $navlink['label'] }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right side: Notifications, Admin Panel, User Menu --}}
            <div class="flex items-center space-x-2">
                {{-- Notifications --}}
                <a href="{{ route('admin.notifications') }}"
                   class="relative rounded-lg transition-colors duration-200 p-2
                          {{ request()->routeIs('admin.notifications')
                              ? 'bg-green-50 text-green-600'
                              : 'text-gray-600 hover:text-green-600' }}"
                   aria-label="Notifications">
                    <i class="fa-solid fa-bell text-xl transition-colors" aria-hidden="true"></i>
                    <span x-show="unreadCount > 0"
                          x-transition
                          class="absolute -top-2 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium shadow-sm"
                          x-text="unreadCount > 99 ? '99+' : unreadCount"
                          aria-live="polite">
                    </span>
                </a>

                {{-- User Menu --}}
                <div class="relative">
                    <button @click="showUserMenu = !showUserMenu"
                            class="flex items-center space-x-3 p-2 py-2 rounded-lg hover:bg-gray-300 hover:text-gray-900 transition-colors duration-200"
                            :aria-expanded="showUserMenu"
                            aria-label="User menu">
                        <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-user text-white text-xs" aria-hidden="true"></i>
                        </div>
                        <span class="text-gray-700 font-medium">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                           :class="{ 'rotate-180': showUserMenu }" aria-hidden="true"></i>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="showUserMenu"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                        @keydown.tab="if ($event.shiftKey) showUserMenu = false"
                        x-cloak>

                        <a href="{{ route('profile.edit') }}"
                        class="flex items-center space-x-3 px-3 py-2 m-1 rounded-md text-sm transition-colors duration-200
                                {{ request()->routeIs('profile.*')
                                    ? 'bg-green-50 text-green-700'
                                    : 'text-gray-700 hover:bg-gray-300 hover:text-gray-900' }}">
                            <i class="fa-solid fa-user-circle {{ request()->routeIs('profile.*')
                                ? 'text-green-600'
                                : 'text-gray-400' }}" aria-hidden="true">
                            </i>
                            <span>Your Profile</span>
                        </a>

                        <!-- Management link -->
                        <a href="{{ route('admin.management.index') }}"
                        class="flex items-center space-x-3 px-3 py-2 text-sm m-1 rounded-md transition-colors duration-200
                                {{ request()->routeIs('admin.management.*')
                                    ? 'bg-green-50 text-green-700'
                                    : 'text-gray-700 hover:bg-gray-300' }}">
                            <i class="fa-solid fa-gears {{ request()->routeIs('admin.management.*') ? 'text-green-600' : 'text-gray-400' }}" aria-hidden="true"></i>
                            <span>Management</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="px-1">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center space-x-3 px-3 py-2 text-sm text-gray-700 hover:bg-red-100 hover:text-red-700 rounded-md transition-colors duration-200 group">
                                <i class="fa-solid fa-right-from-bracket text-gray-400 group-hover:text-red-500" aria-hidden="true"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Navigation --}}
        <div class="lg:hidden">
            {{-- Mobile Header --}}
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/logo-title.png') }}" alt="iTrack Logo" class="w-8 h-8 object-contain" loading="lazy">
                    <span class="font-bold text-gray-900">iTrack</span>
                </div>

                <div class="flex items-center space-x-4">
                    {{-- Mobile Notification Button --}}
                    <a href="{{ route('admin.notifications') }}"
                       class="relative p-2 rounded-lg transition-colors
                              {{ request()->routeIs('admin.notifications')
                                  ? 'bg-green-50 text-green-600'
                                  : 'text-gray-600 hover:bg-gray-50' }}"
                       aria-label="Notifications">
                        <i class="fa-solid fa-bell" aria-hidden="true"></i>
                        <span x-show="unreadCount > 0"
                              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium"
                              x-text="unreadCount > 99 ? '99+' : unreadCount"
                              aria-live="polite">
                        </span>
                    </a>

                    {{-- Admin Panel Label --}}
                    <span class="text-gray-600 font-medium text-sm">Admin Panel</span>

                    {{-- Mobile Menu Button --}}
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="p-2 rounded-lg hover:bg-gray-50 transition-colors"
                            :aria-expanded="mobileMenuOpen"
                            aria-label="Toggle menu">
                        <i class="fa-solid text-gray-600 transition-transform duration-200"
                           :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            {{-- Mobile Menu --}}
            <div x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="border-t border-gray-200 py-4 space-y-2"
                x-cloak>

                @foreach ($navLinks['main'] as $navlink)
                    <a href="{{ route($navlink['route']) }}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 relative
                            {{ request()->routeIs($navlink['group'] ?? $navlink['route'])
                                ? 'bg-green-50 text-green-700'
                                : 'text-gray-700 hover:bg-gray-50' }}"
                    @click="mobileMenuOpen = false"
                    :aria-current="{{ request()->routeIs($navlink['group'] ?? $navlink['route']) ? "'page'" : 'false' }}">
                        <i class="fa-solid fa-{{ $navlink['icon'] }} text-sm w-5" aria-hidden="true"></i>
                        <span class="flex-1">{{ $navlink['label'] }}</span>

                        @if (request()->routeIs($navlink['group'] ?? $navlink['route']))
                            <i class="fa-solid fa-chevron-right text-green-600 text-sm" aria-hidden="true"></i>
                        @endif
                    </a>
                @endforeach

                <!-- Secondary navigation items for mobile -->
                @foreach ($navLinks['secondary'] as $navlink)
                    @if($navlink['route'] !== 'logout')
                        <a href="{{ route($navlink['route']) }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 relative
                                {{ request()->routeIs($navlink['group'] ?? $navlink['route'])
                                    ? 'bg-green-50 text-green-700'
                                    : 'text-gray-700 hover:bg-gray-50' }}"
                        @click="mobileMenuOpen = false"
                        :aria-current="{{ request()->routeIs($navlink['group'] ?? $navlink['route']) ? "'page'" : 'false' }}">
                            <i class="fa-solid fa-{{ $navlink['icon'] }} text-sm w-5" aria-hidden="true"></i>
                            <span class="flex-1">{{ $navlink['label'] }}</span>

                            @if (request()->routeIs($navlink['group'] ?? $navlink['route']))
                                <i class="fa-solid fa-chevron-right text-green-600 text-sm" aria-hidden="true"></i>
                            @endif

                            @if(isset($navlink['badge']) && $navlink['badge'] > 0)
                                <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                                    {{ $navlink['badge'] > 99 ? '99+' : $navlink['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endif
                @endforeach

                {{-- Mobile Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="mt-4 pt-4 border-t border-gray-200">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center space-x-3 px-4 py-3 text-red-700 hover:bg-red-50 rounded-lg transition-colors duration-200">
                        <i class="fa-solid fa-right-from-bracket text-sm w-5" aria-hidden="true"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- Loading overlay for better perceived performance --}}
<div x-data="{ loading: false }"
     @navigation:loading.window="loading = true"
     @navigation:loaded.window="loading = false"
     x-show="loading"
     class="fixed inset-0 bg-white/80 backdrop-blur-sm z-[60] flex items-center justify-center"
     x-cloak>
    <div class="flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-2 border-green-600 border-t-transparent"></div>
    </div>
</div>
