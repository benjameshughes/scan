<div class="space-y-4">
    {{-- Success Indicator --}}
    <div class="text-center py-4">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full mb-4">
            <flux:icon.check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Product Found!</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Barcode: {{ $barcode }}</p>
    </div>

    {{-- Product Details Card --}}
    @if ($product)
        <div class="bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-3">
            {{-- Product Name --}}
            <div>
                <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }}</p>
            </div>

            {{-- Product Barcodes --}}
            <div class="space-y-2">
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Barcodes:</h5>
                <div class="space-y-1">
                    @if ($product->barcode)
                        <div class="flex items-center justify-between py-1 px-2 bg-white dark:bg-zinc-700 rounded border border-zinc-200 dark:border-zinc-600">
                            <span class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $product->barcode }}</span>
                            @if ($product->barcode === $barcode)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <flux:icon.check class="w-3 h-3 mr-1" />
                                    Scanned
                                </span>
                            @endif
                        </div>
                    @endif

                    @if ($product->barcode_2)
                        <div class="flex items-center justify-between py-1 px-2 bg-white dark:bg-zinc-700 rounded border border-zinc-200 dark:border-zinc-600">
                            <span class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $product->barcode_2 }}</span>
                            @if ($product->barcode_2 === $barcode)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <flux:icon.check class="w-3 h-3 mr-1" />
                                    Scanned
                                </span>
                            @endif
                        </div>
                    @endif

                    @if ($product->barcode_3)
                        <div class="flex items-center justify-between py-1 px-2 bg-white dark:bg-zinc-700 rounded border border-zinc-200 dark:border-zinc-600">
                            <span class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $product->barcode_3 }}</span>
                            @if ($product->barcode_3 === $barcode)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <flux:icon.check class="w-3 h-3 mr-1" />
                                    Scanned
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex space-x-3">
        <button 
            wire:click="startNewScan"
            class="flex-1 flex items-center justify-center space-x-2 px-4 py-3 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
        >
            <flux:icon.arrow-path class="w-4 h-4" />
            <span>New Scan</span>
        </button>

        <div class="flex-1">
            {{-- This space will be filled by the scan form component --}}
        </div>
    </div>
</div>