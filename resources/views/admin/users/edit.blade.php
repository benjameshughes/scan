<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
        {{$user->name}}
            <span class="text-xs">{{$user->email}}</span>
        </h2>
    </x-slot>

    <livewire:admin.users.edit :user="$user"/>

</x-app-layout>