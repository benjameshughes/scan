<form class="space-y-6" wire:submit.prevent="save">
    <!-- Barcode Input -->
    <div>
        <x-input-label for="barcode">Barcode</x-input-label>
        <x-text-input
                wire:model="barcode"
                id="barcode"
                name="barcode"
                class="block w-full mt-1"
                placeholder="Enter barcode"
                aria-describedby="barcode-error"
        />
        @error('barcode')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400" id="barcode-error">{{ $message }}</p>
        @enderror
    </div>

    <!-- Quantity Input -->
    <div>
        <x-input-label for="quantity">Quantity</x-input-label>
        <x-text-input
                type="number"
                wire:model="quantity"
                id="quantity"
                name="quantity"
                class="block w-full mt-1"
                placeholder="1"
                min="1"
                aria-describedby="quantity-error"
        />
        @error('quantity')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400" id="quantity-error">{{ $message }}</p>
        @enderror
    </div>

    <!-- Submit Button -->
    <div>
        <x-primary-button class="w-full py-3">Save</x-primary-button>
    </div>

</form>
