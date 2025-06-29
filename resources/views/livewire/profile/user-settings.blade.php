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

<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Notification Settings</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage how you receive notifications</p>
        </div>
        
        <form wire:submit="updateSettings" class="p-6 space-y-6">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Email Notifications</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                            <div>
                                <label for="email-notifications" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                    Communication emails
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Receive emails about your account activity and system updates
                                </p>
                            </div>
                            <flux:switch 
                                wire:model.live="settings.notifications.email" 
                                id="email-notifications"
                                name="email-notifications"
                            />
                        </div>

                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                            <div>
                                <label for="database-notifications" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                    In-app notifications
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Show notifications within the application interface
                                </p>
                            </div>
                            <flux:switch 
                                wire:model.live="settings.notifications.database" 
                                id="database-notifications"
                                name="database-notifications"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <x-action-message class="text-sm text-green-600 dark:text-green-400" on="settings-updated">
                    {{ __('Settings saved successfully') }}
                </x-action-message>
                
                <flux:button variant="primary" type="submit">
                    Save Settings
                </flux:button>
            </div>
        </form>
    </div>
</div>
