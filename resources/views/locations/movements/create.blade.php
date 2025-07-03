<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Create Stock Movement</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Manually record a stock movement between locations
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:button
                            href="{{ route('locations.movements') }}"
                            variant="ghost"
                            icon="arrow-left"
                            size="sm"
                            wire:navigate
                        >
                            Back to Movements
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Create Form -->
            <div class="w-full">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Movement Information</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Fill in the details below to create a new stock movement record.
                        </p>
                    </div>
                    <div class="p-6">
                        <livewire:stock-movements.create-form />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>