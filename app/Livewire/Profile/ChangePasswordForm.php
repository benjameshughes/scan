<?php

namespace App\Livewire\Profile;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePasswordForm extends Component
{
    use AuthorizesRequests;

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

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

        $this->dispatch('password-updated');
    }

    public function render()
    {
        return view('livewire.profile.change-password-form');
    }
}
