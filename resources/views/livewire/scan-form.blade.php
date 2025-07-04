<div class="space-y-6 p-4 dark:bg-zinc-800 dark:border-b-zinc-700">
    <form class="space-y-6" wire:submit="save">
        <!-- Barcode Input -->
        <div>
            <flux:input wire:model.live="barcode" id="barcode" name="barcode" placeholder="Enter a barcode" type="number"
                        pattern="[0-9]*" inputmode="numeric" autocomplete="off" autofocus="barcode"/>
            <flux:error name="barcode"/>
        </div>

        <!-- Quantity Input -->
        <div>
            <x-input-label for="quantity">Quantity</x-input-label>

            <div class="flex gap-2 items-center justify-end">
                <flux:input wire:model="quantity" id="quantity" name="quantity" placeholder="Quantity" type="number"
                            pattern="[0-9]*" min="1" inputmode="numeric" autocomplete="off"/>
                <flux:button icon="plus" variant="primary" wire:click="incrementQuantity"/>
            </div>

            <flux:error name="quantity"/>
        </div>

            <flux:switch wire:model.live="scanAction" label="Stock In" description="Add to stock"/>

        <!-- Submit Button -->
        <div class="flex">
            <x-primary-button class="w-full py-3 disabled:opacity-40">
                Submit Scan
                <div wire:loading.flex wire:target="save">
                    <x-icons.spinner class="w-5 h-5 text-white animate-spin"/>
                </div>
            </x-primary-button>
        </div>

        <!-- Success Message -->
        <div
                x-show="$wire.showSuccessMessage"
                x-transition.out.opacity.duration.1000ms
                x-effect="if($wire.barcodeScanned) setTimeout(() => $wire.barcodeScanned = false, 10000)"
                class="w-full mx-auto">
            <div class="flex gap-2 items-center justify-end mx-4 my-2 text-green-500 text-sm font-medium">
                <span>{{$successMessage}}</span>
                <x-icons.check-circle class="size-6"/>
            </div>
        </div>
    </form>

    {{--    Bay empty notification form--}}
    <form wire:target="bayEmptyNotification" wire:submit="emptyBayNotification">
        <flux:button type="submit" class="w-full" variant="primary">Send Notification Bay Empty</flux:button>
    </form>
</div>