<div x-data="{ collapsed: false }" 
     :class="collapsed ? 'w-16' : 'w-64'" 
     class="flex flex-col bg-white rounded-xl shadow-sm h-full transition-all duration-300 ease-in-out"
     x-init="
        // Initialize collapsed state from localStorage if available
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            collapsed = true;
        }
        // Watch for changes and save to localStorage
        $watch('collapsed', value => localStorage.setItem('sidebar_collapsed', value));
     ">
    
    <!-- Header with Logo, Title and Hamburger -->
    <div class="flex items-center justify-between p-4 border-b border-gray-100">
        <!-- Expanded state - show logo with title -->
        <div class="flex items-center space-x-2 cursor-pointer" 
             x-show="!collapsed" 
             x-transition
             @click="collapsed = !collapsed">
            <!-- Use expanded logo -->
            <img src="{{ $logos['expanded'] }}" alt="iTrack Logo" class="w-40 h-8 object-contain">
        </div>
        
        <!-- Collapsed state - only show small logo centered -->
        <div class="flex items-center justify-center cursor-pointer" 
             x-show="collapsed" 
             x-transition
             @click="collapsed = !collapsed"
             :class="collapsed ? 'w-full' : ''">
            <!-- Use collapsed logo -->
            <img src="{{ $logos['collapsed'] }}" alt="iTrack Logo" class="w-8 h-8 object-contain">
        </div>
        
        <!-- Hamburger Menu Button - Only show when sidebar is expanded -->
        <button x-show="!collapsed"
                @click="collapsed = !collapsed" 
                type="button"
                class="p-1.5 rounded-md hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
            <svg class="w-5 h-5 text-gray-600 transition-transform duration-200" 
                 :class="{ 'rotate-180': collapsed }"
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <!-- Main Navigation Menu -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-1">
            @foreach ($navLinks as $index => $navlink)
                {{-- Handle separator --}}
                @if (isset($navlink['separator']) && $navlink['separator'])
                    <li class="py-2" x-show="!collapsed" x-transition>
                        <hr class="border-gray-200">
                    </li>
                    @continue
                @endif

                <li>
                    {{-- Regular navigation links --}}
                    <a href="{{ route($navlink['route']) }}"
                       class="relative flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition-colors duration-200 group {{ Route::currentRouteName() === $navlink['route'] ? 'bg-green-100 text-gray-900' : 'text-gray-700 hover:bg-gray-300 hover:text-gray-900' }}"
                       :class="collapsed ? 'justify-center space-x-0' : 'space-x-3'">
                        
                        <div class="relative flex-shrink-0">
                           <i class="fa-solid fa-{{ $navlink['icon'] }} w-5 h-5 text-center transition-colors duration-200"></i>
                        </div>
                        
                        <div class="flex-1 min-w-0" x-show="!collapsed" x-transition>
                            <span class="block truncate">{{ $navlink['label'] }}</span>
                        </div>

                        {{-- Badge for expanded state --}}
                        @if (isset($navlink['badge']) && $navlink['badge'])
                            <span x-show="!collapsed" x-transition
                                  class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 font-bold min-w-[20px] text-center">
                                {{ $navlink['badge'] > 99 ? '99+' : $navlink['badge'] }}
                            </span>
                            {{-- Badge dot for collapsed state --}}
                            <span x-show="collapsed" x-transition
                                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-3 h-3 flex items-center justify-center">
                            </span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <!-- Bottom Navigation (Profile & Logout) -->
    <div class="border-t border-gray-100 px-3 py-4">
        <ul class="space-y-1">
            @foreach ($bottomNavLinks as $navlink)
                {{-- Handle logout form --}}
                @if ($navlink['label'] === 'Logout')
                    <li>
                        <form method="POST" action="{{ route($navlink['route']) }}" class="w-full">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition-colors duration-200 group text-gray-700 hover:bg-gray-300 hover:text-gray-900"
                                    :class="collapsed ? 'justify-center space-x-0' : 'space-x-3'">
                                <i class="fa-solid fa-{{ $navlink['icon'] }} w-5 h-5 flex-shrink-0 text-center transition-colors duration-200"></i>
                                <span x-show="!collapsed" x-transition>{{ $navlink['label'] }}</span>
                            </button>
                        </form>
                    </li>
                @else
                    <li>
                        @if (isset($navlink['is_profile']) && $navlink['is_profile'])
                            {{-- Profile link with special styling --}}
                            <a href="{{ route($navlink['route']) }}"
                               class="relative flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition-colors duration-200 group {{ Route::currentRouteName() === $navlink['route'] ? 'bg-green-100 text-gray-900' : 'text-gray-700 hover:bg-gray-300 hover:text-gray-900' }}"
                               :class="collapsed ? 'justify-center space-x-0' : 'space-x-3'">
                                
                                <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                
                                <div class="flex-1 min-w-0" x-show="!collapsed" x-transition>
                                    <span class="block truncate font-medium">{{ $navlink['label'] }}</span>
                                </div>
                            </a>
                        @else
                            {{-- Regular navigation links --}}
                            <a href="{{ route($navlink['route']) }}"
                               class="relative flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition-colors duration-200 group {{ Route::currentRouteName() === $navlink['route'] ? 'bg-green-100 text-gray-900' : 'text-gray-700 hover:bg-gray-300 hover:text-gray-900' }}"
                               :class="collapsed ? 'justify-center space-x-0' : 'space-x-3'">
                                
                                <div class="relative flex-shrink-0">
                                    <i class="fa-solid fa-{{ $navlink['icon'] }} w-5 h-5 text-center transition-colors duration-200"></i>
                                </div>
                                
                                <div class="flex-1 min-w-0" x-show="!collapsed" x-transition>
                                    <span class="block truncate">{{ $navlink['label'] }}</span>
                                </div>
                            </a>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
    </div>

</div>

<style>
    /* Hide scrollbar completely for navigation */
    nav {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* Internet Explorer 10+ */
    }

    nav::-webkit-scrollbar {
        display: none; /* WebKit browsers (Chrome, Safari, Edge) */
    }
</style>