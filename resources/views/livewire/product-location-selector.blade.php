<div class="space-y-2 relative">
    <div>
        <flux:label for="location-search" :required="$required">{{ $label }}</flux:label>
        
        <div class="relative">
            <flux:input
                id="location-search"
                wire:model.live.debounce.300ms="search"
                wire:focus="showSuggestions"
                wire:blur="hideSuggestions"
                :placeholder="$placeholder"
                icon="map-pin"
                autocomplete="off"
                class="pr-10"
                :disabled="!$product"
            />
            
            <!-- Clear Button -->
            @if($selectedLocationId)
                <button
                    type="button"
                    wire:click="clearSelection"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                    title="Clear selection"
                >
                    <flux:icon.x-mark class="size-4" />
                </button>
            @endif
        </div>
        
        @if($errorMessage)
            <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $errorMessage }}</div>
        @endif
        
        @if(!$product)
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Select a product first to see available locations
            </div>
        @elseif($productLocations->isEmpty())
            <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                No stock found for this product in any location
            </div>
        @else
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Showing {{ $productLocations->count() }} locations with stock
            </div>
        @endif
    </div>
    
    <!-- Dropdown Results -->
    @if($showDropdown && $product)
        <div class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-48 overflow-y-auto overflow-x-hidden">
            <!-- Product Locations with Stock -->
            @if($productLocations->isNotEmpty())
                <div class="p-2">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Locations with Stock</div>
                    @foreach($productLocations as $location)
                        <button
                            type="button"
                            wire:click="selectLocation('{{ $location['id'] }}', '{{ $location['code'] }}', {{ $location['quantity'] }})"
                            class="w-full text-left p-3 hover:bg-zinc-50 dark:hover:bg-zinc-700 rounded-md transition-colors border border-transparent hover:border-zinc-200 dark:hover:border-zinc-600"
                        >
                            <div class="flex items-center justify-between min-w-0">
                                <div class="flex-1 min-w-0 pr-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $location['code'] }}</div>
                                    @if($location['name'] && $location['name'] !== $location['code'])
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $location['name'] }}</div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400 whitespace-nowrap">
                                        {{ $location['quantity'] }}
                                    </div>
                                    <div class="w-2 h-2 rounded-full flex-shrink-0 {{ $location['quantity'] > 50 ? 'bg-green-500' : ($location['quantity'] > 10 ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
            
            <!-- No Stock Message -->
            @if($productLocations->isEmpty())
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                    <flux:icon.exclamation-triangle class="size-8 mx-auto mb-2 text-amber-400" />
                    <div class="text-sm">No stock available</div>
                    <div class="text-xs mt-1">This product has no stock in any location</div>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
document.addEventListener('livewire:init', () => {
    // Hide dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('[id="location-search"]') && !e.target.closest('.absolute.z-50')) {
            @this.call('hideDropdown');
        }
    });
    
    // Handle keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            @this.call('hideDropdown');
        }
    });
});
</script>