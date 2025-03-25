<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Users') }}
        </h2>
    </x-slot>

    {{--    <livewire:products.index />--}}
    <livewire:admin.users.user-list/>

</x-app-layout>