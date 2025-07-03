<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-md flex items-center justify-center">
                                <flux:icon.arrows-right-left class="size-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Stock Movement #{{ $movement->id }}</h1>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $movement->moved_at->format('l, F j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ match($movement->type) {
                                \App\Models\StockMovement::TYPE_BAY_REFILL => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                \App\Models\StockMovement::TYPE_MANUAL_TRANSFER => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                \App\Models\StockMovement::TYPE_SCAN_ADJUSTMENT => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                                default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200'
                            } }}">
                                {{ $movement->formatted_type }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">•</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $movement->moved_at->diffForHumans() }}</span>
                        </div>
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

            <!-- Movement Overview Card -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        
                        <!-- From Location -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <flux:icon.map-pin class="size-8 text-red-600 dark:text-red-400" />
                            </div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">From</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $movement->from_location_code ?: 'Unknown' }}</p>
                            @if($movement->from_location_id)
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">ID: {{ $movement->from_location_id }}</p>
                            @endif
                        </div>

                        <!-- Transfer Arrow & Quantity -->
                        <div class="text-center">
                            <div class="flex items-center justify-center mb-3">
                                <div class="flex items-center">
                                    <div class="w-4 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-2">
                                        <flux:icon.arrow-right class="size-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="w-4 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-zinc-800 rounded-md px-4 py-2 border border-zinc-200 dark:border-zinc-700 inline-block">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($movement->quantity) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">units</p>
                            </div>
                        </div>

                        <!-- To Location -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <flux:icon.map-pin class="size-8 text-green-600 dark:text-green-400" />
                            </div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">To</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $movement->to_location_code ?: 'Unknown' }}</p>
                            @if($movement->to_location_id)
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">ID: {{ $movement->to_location_id }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Product Information -->
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-2">
                                <flux:icon.cube class="size-5 text-gray-400" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Product Information</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SKU</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono bg-zinc-50 dark:bg-zinc-900 px-2 py-1 rounded">{{ $movement->product->sku }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Product Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $movement->product->name ?: 'No name available' }}</dd>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    @if($movement->product->barcode)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Primary Barcode</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono bg-zinc-50 dark:bg-zinc-900 px-2 py-1 rounded">{{ $movement->product->barcode }}</dd>
                                    </div>
                                    @endif
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $movement->product->updated_at->format('M j, Y g:i A') }}</dd>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Movement Details -->
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-2">
                                <flux:icon.clock class="size-5 text-gray-400" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Movement Details</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Movement Type</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ match($movement->type) {
                                            \App\Models\StockMovement::TYPE_BAY_REFILL => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            \App\Models\StockMovement::TYPE_MANUAL_TRANSFER => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            \App\Models\StockMovement::TYPE_SCAN_ADJUSTMENT => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                                            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200'
                                        } }}">
                                            {{ $movement->formatted_type }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Quantity Moved</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">{{ number_format($movement->quantity) }} units</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date & Time</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $movement->moved_at->format('F j, Y \a\t g:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Time Ago</dt>
                                    <dd class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $movement->moved_at->diffForHumans() }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($movement->notes)
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-2">
                                <flux:icon.document-text class="size-5 text-gray-400" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Notes</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="prose prose-sm max-w-none text-gray-900 dark:text-gray-100">
                                <p class="whitespace-pre-wrap">{{ $movement->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- User Information -->
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-2">
                                <flux:icon.user class="size-5 text-gray-400" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Performed By</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                        {{ $movement->user->initials() }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $movement->user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->user->email }}</p>
                                    @if($movement->user->hasRole('admin'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 mt-1">
                                            Admin
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata -->
                    @if($movement->metadata && count($movement->metadata) > 0)
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-2">
                                <flux:icon.information-circle class="size-5 text-gray-400" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Additional Details</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-3">
                                @foreach($movement->metadata as $key => $value)
                                    @if(!empty($value))
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            @if(is_array($value))
                                                <pre class="text-xs bg-zinc-50 dark:bg-zinc-900 p-2 rounded font-mono">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                            @elseif(is_numeric($value) && $key === 'stock_before')
                                                {{ number_format($value) }} units
                                            @else
                                                {{ $value }}
                                            @endif
                                        </dd>
                                    </div>
                                    @endif
                                @endforeach
                            </dl>
                        </div>
                    </div>
                    @endif

                    <!-- System Information -->
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-2">
                                <flux:icon.cog-6-tooth class="size-5 text-gray-400" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">System Info</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Movement ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">#{{ $movement->id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $movement->created_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                @if($movement->updated_at != $movement->created_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $movement->updated_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>