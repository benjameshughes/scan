<div class="w-full" 
     x-data="{
        init() {
            // Initialize theme store with user's theme color if different
            const userThemeColor = this.$wire.themeColor;
            if (userThemeColor && userThemeColor !== this.$store.theme.get()) {
                this.$store.theme.set(userThemeColor);
            }
        },
        
        applyTheme(darkMode) {
            if (darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('theme', darkMode ? 'dark' : 'light');
        }
     }"
     @theme-changed.window="if($event.detail.darkMode !== undefined) applyTheme($event.detail.darkMode)"
     @theme-color-changed.window="$store.theme.set($event.detail.color)">
     
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Application Settings</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Settings are saved automatically when changed</p>
                </div>
                
                <!-- Global Actions -->
                <div class="flex items-center gap-3">
                    <!-- Reset to Defaults -->
                    <flux:button 
                        variant="ghost" 
                        size="sm" 
                        wire:click="resetToDefaults"
                        wire:confirm="Are you sure you want to reset all settings to defaults?"
                        icon="arrow-path">
                        Reset All
                    </flux:button>
                    
                    <!-- Global Saving Indicator -->
                    <div wire:loading.delay.shortest class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400">
                        <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-600 border-t-transparent dark:border-blue-400 dark:border-t-transparent"></div>
                        <span>Saving...</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- User Preferences Section -->
            <div>
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">User Preferences</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Customize your application experience with these personal preference settings.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <!-- Dark Mode Setting -->
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                    Dark Mode
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Use dark theme across the application
                                </p>
                            </div>
                            <flux:switch wire:model.live="darkMode" />
                        </div>

                        <!-- Auto-Submit Setting -->
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600 opacity-50">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Auto-Submit Scans
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Coming soon - automatically submit scans without confirmation
                                </p>
                            </div>
                            <flux:switch wire:model.live="autoSubmit" disabled />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Scan Sound Setting -->
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                    Scan Sound Effects
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Play sound when scanning barcodes
                                </p>
                            </div>
                            <flux:switch wire:model.live="scanSound" />
                        </div>

                        <!-- Theme Color Setting -->
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                            <div class="mb-3">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Theme Color
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Personalize your interface with your favorite color
                                </p>
                            </div>
                            <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3">
                                @foreach($this->themeColors as $colorEnum)
                                    <label class="cursor-pointer" title="{{ $colorEnum->label() }} - {{ $colorEnum->description() }}">
                                        <input 
                                            type="radio" 
                                            name="themeColor" 
                                            value="{{ $colorEnum->value }}" 
                                            wire:model.live="themeColor"
                                            class="sr-only"
                                        />
                                        <div class="w-10 h-10 rounded-lg border-2 transition-all duration-200 flex items-center justify-center {{ $colorEnum->backgroundClass() }} border-transparent
                                            {{ $themeColor === $colorEnum->value ? 'ring-2 ring-offset-2 ring-zinc-400 dark:ring-zinc-500 dark:ring-offset-zinc-800 scale-110' : 'hover:scale-105' }}
                                        ">
                                            @if($themeColor === $colorEnum->value)
                                                <flux:icon.check class="w-5 h-5 text-white" />
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Notifications Info -->
                <div class="mt-6 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                    <div class="flex items-start gap-3">
                        <flux:icon.information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                        <div>
                            <h5 class="text-sm font-medium text-blue-900 dark:text-blue-200">System Notifications</h5>
                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                Critical system notifications are automatically enabled based on your user permissions and cannot be disabled.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Information -->
            <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-md">
                <div class="flex items-start gap-3">
                    <flux:icon.question-mark-circle class="w-5 h-5 text-gray-600 dark:text-gray-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 dark:text-gray-200">About Settings</h5>
                        <div class="text-xs text-gray-700 dark:text-gray-300 mt-1 space-y-1">
                            <p>• All settings are saved automatically when you make changes</p>
                            <p>• Your preferences are synchronized across all your devices</p>
                            <p>• System notifications are automatically sent based on your user permissions and cannot be disabled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>