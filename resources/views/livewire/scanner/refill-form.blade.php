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

                    <flux:button.group>
                        <flux:button wire:click="decrementRefillQuantity" icon="minus" disabled="{{$this->refillQuantity <= 1}}" />
                        <flux:input type="number" wire:model.live="refillQuantity" min="1" max="{{$this->maxRefillStock}}" clearable/>
                        <flux:button wire:click="incrementRefillQuantity" icon="plus" disabled="{{$this->refillQuantity >= $this->maxRefillStock}}"/>
                    </flux:button.group>

                    {{-- Quick Quantity Buttons (additive) --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach ([6, 12, 24, 36, 48, 60] as $qty)
                            <flux:button
                                size="sm"
                                wire:click="addRefillQuantity({{ $qty }})"
                                :disabled="$this->refillQuantity >= $this->maxRefillStock"
                            >+{{ $qty }}</flux:button>
                        @endforeach
                        <flux:button
                            size="sm"
                            variant="primary"
                            wire:click="$set('refillQuantity', {{ $this->maxRefillStock }})"
                        >Max</flux:button>
                    </div>

                    <flux:error name="refillQuantity"/>
                </div>
            @endif

            {{-- Submit Button --}}
            <flux:button type="submit" icon="arrow-up-tray" variant="primary" class="w-full">Refill Bay</flux:button>

        </form>
    @endif

    {{-- Action Buttons --}}
    <div class="flex space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
        <flux:button variant="ghost" wire:click="cancelRefill" icon="arrow-left">Product Details</flux:button>
    </div>
</div>