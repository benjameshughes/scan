<div class="space-y-4">
    @if ($barcodeScanned)
        {{-- Transitional loading state while parent processes barcode-processed event --}}
        <div class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Fetching product details...</p>
        </div>
    @else
        {{-- Manual Entry Header --}}
        <div class="text-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Manual Entry</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter a barcode or SKU manually</p>
        </div>

        {{-- Barcode Input --}}
        <div class="space-y-2">
            <label for="barcode-input" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                Barcode / SKU
            </label>

            <div class="relative">

                <flux:input
                    type="text"
                    id="barcode-input"
                    placeholder="Enter barcode or SKU (e.g. 123-456)"
                    x-data="{
                        timer: null,
                        destroy() { clearTimeout(this.timer) }
                    }"
                    x-on:input="
                        clearTimeout(timer);
                        let raw = $event.target.value.replace(/[^0-9]/g, '');
                        $event.target.value = raw;
                        timer = setTimeout(() => {
                            let v = raw;
                            if (v.length === 6 && !v.startsWith('505903')) { // 505903 = EAN barcode prefix, don't format as SKU
                                v = v.slice(0, 3) + '-' + v.slice(3);
                                $event.target.value = v;
                            }
                            $wire.set('barcode', v);
                        }, 800);
                    "
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
                    Enter a barcode (13 digits) or SKU (e.g. 123-456)
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
    @endif
</div>