<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Stock Movement History</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Track all stock movements between locations
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:button
                            href="{{ route('locations.movements.create') }}"
                            variant="filled"
                            icon="plus"
                            size="sm"
                            wire:navigate
                        >
                            Create Movement
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Movements</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format(\App\Models\StockMovement::count()) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <flux:icon.arrows-right-left class="size-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">This Month</p>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                                {{ number_format(\App\Models\StockMovement::whereMonth('moved_at', now()->month)->count()) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <flux:icon.calendar-days class="size-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Bay Refills</p>
                            <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                                {{ number_format(\App\Models\StockMovement::where('type', \App\Models\StockMovement::TYPE_BAY_REFILL)->count()) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <flux:icon.home class="size-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Manual Transfers</p>
                            <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                                {{ number_format(\App\Models\StockMovement::where('type', \App\Models\StockMovement::TYPE_MANUAL_TRANSFER)->count()) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                            <flux:icon.hand-raised class="size-6 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <livewire:stock-movements-table />
        </div>
    </div>
</x-app-layout>