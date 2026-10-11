<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/jpeg" href="{{ asset('images/philrice-emblem-small.jpg') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="guest-page">
            <div class="guest-shell">
                <a class="guest-brand" href="/">
                    <img class="guest-brand-mark" src="{{ asset('images/philrice-emblem-small.jpg') }}" alt="PhilRice logo">
                    Fertilizer Subsidy
                </a>
                <div class="guest-card">
                {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
