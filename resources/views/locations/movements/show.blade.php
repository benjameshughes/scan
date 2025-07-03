<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Stock Movement Details</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Movement #{{ $movement->id }} • {{ $movement->moved_at->format('M j, Y g:i A') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:button
                            href="{{ route('locations.movements.edit', $movement) }}"
                            variant="filled"
                            icon="pencil"
                            size="sm"
                            wire:navigate
                        >
                            Edit Movement
                        </flux:button>
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Movement Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <!-- Product Details -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Product</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">SKU</label>
                                            <p class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $movement->product->sku }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Product Name</label>
                                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $movement->product->name ?: 'No name available' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Movement Details -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Movement</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ match($movement->type) {
                                                \App\Models\StockMovement::TYPE_BAY_REFILL => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                \App\Models\StockMovement::TYPE_MANUAL_TRANSFER => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                \App\Models\StockMovement::TYPE_SCAN_ADJUSTMENT => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                                                default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200'
                                            } }}">
                                                {{ $movement->formatted_type }}
                                            </span>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Quantity</label>
                                            <p class="text-sm text-gray-900 dark:text-gray-100 font-semibold">{{ number_format($movement->quantity) }} units</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Date & Time</label>
                                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $movement->moved_at->format('F j, Y \a\t g:i A') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->moved_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location Movement -->
                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Location Transfer</h4>
                                    <div class="flex items-center justify-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-md">
                                        <div class="flex items-center space-x-6">
                                            <!-- From Location -->
                                            <div class="text-center">
                                                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-md flex items-center justify-center mb-2">
                                                    <flux:icon.map-pin class="size-6 text-red-600 dark:text-red-400" />
                                                </div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">From</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $movement->from_location_code ?: 'Unknown' }}</p>
                                                @if($movement->from_location_id)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $movement->from_location_id }}</p>
                                                @endif
                                            </div>

                                            <!-- Arrow -->
                                            <div class="flex items-center">
                                                <flux:icon.arrow-right class="size-6 text-gray-400 dark:text-gray-500" />
                                            </div>

                                            <!-- To Location -->
                                            <div class="text-center">
                                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-md flex items-center justify-center mb-2">
                                                    <flux:icon.map-pin class="size-6 text-green-600 dark:text-green-400" />
                                                </div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">To</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $movement->to_location_code ?: 'Unknown' }}</p>
                                                @if($movement->to_location_id)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $movement->to_location_id }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                @if($movement->notes)
                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Notes</h4>
                                    <div class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-md">
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $movement->notes }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- User Information -->
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Performed By</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                        {{ $movement->user->initials() }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $movement->user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->user->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata -->
                    @if($movement->metadata && count($movement->metadata) > 0)
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Additional Details</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($movement->metadata as $key => $value)
                                    @if(!empty($value))
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                                        </label>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">
                                            @if(is_array($value))
                                                {{ json_encode($value) }}
                                            @elseif(is_numeric($value) && $key === 'stock_before')
                                                {{ number_format($value) }} units
                                            @else
                                                {{ $value }}
                                            @endif
                                        </p>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- System Information -->
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">System Info</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Movement ID</label>
                                    <p class="text-sm text-gray-900 dark:text-gray-100 font-mono">#{{ $movement->id }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Created</label>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $movement->created_at->format('M j, Y g:i A') }}</p>
                                </div>
                                @if($movement->updated_at != $movement->created_at)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Last Updated</label>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $movement->updated_at->format('M j, Y g:i A') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>