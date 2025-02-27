<form class="space-y-6 p-4 dark:bg-gray-800 dark:border-gray-700" wire:submit="save">
    <!-- Barcode Input -->
    <div>
{{--        @error('barcode')--}}
{{--        <div class="alert alert-error mb-2 p-2 bg-red-100 border border-red-400 rounded">--}}
{{--            {{ $message }}--}}
{{--        </div>--}}
{{--        @enderror--}}
{{--        <x-input-label for="barcode">Barcode</x-input-label>--}}
{{--        <x-text-input--}}
{{--                wire:model.live.delay="barcode"--}}
{{--                --}}{{--                wire:change="checkBarcodeExists"--}}
{{--                id="barcode"--}}
{{--                name="barcode"--}}
{{--                class="block w-full mt-1"--}}
{{--                placeholder="Enter barcode"--}}
{{--                aria-describedby="barcode-error"--}}
{{--                type="number"--}}
{{--                pattern="[0-9]*"--}}
{{--                inputmode="numeric"--}}
{{--                autofocus--}}
{{--                autocomplete="off"--}}
{{--        />--}}
        <flux:input wire:model="barcode" id="barcode" name="barcode" placeholder="Enter a barcode" type="number" pattern="[0-9]*" inputmode="numeric" autocomplete="off" autofocus="barcode"/>
        <flux:error name="barcode" />
    </div>

    <!-- Quantity Input -->
    <div>
        <x-input-label for="quantity">Quantity</x-input-label>

        <div class="flex gap-2 items-center justify-end">
{{--            <x-text-input--}}
{{--                    type="number"--}}
{{--                    inputmode="numeric"--}}
{{--                    pattern="[0-9]*"--}}
{{--                    wire:model.live="quantity"--}}
{{--                    id="quantity"--}}
{{--                    name="quantity"--}}
{{--                    class="block w-full mt-1"--}}
{{--                    autocomplete="off"--}}
{{--            />--}}
{{--            <div>--}}
{{--                <button type="button" wire:click.debounce.100="incrementQuantity" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">--}}
{{--                    +--}}
{{--                </button>--}}
{{--            </div>--}}

            <flux:input wire:model="quantity" id="quantity" name="quantity" placeholder="Quantity" type="number" pattern="[0-9]*" inputmode="numeric" autocomplete="off" />
            <flux:button icon="plus" variant="primary" wire:click="incrementQuantity"/>
        </div>

        @error('quantity')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400" id="quantity-error">{{ $message }}</p>
        @enderror
    </div>

    <!-- Submit Button -->
    <div class="flex">
        <x-primary-button class="w-full py-3 disabled:opacity-40">
            Save
            <div wire:loading.flex wire:target="save">
                <x-icons.spinner class="w-5 h-5 text-white animate-spin"/>
            </div>
        </x-primary-button>
    </div>

    <!-- Success Message -->
    <div
            x-show="$wire.barcodeScanned"
            x-transition.out.opacity.duration.1000ms
            x-effect="if($wire.barcodeScanned) setTimeout(() => $wire.barcodeScanned = false, 3000)"
            class="w-full mx-auto">
        <div class="flex gap-2 items-center justify-end mx-4 my-2 text-green-500 text-sm font-medium">
            <span>Barcode Scanned Successfully</span>
            <x-icons.check-circle class="size-6"/>
        </div>
    </div>
</form>
