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

        <!-- Update Details Messages -->
        @if($updateMessage)
            @php
                $messageType = explode(':', $updateMessage)[0];
                $messageText = explode(':', $updateMessage, 2)[1] ?? $updateMessage;
            @endphp
            
            @if($messageType === 'success')
                <div class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-md p-4 border border-green-200 dark:border-green-800">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm">{{ $messageText }}</p>
                        </div>
                    </div>
                </div>
            @elseif($messageType === 'warning')
                <div class="bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 rounded-md p-4 border border-amber-200 dark:border-amber-800">
                    <div class="flex">
                        <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm">{{ $messageText }}</p>
                        </div>
                    </div>
                </div>
            @elseif($messageType === 'error')
                <div class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-md p-4 border border-red-200 dark:border-red-800">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm">{{ $messageText }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-md p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex">
                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm">{{ $messageText }}</p>
                        </div>
                    </div>
                </div>
            @endif
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
                    <flux:button 
                        wire:click="updateProductDetails" 
                        variant="ghost" 
                        size="sm" 
                        icon="arrow-path" 
                        :loading="$isUpdatingDetails">Update Details</flux:button>
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

    <!-- Location Stock Information -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Stock by Location</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Current inventory levels across all warehouse locations</p>
                </div>
                <flux:button 
                    wire:click="refreshLocationStocks" 
                    variant="ghost" 
                    size="sm" 
                    icon="arrow-path" 
                    :loading="$isLoadingLocationStocks">
                    Refresh
                </flux:button>
            </div>
        </div>

        <!-- Card Content -->
        <div class="p-6">
            @if($isLoadingLocationStocks)
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-600 dark:text-gray-400">Loading location stock data...</span>
                </div>
            @elseif($locationStockError)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                        <span class="text-sm text-red-700 dark:text-red-300">{{ $locationStockError }}</span>
                    </div>
                </div>
            @elseif(count($locationStocks) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($locationStocks as $locationStock)
                        <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4 border border-zinc-200 dark:border-zinc-600">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $locationStock['name'] }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $locationStock['id'] }}
                                    </p>
                                </div>
                                <flux:icon.map-pin class="size-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Total Stock:</span>
                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                        {{ number_format($locationStock['stock_level']) }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Available:</span>
                                    <span class="text-xs font-medium text-green-600 dark:text-green-400">
                                        {{ number_format($locationStock['available']) }}
                                    </span>
                                </div>
                                
                                @if($locationStock['allocated'] > 0)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Allocated:</span>
                                        <span class="text-xs font-medium text-amber-600 dark:text-amber-400">
                                            {{ number_format($locationStock['allocated']) }}
                                        </span>
                                    </div>
                                @endif
                                
                                @if($locationStock['on_order'] > 0)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">On Order:</span>
                                        <span class="text-xs font-medium text-blue-600 dark:text-blue-400">
                                            {{ number_format($locationStock['on_order']) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Stock Level Indicator -->
                            <div class="mt-3 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                @php
                                    $maxStock = collect($locationStocks)->max('stock_level');
                                    $percentage = $maxStock > 0 ? ($locationStock['stock_level'] / $maxStock) * 100 : 0;
                                @endphp
                                <div class="bg-blue-600 dark:bg-blue-400 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Showing {{ count($locationStocks) }} location(s) with stock. 
                        Total inventory: {{ number_format(collect($locationStocks)->sum('stock_level')) }} units
                    </p>
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon.map-pin class="size-12 text-gray-400 mx-auto mb-4" />
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">No stock at any location</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        This product currently has no inventory at any warehouse location
                    </p>
                </div>
            @endif
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
    @if($showHistoryModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="stock-history-modal">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-50" wire:click="closeHistoryModal"></div>
            
            <!-- Modal container -->
            <div class="flex items-center justify-center min-h-screen p-4">
                <!-- Modal panel following Design System standards -->
                <div class="relative bg-white dark:bg-zinc-800 rounded-lg shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-zinc-200 dark:border-zinc-700 z-10">
                    <!-- Modal Header following Card Header standards -->
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Stock History for {{ $product->name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Showing recent stock changes from Linnworks</p>
                            </div>
                            <flux:button variant="ghost" size="sm" wire:click="closeHistoryModal" icon="x-mark" />
                        </div>
                    </div>

                    <!-- Modal Content -->
                    <div class="px-6 space-y-4">
                        @if($isLoadingHistory)
                            <div class="flex items-center justify-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <span class="ml-3 text-gray-600 dark:text-gray-400">Loading stock history...</span>
                            </div>
                        @elseif($errorMessage)
                            <!-- Error Alert following Status Indicator standards -->
                            <div class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-md p-4 border border-red-200 dark:border-red-800">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium">Error loading stock history</h3>
                                        <p class="mt-2 text-sm">{{ $errorMessage }}</p>
                                    </div>
                                </div>
                            </div>
                        @elseif($stockHistory && is_array($stockHistory) && count($stockHistory) > 0)
                            <!-- Table following Design Language System Standards -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">Change</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">Balance</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">Note</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-800">
                                        @foreach($stockHistory as $entry)
                                            @if(is_array($entry))
                                            <tr class="border-b border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200">
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    {{ isset($entry['Date']) ? \Carbon\Carbon::parse($entry['Date'])->format('M d, Y H:i') : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm">
                                                    <!-- Status Badge following Status Indicator Standards -->
                                                    @if(isset($entry['ChangeQty']) && is_numeric($entry['ChangeQty']))
                                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $entry['ChangeQty'] >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                        {{ $entry['ChangeQty'] >= 0 ? '+' : '' }}{{ number_format($entry['ChangeQty']) }}
                                                    </span>
                                                    @else
                                                    <span class="text-gray-500">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    {{ isset($entry['Level']) && is_numeric($entry['Level']) ? number_format($entry['Level']) : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    {{ str_contains($entry['Note'] ?? '', 'DIRECT ADJUSTMENT BY') ? 'Manual Adjustment' : 'Order' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $entry['Note'] ?? '' }}
                                                </td>
                                            </tr>
                                            @else
                                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                                <td colspan="5" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                    Invalid data entry: {{ json_encode($entry) }}
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination following Design Language System -->
                            @if($historyTotalPages > 1)
                            <div class="flex items-center justify-between mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Showing page {{ $historyCurrentPage }} of {{ $historyTotalPages }} ({{ number_format($historyTotalEntries) }} total entries)
                                </div>
                                <div class="flex items-center space-x-2">
                                    <flux:button 
                                        wire:click="previousHistoryPage" 
                                        variant="ghost" 
                                        size="sm" 
                                        :disabled="$historyCurrentPage <= 1 || $isLoadingHistory"
                                        icon="chevron-left">Previous</flux:button>
                                    
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        Page {{ $historyCurrentPage }} of {{ $historyTotalPages }}
                                    </span>
                                    
                                    <flux:button 
                                        wire:click="nextHistoryPage" 
                                        variant="ghost" 
                                        size="sm" 
                                        :disabled="$historyCurrentPage >= $historyTotalPages || $isLoadingHistory"
                                        icon="chevron-right">Next</flux:button>
                                </div>
                            </div>
                            @endif
                        @else
                            <!-- Empty State -->
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <h4 class="mt-4 text-sm font-medium text-gray-500 dark:text-gray-400">No stock history found</h4>
                                <p class="mt-2 text-sm text-gray-400">Stock changes for this product will appear here.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer following Form Button Standards -->
                    <div class="px-6 py-4 mt-6 flex justify-end space-x-3 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button variant="ghost" wire:click="closeHistoryModal">Close</flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>