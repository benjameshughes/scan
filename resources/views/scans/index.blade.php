<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            {{ __('Scan History') }}
        </h2>
    </x-slot>

    <livewire:syncs-table />
</x-app-layout>