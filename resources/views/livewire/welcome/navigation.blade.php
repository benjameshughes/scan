<div class="relative w-full bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 shadow-sm">
    <nav class="max-w-7xl mx-auto px-4 py-3">
        <div class="flex items-center justify-between">
            <!-- Left side - Greeting -->
            <div class="flex items-center space-x-3">
                @auth
                    <div class="flex items-center space-x-2">
                        <div 
                            x-data="{ currentTheme: '{{ auth()->user()->settings['theme_color'] ?? 'blue' }}' }"
                            @theme-color-changed.window="currentTheme = $event.detail.color"
                            :class="`w-8 h-8 rounded-full flex items-center justify-center bg-${currentTheme}-600`"
                        >
                            <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ now()->hour < 12 ? 'Good Morning' : (now()->hour < 18 ? 'Good Afternoon' : 'Good Evening') }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                @endauth
            </div>

            <!-- Right side - Actions -->
            <div class="flex items-center space-x-2">
                <!-- Dark mode toggle -->
                <flux:button 
                    x-data 
                    x-on:click="$flux.dark = ! $flux.dark" 
                    icon="moon" 
                    variant="ghost"
                    size="sm"
                    aria-label="Toggle dark mode"
                />
                
                <!-- Dashboard link -->
                <flux:button 
                    href="{{ route('dashboard') }}"
                    variant="primary"
                    size="sm"
                    icon="home"
                >
                    Dashboard
                </flux:button>
            </div>
        </div>
    </nav>
</div>