<div class="w-full">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create New Product</h3>
                <flux:button variant="ghost" href="{{ route('products.index') }}">
                    <flux:icon.arrow-left class="size-4" />
                    Back to Products
                </flux:button>
            </div>
        </div>

        <!-- Card Content -->
        <form wire:submit="save" class="p-6">
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- SKU -->
                    <div>
                        <flux:label for="sku">SKU <span class="text-red-500">*</span></flux:label>
                        <flux:input id="sku" name="sku" wire:model="sku" placeholder="Enter product SKU" required class="w-full" />
                        @error('sku')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Product Name -->
                    <div>
                        <flux:label for="name">Product Name <span class="text-red-500">*</span></flux:label>
                        <flux:input id="name" name="name" wire:model="name" placeholder="Enter product name" required class="w-full" />
                        @error('name')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Primary Barcode -->
                    <div>
                        <flux:label for="barcode">Primary Barcode <span class="text-red-500">*</span></flux:label>
                        <flux:input id="barcode" name="barcode" wire:model="barcode" placeholder="Enter primary barcode" required class="w-full" />
                        @error('barcode')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Quantity -->
                    <div>
                        <flux:label for="quantity">Initial Quantity <span class="text-red-500">*</span></flux:label>
                        <flux:input id="quantity" name="quantity" type="number" wire:model="quantity" min="0" placeholder="0" required class="w-full" />
                        @error('quantity')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Secondary Barcode -->
                    <div>
                        <flux:label>Secondary Barcode</flux:label>
                        <flux:input wire:model="barcode_2" placeholder="Enter secondary barcode (optional)" />
                        @error('barcode_2')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Optional alternative barcode for this product</p>
                    </div>

                    <!-- Tertiary Barcode -->
                    <div>
                        <flux:label>Tertiary Barcode</flux:label>
                        <flux:input wire:model="barcode_3" placeholder="Enter tertiary barcode (optional)" />
                        @error('barcode_3')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Optional third barcode for this product</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" href="{{ route('products.index') }}">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        <flux:icon.plus class="size-4" />
                        Create Product
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>