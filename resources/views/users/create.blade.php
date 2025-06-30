<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Invite New User') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Create a new user account and send an invitation to join the system
                </p>
            </div>
            <flux:button 
                variant="ghost" 
                href="{{ route('users.index') }}"
                icon="arrow-left"
            >
                Back to Users
            </flux:button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:admin.users.add />
        </div>
    </div>
</x-app-layout>