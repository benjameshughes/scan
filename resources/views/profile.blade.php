<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Profile') }}
        </h2>
    </x-slot>

    @role('admin')
    <div class="p-4 mb-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <livewire:profile.linnworks />
        </div>
    </div>
    @endrole

    <div class="p-4 mb-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <livewire:profile.update-profile-information-form />
        </div>
    </div>

    <div class="p-4 mb-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <livewire:profile.update-password-form />
        </div>
    </div>

    <div class="p-4 mb-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <livewire:profile.user-settings />
        </div>
    </div>

    <div class="p-4 mb-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <livewire:profile.delete-user-form />
        </div>
    </div>
</x-app-layout>
