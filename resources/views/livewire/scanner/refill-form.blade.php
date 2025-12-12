<div class="space-y-4">
    {{-- Form Header --}}
    <div class="text-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            @if ($isEmailRefill) Email Refill Request @else Refill Bay @endif
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            @if ($isEmailRefill) 
                Send refill request via email
            @else 
                Transfer stock from another location
            @endif
        </p>
    </div>

    {{-- Product Info --}}
    @if ($product)
        <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
                <flux:icon.cube class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                <div>
                    <h4 class="font-medium text-blue-900 dark:text-blue-100">{{ $product->name }}</h4>
                    <p class="text-sm text-blue-700 dark:text-blue-300">SKU: {{ $product->sku }}</p>
                </div>
            </div>
        </div>
    @endif

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

    {{-- Refill Success --}}
    @if ($refillSuccess)
        <div class="p-3 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md">
            <p class="text-sm text-green-800 dark:text-green-200 flex items-center">
                <flux:icon.check-circle class="w-4 h-4 mr-2" />
                {{ $refillSuccess }}
            </p>
        </div>
    @endif

    {{-- Refill Form (Non-Email) --}}
    @if (!$isEmailRefill && !$isProcessingRefill && !$refillError && !$refillSuccess)
        <form wire:submit="submitRefill" class="space-y-4">
            {{-- Location Selection --}}
            @if (!empty($this->smartLocationSelectorData))
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Transfer From Location
                    </label>
                    
                    <select 
                        wire:model.live="selectedLocationId"
                        class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-blue-500 focus:border-blue-500 focus:ring-1"
                    >
                        <option value="">Select a location...</option>
                        @foreach ($this->smartLocationSelectorData as $location)
                            <option value="{{ $location['StockLocationId'] }}">
                                {{ $location['LocationName'] }} ({{ $location['Quantity'] }} available)
                            </option>
                        @endforeach
                    </select>

                    @error('selectedLocationId')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center">
                            <flux:icon.exclamation-triangle class="w-3 h-3 mr-1" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endif

            {{-- Quantity Selection --}}
            @if ($selectedLocationId)
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Quantity to Transfer
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
                class="w-full flex items-center justify-center space-x-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium transition-colors"
                wire:loading.attr="disabled"
                {{ !$selectedLocationId || $refillQuantity < 1 ? 'disabled' : '' }}
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

    {{-- Email Refill Form --}}
    @if ($isEmailRefill && !$isProcessingRefill && !$refillError && !$refillSuccess)
        <form wire:submit="submitRefill" class="space-y-4">
            <div class="text-center p-4 bg-amber-50 dark:bg-amber-900 border border-amber-200 dark:border-amber-700 rounded-md">
                <flux:icon.envelope class="w-8 h-8 text-amber-600 dark:text-amber-400 mx-auto mb-2" />
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    This will send an email notification for bay refill
                </p>
            </div>

            <button 
                type="submit"
                class="w-full flex items-center justify-center space-x-2 px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-md font-medium transition-colors"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    <flux:icon.paper-airplane class="w-4 h-4" />
                </span>
                <span wire:loading>
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                </span>
                <span wire:loading.remove>Send Email Request</span>
                <span wire:loading>Sending...</span>
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