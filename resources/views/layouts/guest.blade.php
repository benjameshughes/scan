<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @fluxAppearance

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
    @auth
        <livewire:welcome.navigation />
    @endauth
        <div class="min-h-screen flex flex-col justify-center items-center bg-zinc-50 dark:bg-zinc-900">
{{--            <div>--}}
{{--                <a href="/" wire:navigate>--}}
{{--                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />--}}
{{--                </a>--}}
{{--            </div>--}}

            <div class="w-full sm:max-w-md bg-white dark:bg-zinc-800 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        @fluxScripts
    </body>
</html>
