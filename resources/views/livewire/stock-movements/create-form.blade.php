<div class="w-full">
    <!-- Header -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700 mb-6">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Create Stock Movement</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Transfer products between locations with smart suggestions and validation</p>
        </div>
    </div>

    <!-- Messages -->
    @if($success_message)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                <span class="text-sm text-green-700 dark:text-green-300">{{ $success_message }}</span>
            </div>
        </div>
    @endif

    @if($error_message)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                <span class="text-sm text-red-700 dark:text-red-300">{{ $error_message }}</span>
            </div>
        </div>
    @endif

    <!-- Form Container -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <form wire:submit="save" class="p-6">
        
        <!-- Product Selection -->
        <div class="space-y-4">
            @livewire('smart-product-selector', [
                'label' => 'Product',
                'required' => true,
                'selectedProductId' => $selectedProductId
            ])
            <flux:error name="product_id" />
            
            @if($currentStockLevel !== null)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3">
                    <div class="flex items-center gap-2 text-sm">
                        <flux:icon.cube class="size-4 text-blue-600 dark:text-blue-400" />
                        <span class="text-blue-800 dark:text-blue-200">
                            Current stock: <strong>{{ $currentStockLevel }}</strong> units
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Form Content -->
        <div class="space-y-6">
            <!-- Movement Details Section -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <flux:icon.cog-6-tooth class="size-5" />
                    Movement Details
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Movement Type -->
                    <div>
                        <flux:label for="type" required>Movement Type</flux:label>
                        <flux:select id="type" wire:model="type">
                            @foreach($this->movementTypes as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="type" />
                    </div>

                    <!-- Quantity -->
                    <div>
                        <flux:label for="quantity" required>Quantity</flux:label>
                        <div class="relative">
                            <flux:input
                                type="number"
                                id="quantity"
                                wire:model.live="quantity"
                                min="1"
                                :max="$maxQuantity"
                                placeholder="Enter quantity..."
                                icon="calculator"
                                class="{{ $maxQuantity ? 'pr-16' : '' }}"
                            />
                            @if($maxQuantity && $maxQuantity > 0)
                                <div class="absolute right-2 top-1/2 -translate-y-1/2">
                                    <button
                                        type="button"
                                        wire:click="$set('quantity', {{ $maxQuantity }})"
                                        class="px-2 py-1 text-xs bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded transition-colors"
                                        title="Set to maximum available ({{ $maxQuantity }} units)"
                                    >
                                        Max
                                    </button>
                                </div>
                            @endif
                        </div>
                        <flux:error name="quantity" />
                        @if($maxQuantity !== null)
                            <div class="text-xs {{ $maxQuantity > 0 ? 'text-gray-500 dark:text-gray-400' : 'text-amber-600 dark:text-amber-400' }} mt-1">
                                @if($maxQuantity > 0)
                                    Maximum available: {{ $maxQuantity }} units
                                @else
                                    No stock available in selected location
                                @endif
                            </div>
                        @endif
                    </div>

                </div>
                
                <!-- Notes -->
                <div>
                    <flux:label for="notes">Notes</flux:label>
                    <flux:textarea
                        id="notes"
                        wire:model="notes"
                        rows="3"
                        placeholder="Optional notes about this movement..."
                        resize="vertical"
                    />
                    <flux:error name="notes" />
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum 500 characters</div>
                </div>
            </div>

            <!-- Location Transfer Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <flux:icon.map-pin class="size-5" />
                        Location Transfer
                    </h3>
                    @if($from_location_code && $to_location_code)
                        <button
                            type="button"
                            wire:click="swapLocations"
                            class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20"
                            title="Swap locations"
                        >
                            <flux:icon.arrow-path class="size-4" />
                        </button>
                    @endif
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- From Location -->
                    <div>
                        @livewire('product-location-selector', [
                            'label' => 'From Location (Source)',
                            'placeholder' => 'Select location with stock...',
                            'productId' => $selectedProductId,
                            'selectedLocationId' => $selectedFromLocationId,
                            'required' => true
                        ], key('from-location'))
                        <flux:error name="from_location_code" />
                    </div>


                    <!-- To Location -->
                    <div>
                        @livewire('smart-location-selector', [
                            'label' => 'To Location (Destination)',
                            'placeholder' => 'Search and select destination...',
                            'selectedLocationId' => $selectedToLocationId,
                            'required' => true,
                            'type' => 'to'
                        ], key('to-location'))
                        <flux:error name="to_location_code" />
                    </div>

                </div>
            </div>

            <!-- Tips Section -->
            <div class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                    <flux:icon.light-bulb class="size-4" />
                    Quick Tips
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-gray-600 dark:text-gray-400">
                    <div class="flex items-start gap-2">
                        <flux:icon.qr-code class="size-3 mt-0.5 flex-shrink-0" />
                        <span>Use barcode scanner for faster product selection</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <flux:icon.magnifying-glass class="size-3 mt-0.5 flex-shrink-0" />
                        <span>Location selectors show recent and popular locations</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <flux:icon.calculator class="size-3 mt-0.5 flex-shrink-0" />
                        <span>Click "Max" to set quantity to available stock</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <flux:icon.command-line class="size-3 mt-0.5 flex-shrink-0" />
                        <span>Use ⌘+Enter to submit, ⌘+K for search</span>
                    </div>
                </div>
            </div>
        </div>

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
                :disabled="!$selected_product || !$from_location_code || !$to_location_code || !$quantity"
            >
                <span wire:loading.remove wire:target="save">Create Movement</span>
                <span wire:loading wire:target="save">Creating...</span>
            </flux:button>
        </div>
        </form>
    </div>

    <!-- JavaScript for enhanced UX -->
    <script>
        document.addEventListener('livewire:init', () => {
            // Auto-focus quantity field when product is selected
            Livewire.on('productSelected', () => {
                setTimeout(() => {
                    document.getElementById('quantity')?.focus();
                }, 100);
            });

            // Celebrate location selection with subtle animation
            Livewire.on('locationSelected', (event) => {
                const [locationId, locationCode, type] = event;
                
                // Show temporary success message
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md text-sm font-medium transform transition-all duration-300 ${
                    type === 'from' 
                        ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                        : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                }`;
                notification.textContent = `${type === 'from' ? 'From' : 'To'} location set: ${locationCode}`;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }, 2000);
            });

            // Celebrate location swap
            Livewire.on('locations-swapped', () => {
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 z-50 px-4 py-2 rounded-md text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 transform transition-all duration-300';
                notification.textContent = '🔄 Locations swapped!';
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }, 2000);
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // Submit form with Cmd+Enter or Ctrl+Enter
                if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
                    e.preventDefault();
                    const submitButton = document.querySelector('button[type="submit"]');
                    if (submitButton && !submitButton.disabled) {
                        submitButton.click();
                    }
                }
                
                // Focus product search with Cmd+K or Ctrl+K
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    const productSearch = document.getElementById('product-search');
                    if (productSearch) {
                        productSearch.focus();
                    }
                }
                
                // Close dropdowns with Escape
                if (e.key === 'Escape') {
                    // This will be handled by individual components
                }
            });
        });
    </script>
</div>