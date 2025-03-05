<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public array $settings = [];

    public function mount()
    {
        // This will use your accessor to get settings with defaults if needed
        $this->settings = Auth::user()->settings;
    }

    public function updateSettings()
    {
        $settings = [
            'notifications' => [
                'email',
                'database',
            ],
        ];

        $user = Auth::user();

        $user->update([
            'settings' => $this->settings
        ]);

        $this->dispatch('settings-updated');
    }
} ?>

<div>
    <form wire:submit="updateSettings">
        <flux:fieldset>
            <flux:legend>Email notifications</flux:legend>

            <div class="space-y-4">
                <flux:switch wire:model.live="settings.notifications.email" label="Communication emails" description="Receive emails about your account activity." />

                <flux:separator variant="subtle" />

                <flux:switch wire:model.live="settings.notifications.database" label="Marketing emails" description="Receive emails about new products, features, and more." />
            </div>
        </flux:fieldset>

        <x-action-message class="me-3" on="settings-updated">
            {{ __('Your notification settings have been updated') }}
        </x-action-message>
    </form>
</div>
