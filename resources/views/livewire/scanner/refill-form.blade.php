<div class="space-y-4">
    {{-- Form Header --}}
    <div class="text-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Refill Bay
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Transfer stock from another location
        </p>
    </div>

    {{-- Loading State --}}
    @if ($isProcessingRefill)
        <div class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Preparing refill form...</p>
        </div>
    @endif

    {{-- Refill Error --}}
    @if ($refillError)
        <div class="p-3 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md">
            <div class="flex justify-between items-start">
                <p class="text-sm text-red-800 dark:text-red-200 flex items-center">
                    <flux:icon.exclamation-triangle class="w-4 h-4 mr-2 flex-shrink-0" />
                    {{ $refillError }}
                </p>
                <button
                    wire:click="clearRefillError"
                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200"
                >
                    <flux:icon.x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Refill Form --}}
    @if (!$isProcessingRefill && !$refillError)
        <form wire:submit="submitRefill" class="space-y-4">
            {{-- From Location Selection --}}
            @if (!empty($availableLocations))
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Transfer From Location <span class="text-red-500">*</span>
                    </label>

                    <flux:select variant="listbox" wire:model.live="fromLocationId">
                        @foreach ($this->filteredFromLocations as $location)
                            <flux:select.option value="{{ $location['StockLocationId'] }}">
                                {{ $location['LocationName'] }} ({{ $location['Quantity'] }} available)
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    @error('fromLocationId')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center">
                            <flux:icon.exclamation-triangle class="w-3 h-3 mr-1" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endif

            {{-- To Location Selection --}}
            @if (!empty($allLocations))
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Transfer To Location <span class="text-red-500">*</span>
                    </label>

                    <flux:select variant="listbox" searchable clearable wire:model.live="toLocationId">
                        @foreach ($this->filteredToLocations as $location)
                            <flux:select.option value="{{ $location['StockLocationId'] }}">
                                {{ $location['LocationName'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    @error('toLocationId')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center">
                            <flux:icon.exclamation-triangle class="w-3 h-3 mr-1" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endif

            {{-- Quantity Selection --}}
            @if ($fromLocationId)
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Quantity to Transfer <span class="text-red-500">*</span>
                        @if ($this->maxRefillStock > 0)
                            <span class="text-xs text-gray-500 dark:text-gray-400">(Max: {{ $this->maxRefillStock }})</span>
                        @endif
                    </label>

                    <div class="flex items-center space-x-3">
                        {{-- Decrement Button --}}
                        <button
                            type="button"
                            wire:click="decrementRefillQuantity"
                            class="flex items-center justify-center w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
                            {{ $refillQuantity <= 1 ? 'disabled' : '' }}
                        >
                            <flux:icon.minus class="w-4 h-4" />
                        </button>

                        {{-- Quantity Input --}}
                        <div class="flex-1">
                            <input
                                type="number"
                                wire:model.live="refillQuantity"
                                min="1"
                                max="{{ $this->maxRefillStock }}"
                                class="w-full px-4 py-2 text-center border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-blue-500 focus:border-blue-500 focus:ring-1"
                            />
                        </div>

                        {{-- Increment Button --}}
                        <button
                            type="button"
                            wire:click="incrementRefillQuantity"
                            class="flex items-center justify-center w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
                            {{ $refillQuantity >= $this->maxRefillStock ? 'disabled' : '' }}
                        >
                            <flux:icon.plus class="w-4 h-4" />
                        </button>
                    </div>

                    @error('refillQuantity')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center">
                            <flux:icon.exclamation-triangle class="w-3 h-3 mr-1" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endif

            {{-- Submit Button --}}
            <button
                type="submit"
                class="w-full flex items-center justify-center space-x-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                {{ !$fromLocationId || !$toLocationId || $refillQuantity < 1 ? 'disabled' : '' }}
            >
                <span wire:loading.remove>
                    <flux:icon.arrow-up-tray class="w-4 h-4" />
                </span>
                <span wire:loading>
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                </span>
                <span wire:loading.remove>Submit Refill</span>
                <span wire:loading>Processing...</span>
            </button>
        </form>
    @endif

    {{-- Action Buttons --}}
    <div class="flex space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
        <button
            wire:click="cancelRefill"
            class="flex-1 flex items-center justify-center space-x-2 px-4 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
        >
            <flux:icon.arrow-left class="w-4 h-4" />
            <span>Back to Scanner</span>
        </button>
    </div>
</div>