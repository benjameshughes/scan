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
    <form wire:submit="save" class="space-y-8">
        
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

        <!-- Movement Details Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            
            <!-- Left Column - Movement Details -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <flux:icon.cog-6-tooth class="size-4" />
                        Movement Details
                    </h3>
                    
                    <div class="space-y-4">
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
                                    class="pr-20"
                                />
                                @if($maxQuantity)
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                        <button
                                            type="button"
                                            wire:click="$set('quantity', {{ $maxQuantity }})"
                                            class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors"
                                            title="Set to maximum available"
                                        >
                                            Max
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <flux:error name="quantity" />
                            @if($maxQuantity)
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Maximum available: {{ $maxQuantity }} units
                                </div>
                            @endif
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
                </div>
            </div>

            <!-- Middle Column - Location Transfer -->
            <div class="space-y-6">
                <div class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <flux:icon.map-pin class="size-4" />
                            Location Transfer
                        </h3>
                        @if($from_location_code && $to_location_code)
                            <button
                                type="button"
                                wire:click="swapLocations"
                                class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                title="Swap locations"
                            >
                                <flux:icon.arrow-path class="size-4" />
                            </button>
                        @endif
                    </div>
                    
                    <div class="space-y-4">
                        <!-- From Location -->
                        <div>
                            @livewire('smart-location-selector', [
                                'label' => 'From Location',
                                'placeholder' => 'Select source location...',
                                'selectedLocationId' => $selectedFromLocationId
                            ], key('from-location'))
                            <flux:error name="from_location_code" />
                        </div>

                        <!-- Transfer Arrow -->
                        <div class="flex justify-center">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center transition-transform hover:scale-110">
                                <flux:icon.arrow-down class="size-4 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>

                        <!-- To Location -->
                        <div>
                            @livewire('smart-location-selector', [
                                'label' => 'To Location',
                                'placeholder' => 'Select destination location...',
                                'selectedLocationId' => $selectedToLocationId
                            ], key('to-location'))
                            <flux:error name="to_location_code" />
                        </div>

                        <!-- Advanced Options -->
                        <details class="mt-4">
                            <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300 flex items-center gap-1">
                                <flux:icon.cog-6-tooth class="size-3" />
                                Advanced: Location IDs
                            </summary>
                            <div class="mt-3 space-y-3 pl-4 border-l-2 border-zinc-200 dark:border-zinc-700">
                                <flux:input
                                    wire:model="from_location_id"
                                    placeholder="From Location ID (optional)"
                                    size="sm"
                                />
                                <flux:input
                                    wire:model="to_location_id"
                                    placeholder="To Location ID (optional)"
                                    size="sm"
                                />
                            </div>
                        </details>
                    </div>
                </div>
            </div>

            <!-- Right Column - Smart Actions & Tips -->
            <div class="space-y-6">
                <!-- Movement Tips -->
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <flux:icon.light-bulb class="size-4" />
                        Quick Tips
                    </h4>
                    <div class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                        <div class="flex items-start gap-2">
                            <flux:icon.qr-code class="size-3 mt-0.5 flex-shrink-0" />
                            <span>Use the barcode scanner in product search for faster selection</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <flux:icon.magnifying-glass class="size-3 mt-0.5 flex-shrink-0" />
                            <span>Location selectors show recent and popular locations for quick access</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <flux:icon.calculator class="size-3 mt-0.5 flex-shrink-0" />
                            <span>Click "Max" button to set quantity to available stock</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <flux:icon.arrow-path class="size-3 mt-0.5 flex-shrink-0" />
                            <span>Use the swap button to quickly reverse location transfer</span>
                        </div>
                    </div>
                </div>
                
                <!-- Keyboard Shortcuts -->
                <div class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <flux:icon.command-line class="size-4" />
                        Keyboard Shortcuts
                    </h4>
                    <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between">
                            <span>Submit form</span>
                            <kbd class="px-1 py-0.5 bg-gray-200 dark:bg-zinc-700 rounded text-xs">⌘ + Enter</kbd>
                        </div>
                        <div class="flex justify-between">
                            <span>Focus product search</span>
                            <kbd class="px-1 py-0.5 bg-gray-200 dark:bg-zinc-700 rounded text-xs">⌘ + K</kbd>
                        </div>
                        <div class="flex justify-between">
                            <span>Close dropdowns</span>
                            <kbd class="px-1 py-0.5 bg-gray-200 dark:bg-zinc-700 rounded text-xs">Escape</kbd>
                        </div>
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