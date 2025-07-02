<div class="max-w-6xl mx-auto space-y-6">
    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700 dark:text-green-300">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Scan Details Card -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Scan Details</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Scan ID: #{{ $scan->id }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(in_array($scan->sync_status, ['failed', 'pending', null]))
                        <flux:button variant="ghost" wire:click="resync">
                            <flux:icon.arrow-path class="size-4" />
                            Resync
                        </flux:button>
                    @endif
                    <flux:button variant="ghost" href="{{ route('scans.index') }}" wire:navigate>
                        <flux:icon.arrow-left class="size-4" />
                        Back to Scans
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Card Content -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Scan Information -->
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Scan Information</h4>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barcode</label>
                            <div class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $scan->barcode }}</div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity Change</label>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-sm font-medium {{ ($scan->action ?? 'decrease') === 'increase' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ ($scan->action ?? 'decrease') === 'increase' ? '+' : '-' }}{{ $scan->quantity }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</label>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ ($scan->action ?? 'decrease') === 'increase' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ ucfirst($scan->action ?? 'decrease') }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submission Status</label>
                            <div class="mt-1">
                                @if($scan->submitted)
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Submitted</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">Pending</span>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sync Status</label>
                            <div class="mt-1">
                                @if($scan->sync_status === 'synced')
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Synced</span>
                                @elseif($scan->sync_status === 'failed')
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Failed</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">Pending</span>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scanned by</label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $scan->user->name }}</div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scanned at</label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $scan->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                        
                        @if($scan->submitted_at)
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submitted at</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $scan->submitted_at->format('M j, Y g:i A') }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Product Information -->
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Product Information</h4>
                    
                    @if($product)
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
                            
                            @if($product->quantity !== null)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Quantity</label>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ number_format($product->quantity) }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Actions for Product -->
                        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button variant="ghost" href="{{ route('products.show', $product) }}" wire:navigate>
                                <flux:icon.eye class="size-4" />
                                View Product Details
                            </flux:button>
                        </div>
                    @else
                        <!-- Product Not Found -->
                        <div class="rounded-md bg-amber-50 dark:bg-amber-900/20 p-4 border border-amber-200 dark:border-amber-800">
                            <div class="flex">
                                <svg class="h-5 w-5 text-amber-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-amber-800 dark:text-amber-200">Product Not Found</h4>
                                    <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                        <p>No product found matching barcode: <code class="font-mono bg-amber-100 dark:bg-amber-900 px-1 py-0.5 rounded text-xs">{{ $scan->barcode }}</code></p>
                                        <p class="mt-1">This may indicate the product was deleted or the barcode was incorrectly entered.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information Card -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Technical Details</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scan ID</label>
                    <div class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $scan->id }}</div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User ID</label>
                    <div class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $scan->user_id }}</div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</label>
                    <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $scan->created_at->diffForHumans() }}</div>
                </div>
            </div>
            
            @if($scan->sync_status === 'failed')
                <div class="mt-6 rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-red-800 dark:text-red-200">Sync Failed</h4>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                <p>This scan failed to sync with the external system. You can retry the sync using the "Resync" button above.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>