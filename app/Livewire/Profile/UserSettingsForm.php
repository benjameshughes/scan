<?php

namespace App\Livewire\Profile;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class UserSettingsForm extends Component
{
    use AuthorizesRequests;

    // User settings
    public bool $autoSubmit = false;

    public bool $scanSound = true;

    public string $vibrationPattern = 'medium';

    public string $themeColor = 'blue';

    public function mount()
    {
        $user = auth()->user();

        // Load user settings
        $settings = $user->settings ?: [];
        $this->autoSubmit = $settings['auto_submit'] ?? false;
        $this->scanSound = $settings['scan_sound'] ?? true;
        $this->vibrationPattern = $settings['vibration_pattern'] ?? 'medium';
        $this->themeColor = $settings['theme_color'] ?? 'blue';
    }

    public function updateSettings()
    {
        $user = auth()->user();
        $this->authorize('update', $user);

        $settings = [
            'auto_submit' => $this->autoSubmit,
            'scan_sound' => $this->scanSound,
            'vibration_pattern' => $this->vibrationPattern,
            'theme_color' => $this->themeColor,
        ];

        try {
            $user->update([
                'settings' => $settings,
            ]);

            $this->dispatch('settings-updated');
        } catch (\Exception $e) {
            // For errors, we could add a separate error event if needed
            // For now, just silently fail - user will see no update indicator
        }
    }

    // Handle live auto-submit updates
    public function updatedAutoSubmit($value)
    {
        $this->updateSettings();
    }

    // Handle live scan sound updates
    public function updatedScanSound($value)
    {
        $this->updateSettings();
    }

    // Handle live theme color updates
    public function updatedThemeColor($value)
    {
        $this->updateSettings();
        $this->dispatch('theme-color-changed', color: $value);
    }

    // Handle live vibration pattern updates
    public function updatedVibrationPattern($value)
    {
        $this->updateSettings();
    }

    public function render()
    {
        return view('livewire.profile.user-settings-form');
    }
}
