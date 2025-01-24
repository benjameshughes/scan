<form class="space-y-6 p-4" wire:submit="save">

    <!-- Success Message -->
    <div
            x-show="$wire.showSuccessMessage"
            x-transition.out.opacity.duration.1000ms
            x-effect="if($wire.showSuccessMessage) setTimeout(() => $wire.showSuccessMessage = false, 3000)"
            class="w-full mx-auto">
        <div class="flex gap-2 items-center justify-end mx-4 my-2 text-green-500 text-sm font-medium">
            <span>Scan Saved Successfully</span>
            <x-icons.check-circle class="size-6"/>
        </div>
    </div>

    <!-- Barcode Input -->
    <div>
        @error('barcode')
        <div class="alert alert-error mb-2 p-2 bg-red-100 border border-red-400 rounded">
            {{ $message }}
        </div>
        @enderror
        <x-input-label for="barcode">Barcode</x-input-label>
        <x-text-input
                wire:model.live.delay="barcode"
{{--                wire:change="checkBarcodeExists"--}}
                id="barcode"
                name="barcode"
                class="block w-full mt-1"
                placeholder="Enter barcode"
                aria-describedby="barcode-error"
                type="number"
                pattern="[0-9]*"
                inputmode="numeric"
        />
    </div>

    <!-- Quantity Input -->
    <div>
        <x-input-label for="quantity">Quantity</x-input-label>
        <x-text-input
                type="number"
                inputmode="numeric"
                pattern="[0-9]*"
                wire:model="quantity"
                id="quantity"
                name="quantity"
                class="block w-full mt-1"
        />
        @error('quantity')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400" id="quantity-error">{{ $message }}</p>
        @enderror
    </div>

    <!-- Submit Button -->
    <div class="flex">
        <x-primary-button class="w-fulls py-3 disabled:opacity-40">
            Save
        <div wire:loading.flex wire:target="save">
            <x-icons.spinner class="w-5 h-5 text-white animate-spin"/>
        </div>
        </x-primary-button>
    </div>
</form>
