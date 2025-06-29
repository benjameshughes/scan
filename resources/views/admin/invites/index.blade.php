<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            {{ __('User & Invitation Management') }}
        </h2>
    </x-slot>

    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Unified User Management
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>User and invitation management has been combined into a single interface. 
                    <a href="{{ route('admin.users.index') }}" class="font-medium underline hover:no-underline">
                        Go to Users Management
                    </a> to manage both users and invitations.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Still show the old table for reference/transition period -->
    <livewire:invites-table />
</x-app-layout>