<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Stock Scanner">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    @fluxAppearance
    @livewireStyles
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="font-sans antialiased"
    x-data
    x-init="$store.theme.init()"
    x-cloak
    @auth
    data-user-theme="{{ auth()->user()->settings['theme_color'] ?? 'blue' }}"
    @endauth
>
<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    @if(request()->routeIs('scan.*'))
        <!-- Scanner Layout -->
        @auth
            <livewire:welcome.navigation />
        @endauth
        
        <div class="min-h-screen flex flex-col md:justify-center items-center bg-gray-100 dark:bg-zinc-900">
            <div class="w-full sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    @else
        <!-- Standard App Layout -->
        <livewire:layout.navigation/>

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-zinc-800 shadow-sm border-b border-zinc-200 dark:border-zinc-700">
                <div class="max-w-7xl mx-auto py-6 px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif
        <!-- Standard App Layout -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="overflow-hidden">
                    {{ $slot }}
                </div>
            </div>
        </main>
    @endif
</div>

@livewireScripts
@fluxScripts


</body>
</html>
