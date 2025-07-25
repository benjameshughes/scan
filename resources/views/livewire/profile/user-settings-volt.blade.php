<div class="w-full" 
     x-data="userSettings" 
     @auto-clear-success.window="setTimeout(() => $wire.clearSuccessState($event.detail.key), $event.detail.delay)"
     @theme-changed.window="applyTheme($event.detail.darkMode)"
     @theme-color-changed.window="applyThemeColor($event.detail.color)">
     
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Application Settings</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Settings are saved automatically when changed</p>
                </div>
                
                <!-- Global Actions -->
                <div class="flex items-center gap-3">
                    <!-- Export Settings -->
                    <flux:button 
                        variant="ghost" 
                        size="sm" 
                        wire:click="exportSettings"
                        icon="arrow-down-tray">
                        Export
                    </flux:button>
                    
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
            <x-settings.setting-section 
                title="User Preferences" 
                description="Customize your application experience with these personal preference settings.">
                
                @foreach($this->settingDefinitions as $definition)
                    @php
                        $key = $definition->key;
                        $value = $this->settings->get($key);
                        $isLoading = $this->isLoading($key);
                        $hasError = $this->hasError($key);
                        $hasSuccess = $this->hasSuccess($key);
                        $error = $this->getError($key);
                    @endphp
                    
                    @switch($definition->type->value)
                        @case('toggle')
                            <x-settings.setting-toggle
                                :setting="$definition"
                                :value="$value"
                                :is-loading="$isLoading"
                                :has-error="$hasError"
                                :has-success="$hasSuccess"
                                :error="$error"
                                wire:model.live="settings.{{ $key }}"
                            />
                            @break
                            
                        @case('select')
                            <x-settings.setting-select
                                :setting="$definition"
                                :value="$value"
                                :options="$definition->options->toArray()"
                                :is-loading="$isLoading"
                                :has-error="$hasError"
                                :has-success="$hasSuccess"
                                :error="$error"
                                wire:model.live="settings.{{ $key }}"
                            />
                            @break
                            
                        @case('text')
                            <x-settings.setting-text
                                :setting="$definition"
                                :value="$value"
                                :is-loading="$isLoading"
                                :has-error="$hasError"
                                :has-success="$hasSuccess"
                                :error="$error"
                                wire:model.live="settings.{{ $key }}"
                            />
                            @break
                            
                        @case('number')
                            <x-settings.setting-number
                                :setting="$definition"
                                :value="$value"
                                :is-loading="$isLoading"
                                :has-error="$hasError"
                                :has-success="$hasSuccess"
                                :error="$error"
                                wire:model.live="settings.{{ $key }}"
                            />
                            @break
                    @endswitch
                @endforeach
            </x-settings.setting-section>

            <!-- Settings Summary -->
            @if($this->hasModifiedSettings)
                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                    <div class="flex items-start gap-3">
                        <flux:icon.information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <h5 class="text-sm font-medium text-blue-900 dark:text-blue-200">Settings Modified</h5>
                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                You have modified {{ $this->modifiedSettings->count() }} setting(s) from their defaults.
                                
                                @if($this->settingDefinitions->filter(fn($def) => $this->modifiedSettings->has($def->key) && $def->requiresReload)->isNotEmpty())
                                    <strong>Some changes may require a page refresh to take full effect.</strong>
                                @endif
                            </p>
                            
                            <!-- Modified Settings List -->
                            <div class="mt-2">
                                <details class="group">
                                    <summary class="text-xs text-blue-600 dark:text-blue-400 cursor-pointer hover:text-blue-700 dark:hover:text-blue-300">
                                        View modified settings
                                    </summary>
                                    <div class="mt-2 space-y-1">
                                        @foreach($this->modifiedSettings as $key => $value)
                                            @php $definition = $this->settingDefinitions->firstWhere('key', $key); @endphp
                                            <div class="text-xs text-blue-700 dark:text-blue-300">
                                                <strong>{{ $definition?->label ?? $key }}:</strong> 
                                                @if(is_bool($value))
                                                    {{ $value ? 'Enabled' : 'Disabled' }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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
                            <p>• You can export your settings as a backup or import them on another device</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('userSettings', () => ({
        init() {
            // Initialize theme on component load
            this.applyTheme(this.$wire.settings.dark_mode);
            this.applyThemeColor(this.$wire.settings.theme_color);
        },
        
        applyTheme(darkMode) {
            if (darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            // Store preference in localStorage
            localStorage.setItem('theme', darkMode ? 'dark' : 'light');
        },
        
        applyThemeColor(color) {
            // Remove existing theme color classes
            document.documentElement.classList.remove(
                '--theme-blue', '--theme-green', '--theme-purple', 
                '--theme-red', '--theme-amber', '--theme-teal'
            );
            
            // Add new theme color class
            document.documentElement.classList.add(`--theme-${color}`);
            
            // Store preference in localStorage
            localStorage.setItem('themeColor', color);
        }
    }));
});

// Handle downloads
window.addEventListener('download-settings', event => {
    const blob = new Blob([event.detail.content], { type: 'application/json' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = event.detail.filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
});

// Handle page reload requirement
window.addEventListener('page-reload', () => {
    setTimeout(() => {
        if (confirm('Some settings require a page refresh to take effect. Reload now?')) {
            window.location.reload();
        }
    }, 1000);
});
</script>