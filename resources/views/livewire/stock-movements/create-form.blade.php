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
            <div>
                <flux:label for="product_search" required>Product</flux:label>
                <div class="relative">
                    <flux:input
                        id="product_search"
                        wire:model.live.debounce.300ms="product_search"
                        placeholder="Search by SKU or product name..."
                        icon="magnifying-glass"
                    />
                    @if($selected_product)
                        <button
                            type="button"
                            wire:click="clearProduct"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                        >
                            <flux:icon.x-mark class="size-4" />
                        </button>
                    @endif
                </div>
                <flux:error name="product_id" />
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Type at least 2 characters to search</div>
            </div>
            
            @if($selected_product)
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md transition-all duration-200">
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
                            <flux:label for="from_location_code">From Location</flux:label>
                            <div class="relative">
                                <flux:input
                                    id="from_location_code"
                                    wire:model="from_location_code"
                                    placeholder="e.g., 12B-3"
                                    icon="arrow-up-tray"
                                />
                                @if($from_location_code)
                                    <button
                                        type="button"
                                        wire:click="clearFromLocation"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                                    >
                                        <flux:icon.x-mark class="size-4" />
                                    </button>
                                @endif
                            </div>
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
                            <flux:label for="to_location_code">To Location</flux:label>
                            <div class="relative">
                                <flux:input
                                    id="to_location_code"
                                    wire:model="to_location_code"
                                    placeholder="e.g., Default"
                                    icon="arrow-down-tray"
                                />
                                @if($to_location_code)
                                    <button
                                        type="button"
                                        wire:click="clearToLocation"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                                    >
                                        <flux:icon.x-mark class="size-4" />
                                    </button>
                                @endif
                            </div>
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

            <!-- Right Column - Location Suggestions -->
            <div class="space-y-6">
                
                <!-- Quick Actions -->
                @if($from_location_code || $to_location_code)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-3 flex items-center gap-2">
                        <flux:icon.lightning-bolt class="size-4" />
                        Quick Actions
                    </h4>
                    <div class="space-y-2">
                        @if(!$from_location_code && $to_location_code)
                            <button
                                type="button"
                                wire:click="selectFromLocation('Default')"
                                class="w-full text-left px-3 py-2 text-xs bg-white dark:bg-zinc-800 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors"
                            >
                                Set "Default" as From Location
                            </button>
                        @endif
                        @if($from_location_code && !$to_location_code)
                            <button
                                type="button"
                                wire:click="selectToLocation('Default')"
                                class="w-full text-left px-3 py-2 text-xs bg-white dark:bg-zinc-800 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors"
                            >
                                Set "Default" as To Location
                            </button>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Recently Used Locations -->
                @if(count($this->recentLocations) > 0)
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <flux:icon.clock class="size-4" />
                        Recently Used
                    </h4>
                    <div class="space-y-1">
                        @foreach($this->recentLocations as $location)
                            <div class="flex gap-1">
                                <button
                                    type="button"
                                    wire:click="selectFromLocation('{{ $location['code'] }}', '{{ $location['id'] }}')"
                                    class="flex-1 text-left px-2 py-1 text-xs bg-zinc-50 dark:bg-zinc-900 hover:bg-red-50 dark:hover:bg-red-900/30 border border-zinc-200 dark:border-zinc-700 rounded text-gray-700 dark:text-gray-300 transition-colors"
                                    title="Set as From location"
                                >
                                    ↑ {{ $location['code'] }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="selectToLocation('{{ $location['code'] }}', '{{ $location['id'] }}')"
                                    class="flex-1 text-left px-2 py-1 text-xs bg-zinc-50 dark:bg-zinc-900 hover:bg-green-50 dark:hover:bg-green-900/30 border border-zinc-200 dark:border-zinc-700 rounded text-gray-700 dark:text-gray-300 transition-colors"
                                    title="Set as To location"
                                >
                                    ↓ {{ $location['code'] }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Popular Locations -->
                @if(count($this->popularLocations) > 0)
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <flux:icon.fire class="size-4" />
                            Popular Locations
                        </h4>
                        <button
                            type="button"
                            wire:click="toggleLocationSuggestions"
                            class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                        >
                            {{ $show_location_suggestions ? 'Hide' : 'Show' }} All
                        </button>
                    </div>
                    
                    <div class="space-y-1">
                        @foreach($this->popularLocations as $location)
                            <div class="flex gap-1">
                                <button
                                    type="button"
                                    wire:click="selectFromLocation('{{ $location['code'] }}', '{{ $location['id'] }}')"
                                    class="flex-1 text-left px-2 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-900 hover:bg-red-50 dark:hover:bg-red-900/30 border border-zinc-200 dark:border-zinc-700 rounded transition-colors group"
                                    title="Set as From location"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono">↑ {{ $location['code'] }}</span>
                                        @if($location['use_count'] > 0)
                                            <span class="text-xs text-gray-400 group-hover:text-red-600">{{ $location['use_count'] }}</span>
                                        @endif
                                    </div>
                                    @if($location['name'])
                                        <div class="text-xs text-gray-500 truncate">{{ $location['name'] }}</div>
                                    @endif
                                </button>
                                <button
                                    type="button"
                                    wire:click="selectToLocation('{{ $location['code'] }}', '{{ $location['id'] }}')"
                                    class="flex-1 text-left px-2 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-900 hover:bg-green-50 dark:hover:bg-green-900/30 border border-zinc-200 dark:border-zinc-700 rounded transition-colors group"
                                    title="Set as To location"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono">↓ {{ $location['code'] }}</span>
                                        @if($location['use_count'] > 0)
                                            <span class="text-xs text-gray-400 group-hover:text-green-600">{{ $location['use_count'] }}</span>
                                        @endif
                                    </div>
                                    @if($location['name'])
                                        <div class="text-xs text-gray-500 truncate">{{ $location['name'] }}</div>
                                    @endif
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- All Available Locations -->
                @if($show_location_suggestions && count($this->availableLocations) > 6)
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <flux:icon.map-pin class="size-4" />
                        All Locations
                    </h4>
                    
                    <div class="grid grid-cols-1 gap-1 max-h-64 overflow-y-auto">
                        @foreach($this->availableLocations->skip(6) as $location)
                            <div class="flex gap-1">
                                <button
                                    type="button"
                                    wire:click="selectFromLocation('{{ $location['code'] }}', '{{ $location['id'] }}')"
                                    class="flex-1 text-left px-2 py-1 text-xs bg-zinc-50 dark:bg-zinc-900 hover:bg-red-50 dark:hover:bg-red-900/30 border border-zinc-200 dark:border-zinc-700 rounded transition-colors"
                                    title="Set as From location"
                                >
                                    <span class="font-mono">↑ {{ $location['code'] }}</span>
                                    @if($location['name'])
                                        <span class="ml-1 text-gray-500">({{ Str::limit($location['name'], 15) }})</span>
                                    @endif
                                </button>
                                <button
                                    type="button"
                                    wire:click="selectToLocation('{{ $location['code'] }}', '{{ $location['id'] }}')"
                                    class="flex-1 text-left px-2 py-1 text-xs bg-zinc-50 dark:bg-zinc-900 hover:bg-green-50 dark:hover:bg-green-900/30 border border-zinc-200 dark:border-zinc-700 rounded transition-colors"
                                    title="Set as To location"
                                >
                                    <span class="font-mono">↓ {{ $location['code'] }}</span>
                                    @if($location['name'])
                                        <span class="ml-1 text-gray-500">({{ Str::limit($location['name'], 15) }})</span>
                                    @endif
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
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
            Livewire.on('product-selected', () => {
                setTimeout(() => {
                    document.getElementById('quantity')?.focus();
                }, 100);
            });

            // Celebrate location selection with subtle animation
            Livewire.on('location-selected', (event) => {
                const type = event.type;
                const code = event.code;
                
                // Show temporary success message
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md text-sm font-medium transform transition-all duration-300 ${
                    type === 'from' 
                        ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                        : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                }`;
                notification.textContent = `${type === 'from' ? 'From' : 'To'} location set: ${code}`;
                
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
        });
    </script>
</div>