<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            {{ __('Products Management') }}
        </h2>
    </x-slot>

    <livewire:products-table />
</x-app-layout>
