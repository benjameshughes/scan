<div class="max-w-6xl mx-auto space-y-6">
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
</div>