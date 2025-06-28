<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-800 dark:text-white">
            {{ __('Scan') }}
        </h2>
    </x-slot>


{{--    <livewire:scan-list/>--}}
    <livewire:syncs-table/>

</x-app-layout>