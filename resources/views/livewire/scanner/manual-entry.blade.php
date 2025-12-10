<div class="space-y-4">
    {{-- Manual Entry Header --}}
    <div class="text-center">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Manual Entry</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter barcode manually if scanning fails</p>
    </div>

    {{-- Barcode Input --}}
    <div class="space-y-2">
        <label for="barcode-input" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            Barcode
        </label>
        
        <div class="relative">
            <input 
                type="text" 
                id="barcode-input"
                wire:model.live.debounce.500ms="barcode"
                placeholder="Enter or scan barcode"
                class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-blue-500 focus:border-blue-500 focus:ring-1"
                wire:loading.attr="disabled"
            />
            
            {{-- Loading Indicator --}}
            <div class="absolute right-3 top-1/2 transform -translate-y-1/2" wire:loading>
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
            </div>
        </div>

        {{-- Validation Error --}}
        @error('barcode')
            <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center">
                <flux:icon.exclamation-triangle class="w-3 h-3 mr-1" />
                {{ $message }}
            </p>
        @enderror

        {{-- Help Text --}}
        @if (!$barcode && !$errors->has('barcode'))
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Enter a barcode to search for products
            </p>
        @endif
    </div>

    {{-- Clear Barcode --}}
    @if ($barcode)
        <div class="flex justify-center">
            <button 
                wire:click="$set('barcode', '')"
                class="flex items-center space-x-2 px-4 py-2 text-zinc-600 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 text-sm transition-colors"
            >
                <flux:icon.x-mark class="w-4 h-4" />
                <span>Clear Input</span>
            </button>
        </div>
    @endif
</div>