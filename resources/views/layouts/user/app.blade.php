<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme='light'>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://kit.fontawesome.com/a10f8182c0.js" crossorigin="anonymous"></script>
    
    <!-- Alpine.js Core and Persist Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
    <style>
        body {
            background: linear-gradient(to top, #a8e6cf 40%, transparent 90%);
            background-attachment: fixed;
            min-height: 100vh;
        }
    </style>
</head>
<body wire:poll.500ms 
      class="flex font-sans antialiased w-screen h-screen">
    <div class="flex flex-row gap-3 p-4 w-full h-full">
        <!-- Sidebar Navigation -->
        <div class="flex-shrink-0">
            <x-user.navigation />
        </div>
        <!-- Page Content -->
        <div class="flex-1 h-full overflow-auto transition-all duration-300 ease-in-out text-gray-800">
            {{ $slot }}
        </div>
    </div>
    <x-session-alert-messages />
    @livewireScripts
    
    <!-- Custom Scripts for Enhanced Functionality -->
    <script>
        document.addEventListener('alpine:init', () => {
            // Handle responsive behavior
            function handleResize() {
                if (window.innerWidth < 768) {
                    // On mobile, auto-collapse sidebar
                    const sidebarElement = document.querySelector('[x-data*="collapsed"]');
                    if (sidebarElement && sidebarElement._x_dataStack) {
                        sidebarElement._x_dataStack[0].collapsed = true;
                    }
                }
            }
            
            window.addEventListener('resize', handleResize);
            handleResize(); // Check on load
            
            // Keyboard shortcut to toggle sidebar (Ctrl+B or Cmd+B)
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                    e.preventDefault();
                    const sidebarElement = document.querySelector('[x-data*="collapsed"]');
                    if (sidebarElement && sidebarElement._x_dataStack) {
                        const data = sidebarElement._x_dataStack[0];
                        data.collapsed = !data.collapsed;
                    }
                }
            });
        });
    </script>
</body>
</html>