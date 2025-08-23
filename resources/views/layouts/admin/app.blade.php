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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://kit.fontawesome.com/a10f8182c0.js" crossorigin="anonymous"></script>

        @livewireStyles
    </head>

    <body class="bg-gray-100">
        <!-- Fixed Navigation -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-gray-100">
            <x-admin.navigation />
        </div>

        <!-- Main Content -->
        <div class="pt-22 px-6 container mx-auto min-h-screen">
            {{ $slot }}
        </div>

        <x-session-alert-messages />

        @livewireScripts
        @livewire('notification-toast')
    </body>

</html>
