<div class="space-y-4">
    {{-- Form Header --}}
    <div class="text-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Record Scan</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Adjust quantity and submit</p>
    </div>

    {{-- Scan Form --}}
    <form wire:submit="save" class="space-y-4">
        {{-- Quantity Control --}}
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                Quantity
            </label>
            
            <div class="flex items-center space-x-3">
                {{-- Decrement Button --}}
                <button 
                    type="button"
                    wire:click="decrementQuantity"
                    class="flex items-center justify-center w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
                    {{ $quantity <= 1 ? 'disabled' : '' }}
                >
                    <flux:icon.minus class="w-4 h-4" />
                </button>

                {{-- Quantity Input --}}
                <div class="flex-1">
                    <input 
                        type="number" 
                        wire:model.live="quantity"
                        min="1"
                        class="w-full px-4 py-2 text-center border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-blue-500 focus:border-blue-500 focus:ring-1"
                    />
                </div>

                {{-- Increment Button --}}
                <button 
                    type="button"
                    wire:click="incrementQuantity"
                    class="flex items-center justify-center w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
                >
                    <flux:icon.plus class="w-4 h-4" />
                </button>
            </div>

            {{-- Quantity Error --}}
            @error('quantity')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center">
                    <flux:icon.exclamation-triangle class="w-3 h-3 mr-1" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Scan Action Toggle --}}
        <div class="flex items-center space-x-3">
            <input 
                type="checkbox" 
                id="scan-action"
                wire:model="scanAction"
                class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500 focus:ring-1"
            />
            <label for="scan-action" class="text-sm text-gray-700 dark:text-gray-200">
                Mark as addition (unchecked = removal)
            </label>
        </div>

        {{-- Form-level Error --}}
        @error('form')
            <div class="p-3 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md">
                <p class="text-sm text-red-800 dark:text-red-200 flex items-center">
                    <flux:icon.exclamation-triangle class="w-4 h-4 mr-2" />
                    {{ $message }}
                </p>
            </div>
        @enderror

        {{-- Submit Button --}}
        <button 
            type="submit"
            class="w-full flex items-center justify-center space-x-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-colors"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove>
                <flux:icon.check class="w-4 h-4" />
            </span>
            <span wire:loading>
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            </span>
            <span wire:loading.remove>Submit Scan</span>
            <span wire:loading>Processing...</span>
        </button>
    </form>

    {{-- Additional Actions --}}
    <div class="grid grid-cols-2 gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
        {{-- Empty Bay Notification --}}
        <button 
            type="button"
            wire:click="emptyBayNotification"
            class="flex items-center justify-center space-x-2 px-4 py-2 bg-amber-100 hover:bg-amber-200 dark:bg-amber-900 dark:hover:bg-amber-800 text-amber-900 dark:text-amber-100 rounded-md text-sm transition-colors"
        >
            <flux:icon.exclamation-triangle class="w-4 h-4" />
            <span>Empty Bay</span>
        </button>

        {{-- Refill Bay Form --}}
        <button 
            type="button"
            wire:click="showRefillBayForm"
            class="flex items-center justify-center space-x-2 px-4 py-2 bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:hover:bg-green-800 text-green-900 dark:text-green-100 rounded-md text-sm transition-colors"
        >
            <flux:icon.arrow-up-tray class="w-4 h-4" />
            <span>Refill Bay</span>
        </button>
    </div>
</div>