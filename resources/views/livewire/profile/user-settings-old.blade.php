<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public array $settings = [];
    public bool $saving = false;

    public function mount()
    {
        // This will use your accessor to get settings with defaults if needed
        $this->settings = Auth::user()->settings;
    }

    public function saveSettings()
    {
        $this->saving = true;
        
        $user = Auth::user();
        $user->update([
            'settings' => $this->settings
        ]);

        $this->dispatch('settings-updated');
        
        // Reset saving state after a brief moment
        $this->dispatch('setting-saved');
        $this->saving = false;
    }
    
    // Handle live dark mode updates
    public function updatedSettingsDarkMode($value)
    {
        $this->saveSettings();
        $this->dispatch('theme-changed', darkMode: $value);
    }
    
    // Handle live auto-submit updates
    public function updatedSettingsAutoSubmit($value)
    {
        $this->saveSettings();
    }
    
    // Handle live scan sound updates
    public function updatedSettingsScanSound($value)
    {
        $this->saveSettings();
    }
    
    // Handle live theme color updates
    public function updatedSettingsThemeColor($value)
    {
        $this->saveSettings();
    }
    
    // Generic handler for any settings property change
    public function updatedSettings($value, $name)
    {
        $this->saveSettings();
        
        // Special handling for dark mode
        if ($name === 'dark_mode') {
            $this->dispatch('theme-changed', darkMode: $value);
        }
    }
} ?>

<div class="w-full">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Application Settings</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Settings are saved automatically when changed</p>
                </div>
                
                <!-- Saving indicator -->
                <div wire:loading.delay.shortest wire:target="saveSettings" class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400">
                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-600 border-t-transparent dark:border-blue-400 dark:border-t-transparent"></div>
                    <span>Saving...</span>
                </div>
            </div>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">User Preferences</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600 transition-colors duration-200">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Dark Mode
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Use dark theme across the application
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Saved indicator for this setting -->
                                <div wire:loading.remove wire:target="updatedSettings,updatedSettingsDarkMode" class="text-green-600 dark:text-green-400 opacity-0 transition-opacity duration-300" 
                                     x-data="{ show: false }" 
                                     @setting-saved.window="show = true; setTimeout(() => show = false, 2000)"
                                     x-show="show" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-90">
                                    <flux:icon.check class="w-4 h-4" />
                                </div>
                                
                                <flux:switch wire:model.live="settings.dark_mode" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600 transition-colors duration-200">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Auto-Submit Scans
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Automatically submit scans without confirmation
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Saved indicator for this setting -->
                                <div wire:loading.remove wire:target="updatedSettings,updatedSettingsAutoSubmit" class="text-green-600 dark:text-green-400 opacity-0 transition-opacity duration-300" 
                                     x-data="{ show: false }" 
                                     @setting-saved.window="show = true; setTimeout(() => show = false, 2000)"
                                     x-show="show" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-90">
                                    <flux:icon.check class="w-4 h-4" />
                                </div>
                                
                                <flux:switch wire:model.live="settings.auto_submit" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600 transition-colors duration-200">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Scan Sound Effects
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Play sound when scanning barcodes
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Saved indicator for this setting -->
                                <div wire:loading.remove wire:target="updatedSettings,updatedSettingsScanSound" class="text-green-600 dark:text-green-400 opacity-0 transition-opacity duration-300" 
                                     x-data="{ show: false }" 
                                     @setting-saved.window="show = true; setTimeout(() => show = false, 2000)"
                                     x-show="show" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-90">
                                    <flux:icon.check class="w-4 h-4" />
                                </div>
                                
                                <flux:switch wire:model.live="settings.scan_sound" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600 transition-colors duration-200">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Theme Color
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Choose your preferred accent color
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Saved indicator for this setting -->
                                <div wire:loading.remove wire:target="updatedSettings,updatedSettingsThemeColor" class="text-green-600 dark:text-green-400 opacity-0 transition-opacity duration-300" 
                                     x-data="{ show: false }" 
                                     @setting-saved.window="show = true; setTimeout(() => show = false, 2000)"
                                     x-show="show" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-90">
                                    <flux:icon.check class="w-4 h-4" />
                                </div>
                                
                                <flux:select wire:model.live="settings.theme_color" class="w-32">
                                    <flux:option value="blue">Blue</flux:option>
                                    <flux:option value="green">Green</flux:option>
                                    <flux:option value="purple">Purple</flux:option>
                                    <flux:option value="red">Red</flux:option>
                                    <flux:option value="amber">Amber</flux:option>
                                    <flux:option value="teal">Teal</flux:option>
                                </flux:select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                        <div class="flex items-start gap-3">
                            <flux:icon.information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                            <div>
                                <h5 class="text-sm font-medium text-blue-900 dark:text-blue-200">About Notifications</h5>
                                <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                    System notifications (sync failures, empty bays, etc.) are automatically sent based on your user permissions and cannot be disabled. This ensures critical operational alerts reach the right people.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </div>
</div>
</div>