<div>
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Session Flash Messages -->
        @if (session()->has('message'))
            <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-800 rounded-md p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-green-800 dark:text-green-200">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif
        <!-- Product Details Card -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SKU: {{ $product->sku }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button wire:click="showStockHistory" variant="ghost" size="sm" icon="chart-bar">Stock History</flux:button>
                    <a href="{{ route('products.edit', $product) }}" 
                       class="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-zinc-800 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('products.index') }}" 
                       class="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-zinc-800 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Products
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Content -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Basic Information</h4>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">SKU</label>
                            <div class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $product->sku }}</div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->name }}</div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Quantity</label>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ number_format($product->quantity ?? 0) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barcodes -->
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Barcodes</h4>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Primary Barcode</label>
                            <div class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $product->barcode }}</div>
                        </div>
                        
                        @if($product->barcode_2)
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Secondary Barcode</label>
                                <div class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $product->barcode_2 }}</div>
                            </div>
                        @endif
                        
                        @if($product->barcode_3)
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tertiary Barcode</label>
                                <div class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $product->barcode_3 }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timestamps -->
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Timestamps</h4>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->created_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Updated</label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Scans -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Scans</h3>
        </div>

        <!-- Card Content -->
        <div class="p-6">
            @if($recentScans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barcode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($recentScans as $scan)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $scan->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $scan->user->name ?? 'Unknown' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100">
                                        {{ $scan->barcode }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ ($scan->action ?? 'decrease') === 'increase' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ ($scan->action ?? 'decrease') === 'increase' ? '+' : '-' }}{{ $scan->quantity }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ ucfirst($scan->action ?? 'decrease') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($scan->sync_status === 'synced')
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Synced</span>
                                        @elseif($scan->sync_status === 'failed')
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Failed</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h4 class="mt-4 text-sm font-medium text-gray-500 dark:text-gray-400">No scans yet</h4>
                    <p class="mt-2 text-sm text-gray-400">Scans for this product will appear here once they start being recorded.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Stock History Modal -->
    <flux:modal name="stock-history" :show="$showHistoryModal" wire:model="showHistoryModal">
        <flux:modal.trigger>
            <!-- Modal trigger is handled by the button, not needed here -->
        </flux:modal.trigger>

        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Stock History for {{ $product->name }}</flux:heading>
                <flux:subheading>Showing recent stock changes from Linnworks</flux:subheading>
            </div>

            @if($isLoadingHistory)
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-zinc-600 dark:text-zinc-400">Loading stock history...</span>
                </div>
            @elseif($errorMessage)
                <flux:alert variant="danger">
                    <flux:alert.heading>Error loading stock history</flux:alert.heading>
                    <flux:alert.text>{{ $errorMessage }}</flux:alert.text>
                </flux:alert>
            @elseif($stockHistory && count($stockHistory) > 0)
                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:columns>
                            <flux:column>Date</flux:column>
                            <flux:column>Change</flux:column>
                            <flux:column>Balance</flux:column>
                            <flux:column>Type</flux:column>
                            <flux:column>Note</flux:column>
                        </flux:columns>

                        <flux:rows>
                            @foreach($stockHistory as $entry)
                                <flux:row>
                                    <flux:cell>
                                        {{ \Carbon\Carbon::parse($entry['ChangeDate'])->format('M d, Y H:i') }}
                                    </flux:cell>
                                    <flux:cell>
                                        <flux:badge 
                                            :color="$entry['ChangeQuantity'] >= 0 ? 'green' : 'red'" 
                                            size="sm">
                                            {{ $entry['ChangeQuantity'] >= 0 ? '+' : '' }}{{ number_format($entry['ChangeQuantity']) }}
                                        </flux:badge>
                                    </flux:cell>
                                    <flux:cell>
                                        {{ number_format($entry['BalanceAfter']) }}
                                    </flux:cell>
                                    <flux:cell>
                                        {{ $entry['ChangeSource'] ?? 'N/A' }}
                                    </flux:cell>
                                    <flux:cell>
                                        {{ $entry['Note'] ?? '' }}
                                    </flux:cell>
                                </flux:row>
                            @endforeach
                        </flux:rows>
                    </flux:table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <flux:heading size="md" class="mt-4 text-zinc-500 dark:text-zinc-400">No stock history found</flux:heading>
                    <flux:subheading class="mt-2 text-zinc-400">Stock changes for this product will appear here.</flux:subheading>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="closeHistoryModal">Close</flux:button>
            </div>
        </div>
    </flux:modal>
</div>