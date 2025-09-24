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

        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/all.css" />
        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/sharp-solid.css" />
        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/sharp-regular.css" />
        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/sharp-light.css" />
        <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.0.0/css/duotone.css" />

        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>
