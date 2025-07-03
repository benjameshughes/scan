<div>
    <!-- Messages -->
    @if($success_message)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                    <span class="text-sm text-green-700 dark:text-green-300">{{ $success_message }}</span>
                </div>
            </div>
        </div>
    @endif

    @if($error_message)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                    <span class="text-sm text-red-700 dark:text-red-300">{{ $error_message }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        
        <!-- Product Selection -->
        <div class="space-y-4">
            <div>
                <flux:label for="product_search" required>Product</flux:label>
                <div class="relative">
                    <flux:input
                        id="product_search"
                        wire:model.live="product_search"
                        placeholder="Search by SKU or product name..."
                        icon="magnifying-glass"
                    />
                    @if($selected_product)
                        <button
                            type="button"
                            wire:click="clearProduct"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <flux:icon.x-mark class="size-4" />
                        </button>
                    @endif
                </div>
                <flux:error name="product_id" />
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Type at least 2 characters to search</div>
            </div>
            
            @if($selected_product)
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-md flex items-center justify-center flex-shrink-0">
                            <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ $selected_product->sku }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200">
                                    Selected
                                </span>
                            </div>
                            @if($selected_product->name)
                                <p class="text-sm text-green-700 dark:text-green-300">{{ $selected_product->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Movement Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Column -->
            <div class="space-y-6">
                
                <!-- Movement Type -->
                <div>
                    <flux:label for="type" required>Movement Type</flux:label>
                    <flux:select id="type" wire:model="type">
                        @foreach($this->movementTypes as $value => $label)
                            <flux:option value="{{ $value }}">{{ $label }}</flux:option>
                        @endforeach
                    </flux:select>
                    <flux:error name="type" />
                </div>

                <!-- Quantity -->
                <div>
                    <flux:label for="quantity" required>Quantity</flux:label>
                    <flux:input
                        type="number"
                        id="quantity"
                        wire:model="quantity"
                        min="1"
                        placeholder="Enter quantity..."
                        icon="calculator"
                    />
                    <flux:error name="quantity" />
                </div>

                <!-- Notes -->
                <div>
                    <flux:label for="notes">Notes</flux:label>
                    <flux:textarea
                        id="notes"
                        wire:model="notes"
                        rows="4"
                        placeholder="Optional notes about this movement..."
                        resize="vertical"
                    />
                    <flux:error name="notes" />
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum 500 characters</div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                
                <!-- Location Information Card -->
                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-md p-4 space-y-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <flux:icon.map-pin class="size-4" />
                        Location Transfer
                    </h3>
                    
                    <!-- From Location -->
                    <div>
                        <flux:label for="from_location_code">From Location</flux:label>
                        <flux:input
                            id="from_location_code"
                            wire:model="from_location_code"
                            placeholder="e.g., 12B-3"
                            icon="arrow-up-tray"
                        />
                        <flux:error name="from_location_code" />
                        
                        <!-- From Location ID (Advanced) -->
                        <details class="mt-2">
                            <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                                Advanced: Location ID
                            </summary>
                            <div class="mt-2">
                                <flux:input
                                    wire:model="from_location_id"
                                    placeholder="Optional Linnworks Location ID"
                                    size="sm"
                                />
                            </div>
                        </details>
                    </div>

                    <!-- Transfer Arrow -->
                    <div class="flex justify-center">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <flux:icon.arrow-down class="size-4 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>

                    <!-- To Location -->
                    <div>
                        <flux:label for="to_location_code">To Location</flux:label>
                        <flux:input
                            id="to_location_code"
                            wire:model="to_location_code"
                            placeholder="e.g., Default"
                            icon="arrow-down-tray"
                        />
                        <flux:error name="to_location_code" />
                        
                        <!-- To Location ID (Advanced) -->
                        <details class="mt-2">
                            <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                                Advanced: Location ID
                            </summary>
                            <div class="mt-2">
                                <flux:input
                                    wire:model="to_location_id"
                                    placeholder="Optional Linnworks Location ID"
                                    size="sm"
                                />
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Locations Helper -->
        @if(count($this->availableLocations) > 0)
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-md flex items-center justify-center flex-shrink-0">
                    <flux:icon.information-circle class="size-4 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Available Locations</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach($this->availableLocations as $location)
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <span class="font-mono">{{ $location['code'] }}</span>
                                @if($location['name'])
                                    <span class="ml-1 text-blue-600 dark:text-blue-300 truncate">({{ $location['name'] }})</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-6 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button
                href="{{ route('locations.movements') }}"
                variant="ghost"
                wire:navigate
            >
                Cancel
            </flux:button>
            
            <flux:button
                type="submit"
                variant="filled"
                icon="plus"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">Create Movement</span>
                <span wire:loading wire:target="save">Creating...</span>
            </flux:button>
        </div>
    </form>
</div>