<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Products') }}
        </h2>
    </x-slot>

{{--    <livewire:products.index />--}}
    <livewire:table table-class="App\Tables\ProductsTable"/>

</x-app-layout>
