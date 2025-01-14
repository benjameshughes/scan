<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Scan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex gap-4 justify-start p-6 bg-white border-b border-gray-200 rounded-t-lg dark:bg-gray-800">
                    <button wire:click="all" class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                        All Scans
                    </button>
                    <button wire:click="aggregated" class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                        Aggregated Scans
                    </button>
                </div>
                    <livewire:scan-list />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>