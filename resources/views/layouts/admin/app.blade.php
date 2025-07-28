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

        @livewireStyles
    </head>

    <body class="bg-gray-100">
        <div class="flex flex-col gap-4 p-6 container mx-auto min-h-screen">
            <x-admin.navigation />

            <!-- Page Content -->
            {{ $slot }}
        </div>

        <x-session-alert-messages />

        @livewireScripts
    </body>

</html>
