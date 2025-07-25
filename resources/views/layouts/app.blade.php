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
    
    <!-- Include product scanner JS globally for PWA -->
    @vite('resources/js/product-scanner.js')
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <livewire:layout.navigation/>

    <!-- Page Heading -->
    @if (isset($header))
        <header class="bg-white dark:bg-zinc-800 shadow-sm border-b border-zinc-200 dark:border-zinc-700">
            <div class="max-w-7xl mx-auto py-6 px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <!-- Page Content -->
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </main>
</div>

@livewireScripts
@fluxScripts

@auth
<script>
// Accent color management similar to Flux dark mode
window.StockScan = window.StockScan || {};

window.StockScan.applyThemeColor = function(color) {
    // Remove all theme classes
    const themeClasses = ['theme-blue', 'theme-green', 'theme-purple', 'theme-orange', 'theme-red', 
                         'theme-pink', 'theme-indigo', 'theme-teal', 'theme-emerald', 'theme-amber', 'theme-rose'];
    document.documentElement.classList.remove(...themeClasses);
    
    // Add new theme class (blue is default, no class needed)
    if (color && color !== 'blue') {
        document.documentElement.classList.add('theme-' + color);
    }
    
    // Store in localStorage for persistence
    localStorage.setItem('stockscan.theme-color', color);
};

window.StockScan.getStoredThemeColor = function() {
    return localStorage.getItem('stockscan.theme-color') || @json(auth()->user()->settings['theme_color'] ?? 'blue');
};

// Apply on initial load
document.addEventListener('DOMContentLoaded', function() {
    const themeColor = window.StockScan.getStoredThemeColor();
    window.StockScan.applyThemeColor(themeColor);
});

// Re-apply on Livewire navigate (like Flux does for dark mode)
document.addEventListener('livewire:navigated', function() {
    const themeColor = window.StockScan.getStoredThemeColor();
    window.StockScan.applyThemeColor(themeColor);
});
</script>
@endauth

</body>
</html>
