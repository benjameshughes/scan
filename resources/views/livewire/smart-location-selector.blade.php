<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <!-- Label -->
    @if($label)
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <!-- Input Field -->
    <div class="relative">
        <input
            wire:model.live.debounce.300ms="search"
            wire:focus="showSuggestions"
            wire:blur="hideSuggestions"
            type="text"
            placeholder="{{ $placeholder }}"
            class="w-full border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 pr-10"
            autocomplete="off"
        />
        
        <!-- Clear Button -->
        @if($selectedLocationId || $search)
            <button
                wire:click="clearSelection"
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
            >
                <flux:icon.x-mark class="size-4" />
            </button>
        @else
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <flux:icon.map-pin class="size-4 text-gray-400" />
            </div>
        @endif
    </div>

    <!-- Error Message -->
    @if($errorMessage)
        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $errorMessage }}</p>
    @endif

    <!-- Dropdown -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-md shadow-lg max-h-64 overflow-y-auto"
        style="display: none;"
    >
        <!-- Smart Suggestions (when no search) -->
        @if(!$search && $smartSuggestions->count() > 0)
            <div class="px-3 py-2 border-b border-zinc-200 dark:border-zinc-700">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Recent & Popular
                </p>
            </div>
            @foreach($smartSuggestions as $location)
                <button
                    wire:click="selectLocation('{{ $location->location_id }}')"
                    type="button"
                    class="w-full px-3 py-2 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:bg-zinc-50 dark:focus:bg-zinc-700 focus:outline-none flex items-center justify-between group"
                >
                    <div>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                            {{ $location->code }}
                        </div>
                        @if($location->name && $location->name !== $location->code)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $location->name }}
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        @if($location->use_count > 0)
                            <span class="flex items-center gap-1">
                                <flux:icon.arrow-trending-up class="size-3" />
                                {{ $location->use_count }}
                            </span>
                        @endif
                        @if($location->last_used_at)
                            <span title="{{ $location->last_used_at->format('M j, Y g:i A') }}">
                                {{ $location->last_used_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        @endif

        <!-- Search Results -->
        @if($search && $searchResults->count() > 0)
            <div class="px-3 py-2 border-b border-zinc-200 dark:border-zinc-700">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Search Results
                </p>
            </div>
            @foreach($searchResults as $location)
                <button
                    wire:click="selectLocation('{{ $location->location_id }}')"
                    type="button"
                    class="w-full px-3 py-2 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:bg-zinc-50 dark:focus:bg-zinc-700 focus:outline-none flex items-center justify-between group"
                >
                    <div>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                            {{ $location->code }}
                        </div>
                        @if($location->name && $location->name !== $location->code)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $location->name }}
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        @if($location->use_count > 0)
                            <span class="flex items-center gap-1">
                                <flux:icon.arrow-trending-up class="size-3" />
                                {{ $location->use_count }}
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        @endif

        <!-- Create New Location Option -->
        @if($showCreateOption)
            @if($searchResults->count() > 0)
                <div class="border-t border-zinc-200 dark:border-zinc-700"></div>
            @endif
            <button
                wire:click="selectLocation('create')"
                type="button"
                class="w-full px-3 py-2 text-left hover:bg-blue-50 dark:hover:bg-blue-900/20 focus:bg-blue-50 dark:focus:bg-blue-900/20 focus:outline-none flex items-center gap-2 group"
            >
                <flux:icon.plus class="size-4 text-blue-600 dark:text-blue-400" />
                <div>
                    <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">
                        Create "{{ $search }}"
                    </div>
                    <div class="text-xs text-blue-500 dark:text-blue-500">
                        Add as new location
                    </div>
                </div>
            </button>
        @endif

        <!-- No Results -->
        @if($search && $searchResults->count() === 0 && !$showCreateOption)
            <div class="px-3 py-6 text-center">
                <flux:icon.map-pin class="size-8 text-gray-400 mx-auto mb-2" />
                <p class="text-sm text-gray-500 dark:text-gray-400">No locations found</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    Try a different search term
                </p>
            </div>
        @endif

        <!-- Sync Option (for admin users) -->
        @can('manage products')
            @if((!$search && $smartSuggestions->count() === 0) || ($search && $searchResults->count() === 0))
                <div class="border-t border-zinc-200 dark:border-zinc-700">
                    <button
                        wire:click="syncFromLinnworks"
                        type="button"
                        class="w-full px-3 py-2 text-left hover:bg-amber-50 dark:hover:bg-amber-900/20 focus:bg-amber-50 dark:focus:bg-amber-900/20 focus:outline-none flex items-center gap-2"
                    >
                        <flux:icon.arrow-path class="size-4 text-amber-600 dark:text-amber-400" />
                        <div>
                            <div class="text-sm text-amber-600 dark:text-amber-400 font-medium">
                                Sync from Linnworks
                            </div>
                            <div class="text-xs text-amber-500 dark:text-amber-500">
                                Import location data
                            </div>
                        </div>
                    </button>
                </div>
            @endif
        @endcan
    </div>
</div>