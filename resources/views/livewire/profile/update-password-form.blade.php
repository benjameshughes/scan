<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::min(6)->max(255)->letters()->mixedCase(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form wire:submit="updatePassword" class="mt-6 space-y-4">
        <div>
            <flux:input 
                wire:model="current_password" 
                label="{{ __('Current Password') }}" 
                id="update_password_current_password" 
                name="current_password" 
                type="password" 
                placeholder="{{ __('Enter your current password') }}"
                autocomplete="current-password" 
                required
                class="w-full"
            />
            <flux:error name="current_password"/>
        </div>

        <div>
            <flux:input 
                wire:model="password" 
                id="update_password_password" 
                name="password" 
                type="password" 
                label="{{ __('New Password') }}" 
                placeholder="{{ __('Create a new strong password') }}"
                autocomplete="new-password"
                required
                class="w-full"
            />
            <flux:error name="password"/>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ __('Must be at least 6 characters with mixed case letters') }}
            </p>
        </div>

        <div>
            <flux:input 
                wire:model="password_confirmation" 
                id="update_password_password_confirmation" 
                name="password_confirmation" 
                type="password" 
                label="{{ __('Confirm New Password') }}" 
                placeholder="{{ __('Confirm your new password') }}"
                autocomplete="new-password" 
                required
                class="w-full"
            />
            <flux:error name="password_confirmation"/>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <x-action-message class="text-sm text-green-600 dark:text-green-400" on="password-updated">
                {{ __('Password updated successfully') }}
            </x-action-message>
            
            <flux:button variant="primary" type="submit">
                {{ __('Update Password') }}
            </flux:button>
        </div>
    </form>
</section>
