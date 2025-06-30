<div class="w-full">
    <!-- Card Container -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Product</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $product->name }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button 
                        variant="ghost" 
                        href="{{ route('products.show', $product) }}"
                        size="sm"
                    >
                        <flux:icon.eye class="size-4" />
                        View Product
                    </flux:button>
                    <flux:button 
                        variant="ghost" 
                        href="{{ route('products.index') }}"
                        size="sm"
                    >
                        <flux:icon.arrow-left class="size-4" />
                        Back to Products
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Card Content -->
        <form wire:submit="save" class="p-6">
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- SKU -->
                    <div>
                        <flux:input 
                            wire:model="sku" 
                            id="sku"
                            name="sku"
                            label="SKU *"
                            placeholder="Enter product SKU"
                            required
                            class="w-full"
                        />
                        <flux:error name="sku"/>
                    </div>

                    <!-- Product Name -->
                    <div>
                        <flux:input 
                            wire:model="name" 
                            id="name"
                            name="name"
                            label="Product Name *"
                            placeholder="Enter product name"
                            required
                            class="w-full"
                        />
                        <flux:error name="name"/>
                    </div>

                    <!-- Primary Barcode -->
                    <div>
                        <flux:input 
                            wire:model="barcode" 
                            id="barcode"
                            name="barcode"
                            label="Primary Barcode *"
                            placeholder="Enter primary barcode"
                            required
                            class="w-full"
                        />
                        <flux:error name="barcode"/>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <flux:input 
                            wire:model="quantity" 
                            id="quantity"
                            name="quantity"
                            type="number"
                            label="Current Quantity *"
                            min="0" 
                            placeholder="0"
                            required
                            class="w-full"
                        />
                        <flux:error name="quantity"/>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This will override the current quantity. Use the scanner for inventory adjustments.</p>
                    </div>

                    <!-- Secondary Barcode -->
                    <div>
                        <flux:input 
                            wire:model="barcode_2" 
                            id="barcode_2"
                            name="barcode_2"
                            label="Secondary Barcode"
                            placeholder="Enter secondary barcode (optional)"
                            class="w-full"
                        />
                        <flux:error name="barcode_2"/>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Optional alternative barcode for this product</p>
                    </div>

                    <!-- Tertiary Barcode -->
                    <div>
                        <flux:input 
                            wire:model="barcode_3" 
                            id="barcode_3"
                            name="barcode_3"
                            label="Tertiary Barcode"
                            placeholder="Enter tertiary barcode (optional)"
                            class="w-full"
                        />
                        <flux:error name="barcode_3"/>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Optional third barcode for this product</p>
                    </div>
                </div>

                <!-- Warning Notice -->
                <div class="rounded-md bg-amber-50 dark:bg-amber-900/20 p-4 border border-amber-200 dark:border-amber-800">
                    <div class="flex">
                        <svg class="h-5 w-5 text-amber-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-amber-800 dark:text-amber-200">Important Note</h4>
                            <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                <p>Changing barcodes or quantity here will affect inventory tracking. Make sure these changes are intentional and accurate.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button 
                        variant="ghost" 
                        href="{{ route('products.show', $product) }}"
                    >
                        Cancel
                    </flux:button>
                    <flux:button 
                        type="submit" 
                        variant="primary"
                    >
                        <flux:icon.check class="size-4" />
                        Update Product
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>