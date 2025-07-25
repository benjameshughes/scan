<?php

namespace App\Livewire\Profile;

use App\Enums\ThemeColor;
use Livewire\Attributes\Computed;
use Livewire\Component;

class UserSettings extends Component
{
    // User settings
    public bool $darkMode = false;
    public bool $autoSubmit = false;
    public bool $scanSound = true;
    public string $themeColor = 'blue';

    public function mount(): void
    {
        $user = auth()->user();
        $settings = $user->settings ?: [];
        
        $this->darkMode = $settings['dark_mode'] ?? false;
        $this->autoSubmit = $settings['auto_submit'] ?? false;
        $this->scanSound = $settings['scan_sound'] ?? true;
        $this->themeColor = $settings['theme_color'] ?? 'blue';
    }

    /**
     * Get available theme colors as computed property
     */
    #[Computed]
    public function themeColors(): \Illuminate\Support\Collection
    {
        return ThemeColor::collection();
    }

    /**
     * Update all settings in database
     */
    private function updateSettings(): void
    {
        $user = auth()->user();
        
        $settings = [
            'dark_mode' => $this->darkMode,
            'auto_submit' => $this->autoSubmit,
            'scan_sound' => $this->scanSound,
            'theme_color' => $this->themeColor,
        ];

        $user->update(['settings' => $settings]);
    }

    /**
     * Reset all settings to defaults
     */
    public function resetToDefaults(): void
    {
        $this->darkMode = false;
        $this->autoSubmit = false;
        $this->scanSound = true;
        $this->themeColor = 'blue';
        
        $this->updateSettings();
        
        $this->dispatch('theme-changed', darkMode: $this->darkMode);
        $this->dispatch('theme-color-changed', color: $this->themeColor);
    }

    /**
     * Handle live setting updates
     */
    public function updatedDarkMode($value): void
    {
        $this->updateSettings();
        $this->dispatch('theme-changed', darkMode: $value);
    }

    public function updatedAutoSubmit($value): void
    {
        $this->updateSettings();
    }

    public function updatedScanSound($value): void
    {
        $this->updateSettings();
    }

    public function updatedThemeColor($value): void
    {
        $this->updateSettings();
        $this->dispatch('theme-color-changed', color: $value);
    }


    public function render()
    {
        return view('livewire.profile.user-settings');
    }
}