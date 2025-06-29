<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserProfile extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $email = '';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    // User settings
    public bool $notifications = false;

    public bool $darkMode = false;

    public bool $autoSubmit = false;

    public bool $scanSound = false;

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;

        // Load user settings
        $settings = $user->settings ?: [];
        $this->notifications = $settings['notifications'] ?? false;
        $this->darkMode = $settings['dark_mode'] ?? false;
        $this->autoSubmit = $settings['auto_submit'] ?? false;
        $this->scanSound = $settings['scan_sound'] ?? false;
    }

    public function updateProfile()
    {
        $user = auth()->user();
        $this->authorize('update', $user);

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('profile-message', 'Profile updated successfully.');
    }

    public function updatePassword()
    {
        $user = auth()->user();
        $this->authorize('update', $user);

        $this->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        if (! Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'Current password is incorrect.');

            return;
        }

        $user->update([
            'password' => Hash::make($this->newPassword),
        ]);

        // Clear password fields
        $this->currentPassword = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';

        session()->flash('password-message', 'Password updated successfully.');
    }

    public function updateSettings()
    {
        $user = auth()->user();
        $this->authorize('update', $user);

        $settings = [
            'notifications' => $this->notifications,
            'dark_mode' => $this->darkMode,
            'auto_submit' => $this->autoSubmit,
            'scan_sound' => $this->scanSound,
        ];

        $user->update([
            'settings' => json_encode($settings),
        ]);

        session()->flash('settings-message', 'Settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.user-profile', [
            'user' => auth()->user(),
            'userRoles' => auth()->user()->roles->pluck('name')->toArray(),
            'userPermissions' => auth()->user()->getAllPermissions()->pluck('name')->toArray(),
        ]);
    }
}
