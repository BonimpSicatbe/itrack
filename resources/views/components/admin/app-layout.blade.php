<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme='light'>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- Fonts -->
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/all.css" />
        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/sharp-solid.css" />
        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/sharp-regular.css" />
        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/sharp-light.css" />
        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/duotone.css" />

        @livewireStyles

        <style>
            .papercut-background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                overflow: hidden;
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            }

            .wave-layer {
                position: absolute;
                width: 500%;
                height: 100vh;
                opacity: 0.9;
                bottom: 0;
            }

            .wave-layer:nth-child(1) {
                background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 50%, #34d399 100%);
                clip-path: path('M-800,200 C-650,150 -500,250 -350,200 C-200,150 -50,250 100,200 C250,150 400,250 550,200 C700,150 850,250 1000,200 C1150,150 1300,250 1450,200 C1600,150 1750,250 1900,200 C2050,150 2200,250 2350,200 C2500,150 2650,250 2800,200 C2950,150 3100,250 3250,200 C3400,150 3550,250 3700,200 C3850,150 4000,250 4150,200 L4150,1200 L-800,1200 Z');
                height: 60vh;
                animation: slideBackForth1 25s linear infinite;
            }

            .wave-layer:nth-child(2) {
                background: linear-gradient(135deg, #86efac 0%, #4ade80 50%, #22c55e 100%);
                clip-path: path('M-800,100 C-600,20 -400,180 -200,100 C0,20 200,180 400,100 C600,20 800,180 1000,100 C1200,20 1400,180 1600,100 C1800,20 2000,180 2200,100 C2400,20 2600,180 2800,100 C3000,20 3200,180 3400,100 C3600,20 3800,180 4000,100 C4200,20 4400,180 4600,100 L4600,1200 L-800,1200 Z');
                height: 42vh;
                animation: slideBackForth2 15s linear infinite;
            }

            .wave-layer:nth-child(3) {
                background: linear-gradient(135deg, #4ade80 0%, #22c55e 50%, #16a34a 100%);
                clip-path: path('M-800,150 C-650,100 -500,200 -350,150 C-200,100 -50,200 100,150 C250,100 400,200 550,150 C700,100 850,200 1000,150 C1150,100 1300,200 1450,150 C1600,100 1750,200 1900,150 C2050,100 2200,200 2350,150 C2500,100 2650,200 2800,150 C2950,100 3100,200 3250,150 C3400,100 3550,200 3700,150 C3850,100 4000,200 4150,150 L4150,1200 L-800,1200 Z');
                height: 38vh;
                animation: slideBackForth3 20s linear infinite;
            }

            .wave-layer:nth-child(4) {
                background: linear-gradient(135deg, #22c55e 0%, #15803d 50%, #166534 100%);
                clip-path: path('M-800,80 C-650,40 -500,120 -350,80 C-200,40 -50,120 100,80 C250,40 400,120 550,80 C700,40 850,120 1000,80 C1150,40 1300,120 1450,80 C1600,40 1750,120 1900,80 C2050,40 2200,120 2350,80 C2500,40 2650,120 2800,80 C2950,40 3100,120 3250,80 C3400,40 3550,120 3700,80 C3850,40 4000,120 4150,80 C4300,40 4450,120 4600,80 L4600,1200 L-800,1200 Z');
                height: 26vh;
                animation: slideBackForth4 10s linear infinite;
            }

            .wave-layer:nth-child(5) {
                background: linear-gradient(135deg, #16a34a 0%, #166534 50%, #14532d 100%);
                clip-path: path('M-800,250 C-400,50 0,450 400,250 C800,50 1200,450 1600,250 C2000,50 2400,450 2800,250 C3200,50 3600,450 4000,250 L4000,1200 L-800,1200 Z');
                height: 40vh;
                animation: slideBackForth5 10s linear infinite;
            }

            /* New back-and-forth animations */
            @keyframes slideBackForth1 {
                0% {
                    transform: translateX(0);
                }

                50% {
                    transform: translateX(-800px);
                }

                100% {
                    transform: translateX(0);
                }
            }

            @keyframes slideBackForth2 {
                0% {
                    transform: translateX(-800px);
                }

                50% {
                    transform: translateX(0);
                }

                100% {
                    transform: translateX(-800px);
                }
            }

            @keyframes slideBackForth3 {
                0% {
                    transform: translateX(0);
                }

                50% {
                    transform: translateX(-600px);
                }

                100% {
                    transform: translateX(0);
                }
            }

            @keyframes slideBackForth4 {
                0% {
                    transform: translateX(-600px);
                }

                50% {
                    transform: translateX(0);
                }

                100% {
                    transform: translateX(-600px);
                }
            }

            @keyframes slideBackForth5 {
                0% {
                    transform: translateX(0);
                }

                50% {
                    transform: translateX(-400px);
                }

                100% {
                    transform: translateX(0);
                }
            }

            /* Subtle texture overlay */
            .papercut-background::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
                z-index: 1;
            }
        </style>
    </head>

    <body>
        <!-- <body class="bg-[#F5F5F5]"> -->
        <!-- Papercut Background -->
        <div class="papercut-background">
            <div class="wave-layer"></div>
            <div class="wave-layer"></div>
            <div class="wave-layer"></div>
            <div class="wave-layer"></div>
            <div class="wave-layer"></div>
        </div>

        <!-- Fixed Navigation -->
        <div class="fixed top-0 left-0 right-0 z-50">
            <div class="bg-white/80 backdrop-blur-md border-b border-white/20">
                <x-admin.navigation :unreadCount="$unreadCount" />
            </div>
        </div>

        <!-- Main Content -->
        <div class="pt-22 px-6 container mx-auto min-h-screen">
            <div class="content-wrapper w-[92%] mx-auto">
                {{ $slot }}
            </div>
        </div>

        <x-session-alert-messages />
        @livewireScripts
        @livewire('notification-toast')
    </body>

</html>
