<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Scan') }}
        </h2>
    </x-slot>


{{--    <livewire:scan-list/>--}}
    <livewire:table table-class="App\Tables\SyncsTable"/>

</x-app-layout>