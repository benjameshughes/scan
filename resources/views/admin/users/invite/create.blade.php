<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Invite a user') }}
        </h2>
    </x-slot>
    <flux:text>This will send an email to the address provided. The user has to click the link and set a password. The invite will expire after 24 hours.</flux:text>
    <livewire:invites.create />
</x-app-layout>
