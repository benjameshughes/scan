<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
                {{ __('Bulk Invite Users') }}
            </h2>
            <flux:button variant="ghost" href="{{ route('admin.invites.index') }}">
                Back to Invitations
            </flux:button>
        </div>
    </x-slot>

    <livewire:invites.bulk-create />
</x-app-layout>