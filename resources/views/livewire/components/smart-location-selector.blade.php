<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <!-- Selected Location Display / Trigger -->
    <div 
        class="relative w-full cursor-pointer"
        @click="$wire.toggleDropdown()"
        @click.away="$wire.closeDropdown()"
    >
        <div class="flex items-center justify-between w-full px-4 py-3 text-left bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @if($this->selectedLocation)
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $this->selectedLocation['LocationName'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Available: {{ number_format($this->selectedLocation['Quantity']) }} units
                        </div>
                    </div>
                </div>
            @else
                <span class="text-gray-500 dark:text-gray-400">{{ $placeholder }}</span>
            @endif
            
            <flux:icon.chevron-down class="w-5 h-5 text-gray-400 transition-transform duration-200" ::class="{ 'rotate-180': open }" />
        </div>
    </div>

    <!-- Dropdown -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-md shadow-lg"
        x-cloak
    >
        <!-- Search Input -->
        @if($showSearch)
            <div class="p-3 border-b border-zinc-200 dark:border-zinc-700">
                <div class="relative">
                    <flux:icon.magnifying-glass class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchTerm"
                        class="w-full pl-10 pr-4 py-2 text-sm border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Search locations..."
                        x-ref="searchInput"
                        @keydown.escape="$wire.closeDropdown()"
                    >
                </div>
            </div>
        @endif

        <!-- Location List -->
        <div class="max-h-60 overflow-y-auto">
            @if($this->filteredLocations->isNotEmpty())
                @php
                    $recentLocationIds = collect($this->getRecentLocations());
                    $favoriteLocationIds = collect($this->getFavoriteLocations());
                @endphp

                @foreach($this->filteredLocations as $location)
                    @php
                        $locationId = $location['StockLocationId'];
                        $isRecent = $recentLocationIds->contains($locationId);
                        $isFavorite = $favoriteLocationIds->contains($locationId);
                        $isSelected = $this->selectedLocationId === $locationId;
                    @endphp
                    
                    <div 
                        wire:click="selectLocation('{{ $locationId }}')"
                        class="flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ $isSelected ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                    >
                        <div class="flex items-center space-x-3 flex-1">
                            <!-- Status Indicator -->
                            <div class="flex-shrink-0">
                                @if($isSelected)
                                    <flux:icon.check-circle class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                @else
                                    <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                @endif
                            </div>

                            <!-- Location Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                        {{ $location['LocationName'] }}
                                    </span>
                                    
                                    <!-- Badges -->
                                    @if($isFavorite)
                                        <flux:icon.star class="w-3 h-3 text-amber-400 fill-current" />
                                    @endif
                                    @if($isRecent && !$isFavorite)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Recent
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Available: {{ number_format($location['Quantity']) }} units
                                </div>
                            </div>
                        </div>

                        <!-- Favorite Toggle -->
                        <button
                            wire:click.stop="toggleFavorite('{{ $locationId }}')"
                            class="flex-shrink-0 p-1 text-gray-400 hover:text-amber-500 transition-colors"
                            title="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}"
                        >
                            @if($isFavorite)
                                <flux:icon.star class="w-4 h-4 text-amber-400 fill-current" />
                            @else
                                <flux:icon.star class="w-4 h-4" />
                            @endif
                        </button>
                    </div>
                @endforeach
            @else
                <div class="px-4 py-6 text-center">
                    <flux:icon.magnifying-glass class="mx-auto w-8 h-8 text-gray-400 mb-2" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if(empty($searchTerm))
                            No locations available
                        @else
                            No locations found matching "{{ $searchTerm }}"
                        @endif
                    </p>
                </div>
            @endif
        </div>

        <!-- Quick Actions Footer -->
        @if($this->filteredLocations->isNotEmpty() && empty($searchTerm))
            <div class="px-3 py-2 bg-zinc-50 dark:bg-zinc-700 border-t border-zinc-200 dark:border-zinc-600 rounded-b-md">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <flux:icon.star class="inline w-3 h-3 text-amber-400 mr-1" />
                    Click star to favorite locations
                </p>
            </div>
        @endif
    </div>

    <!-- Focus Search Input When Dropdown Opens -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('focus-search', () => {
                setTimeout(() => {
                    const searchInput = document.querySelector('[x-ref="searchInput"]');
                    if (searchInput) {
                        searchInput.focus();
                    }
                }, 100);
            });
        });
    </script>
</div>