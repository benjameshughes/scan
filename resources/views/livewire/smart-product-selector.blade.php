<div class="space-y-2 relative">
    <div>
        <flux:label for="product-search" :required="$required">{{ $label }}</flux:label>
        
        <div class="relative">
            <flux:input
                id="product-search"
                wire:model.live.debounce.300ms="search"
                wire:focus="showSuggestions"
                wire:blur="hideSuggestions"
                :placeholder="$placeholder"
                icon="magnifying-glass"
                autocomplete="off"
                class="pr-20"
            />
            
            <!-- Barcode Scanner Toggle -->
            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
                @if($selectedProduct)
                    <button
                        type="button"
                        wire:click="clearSelection"
                        class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                        title="Clear selection"
                    >
                        <flux:icon.x-mark class="size-4" />
                    </button>
                @endif
                
                <button
                    type="button"
                    wire:click="toggleBarcodeScanner"
                    class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                    title="Scan barcode"
                >
                    <flux:icon.qr-code class="size-4 {{ $showBarcodeScanner ? 'text-blue-600' : '' }}" />
                </button>
            </div>
        </div>
        
        @if($errorMessage)
            <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $errorMessage }}</div>
        @endif
        
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Search by SKU, product name, or scan a barcode
        </div>
    </div>
    
    <!-- Selected Product Display -->
    @if($selectedProduct)
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-md flex items-center justify-center flex-shrink-0">
                    <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ $selectedProduct->sku }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200">
                            Selected
                        </span>
                    </div>
                    @if($selectedProduct->name)
                        <p class="text-sm text-green-700 dark:text-green-300">{{ $selectedProduct->name }}</p>
                    @endif
                    @if($selectedProduct->barcode)
                        <p class="text-xs text-green-600 dark:text-green-400 font-mono">{{ $selectedProduct->barcode }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
    
    <!-- Dropdown Results -->
    @if($showDropdown)
        <div class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-48 overflow-y-auto overflow-x-hidden">
            <!-- Search Results -->
            @if($searchResults->isNotEmpty())
                <div class="p-2 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search Results</div>
                    @foreach($searchResults as $product)
                        <button
                            type="button"
                            wire:click="selectProduct({{ $product->id }})"
                            class="w-full text-left p-2 hover:bg-zinc-50 dark:hover:bg-zinc-700 rounded-md transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->sku }}</div>
                                    @if($product->name)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $product->name }}</div>
                                    @endif
                                </div>
                                @if($product->barcode)
                                    <div class="text-xs text-gray-400 font-mono ml-2">{{ $product->barcode }}</div>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
            
            <!-- Recent Products -->
            @if($recentProducts->isNotEmpty() && empty($search))
                <div class="p-2">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recent Products</div>
                    @foreach($recentProducts as $product)
                        <button
                            type="button"
                            wire:click="selectProduct({{ $product->id }})"
                            class="w-full text-left p-2 hover:bg-zinc-50 dark:hover:bg-zinc-700 rounded-md transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->sku }}</div>
                                    @if($product->name)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $product->name }}</div>
                                    @endif
                                </div>
                                <flux:icon.clock class="size-3 text-gray-400" />
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
            
            <!-- No Results -->
            @if($searchResults->isEmpty() && !empty($search))
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                    <flux:icon.magnifying-glass class="size-8 mx-auto mb-2 text-gray-300" />
                    <div class="text-sm">No products found for "{{ $search }}"</div>
                    <div class="text-xs mt-1">Try a different search term or scan a barcode</div>
                </div>
            @endif
        </div>
    @endif
    
    <!-- Barcode Scanner -->
    @if($showBarcodeScanner)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Scan Barcode</h3>
                    <button
                        type="button"
                        wire:click="toggleBarcodeScanner"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div id="barcode-scanner" class="bg-gray-100 dark:bg-zinc-700 rounded-lg aspect-video flex items-center justify-center">
                        <div class="text-center">
                            <flux:icon.qr-code class="size-12 mx-auto mb-2 text-gray-400" />
                            <div class="text-sm text-gray-500 dark:text-gray-400">Camera will appear here</div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button
                            type="button"
                            wire:click="toggleBarcodeScanner"
                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('livewire:init', () => {
    // Hide dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('[id="product-search"]') && !e.target.closest('.absolute.z-50')) {
            @this.call('hideDropdown');
        }
    });
    
    // Handle barcode scanner events
    Livewire.on('initBarcodeScanner', () => {
        // Initialize barcode scanner (you can integrate with your existing scanner)
        console.log('Initialize barcode scanner');
    });
    
    Livewire.on('stopBarcodeScanner', () => {
        // Stop barcode scanner
        console.log('Stop barcode scanner');
    });
    
    // Handle keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            @this.call('hideDropdown');
        }
    });
});
</script>