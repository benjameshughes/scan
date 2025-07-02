<div class="w-full">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create Manual Scan</h3>
                <flux:button variant="ghost" href="{{ route('scans.index') }}" wire:navigate>
                    <flux:icon.arrow-left class="size-4" />
                    Back to Scans
                </flux:button>
            </div>
        </div>

        <!-- Card Content -->
        <form wire:submit="save" class="p-6">
            <div class="space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Barcode -->
                    <div class="md:col-span-2">
                        <flux:label>Barcode *</flux:label>
                        <flux:input wire:model.live="barcode" placeholder="Enter or scan barcode" />
                        @error('barcode')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Start typing to search for products</p>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <flux:label>Quantity *</flux:label>
                        <flux:input type="number" wire:model="quantity" min="1" placeholder="1" />
                        @error('quantity')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">
                        Action <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                            <flux:radio 
                                wire:model="action" 
                                value="decrease"
                                id="action-decrease"
                                name="action"
                            />
                            <div>
                                <label for="action-decrease" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                    Decrease Inventory
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Remove items from stock (default scan behavior)
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                            <flux:radio 
                                wire:model="action" 
                                value="increase"
                                id="action-increase"
                                name="action"
                            />
                            <div>
                                <label for="action-increase" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                    Increase Inventory
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Add items to stock (receiving/restocking)
                                </p>
                            </div>
                        </div>
                    </div>
                    <flux:error name="action"/>
                </div>

                <!-- Preview -->
                @if($barcode && $quantity && $selectedProduct)
                    <div class="rounded-md bg-zinc-50 dark:bg-zinc-800 p-4 border border-zinc-200 dark:border-zinc-700">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Scan Preview</h4>
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <p>This will <strong>{{ $action }}</strong> <strong>{{ $quantity }}</strong> unit(s) of <strong>{{ $selectedProduct->name }}</strong></p>
                            <p class="mt-1">
                                New quantity will be: 
                                <span class="font-medium">
                                    {{ number_format(($selectedProduct->quantity ?? 0) + ($action === 'increase' ? $quantity : -$quantity)) }}
                                </span>
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Info Notice -->
                <div class="rounded-md bg-amber-50 dark:bg-amber-900/20 p-4 border border-amber-200 dark:border-amber-800">
                    <div class="flex">
                        <svg class="h-5 w-5 text-amber-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-amber-800 dark:text-amber-200">Manual Scan Entry</h4>
                            <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                <p>This form allows you to manually record inventory changes. For regular operations, use the barcode scanner interface instead.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" href="{{ route('scans.index') }}" wire:navigate>
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary" :disabled="!$selectedProduct">
                        <flux:icon.plus class="size-4" />
                        Record Scan
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>