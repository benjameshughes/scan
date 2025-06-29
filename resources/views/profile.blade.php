<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:user-profile />
            
            @can('view users')
            <!-- Admin Section -->
            <div class="mt-8 bg-white dark:bg-zinc-800 shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Administrator Settings
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Additional settings available to administrators.
                    </p>
                </div>
                <div class="p-6">
                    <livewire:profile.linnworks />
                </div>
            </div>
            @endcan
        </div>
    </div>
</x-app-layout>
