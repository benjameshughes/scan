<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            {{ __('Sync Dashboard') }}
        </h2>
    </x-slot>

    @livewire('admin.sync-dashboard')
</x-app-layout>