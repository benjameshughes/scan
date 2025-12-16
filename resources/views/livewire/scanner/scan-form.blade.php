<div class="space-y-4">
    {{-- Form Header --}}
    <div class="text-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Submit Stock Adjustment</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Adjust quantity and submit</p>
    </div>

    {{-- Scan Form --}}
    <form wire:submit="save" class="space-y-4">
        {{-- Quantity Control --}}
        <div class="space-y-2">
            <flux:label for="quantity">Quantity</flux:label>
            <div class="w-full">
                <flux:button.group>
                    <flux:button
                        type="button"
                        icon="minus"
                        wire:click="decrementQuantity"
                        :disabled="($form->quantity ?? 1) <= 1"
                    />
                    <flux:input
                        id="quantity"
                        type="number"
                        wire:model.live.debounce.300ms="form.quantity"
                        min="1"
                        max="9999"
                        class="text-center"
                    />
                    <flux:button
                        type="button"
                        icon="plus"
                        wire:click="incrementQuantity"
                        :disabled="($form->quantity ?? 1) >= 9999"
                    />
                </flux:button.group>
            </div>

            {{-- Quantity Error - Real-time display --}}
            <flux:error name="form.quantity" />
        </div>

        {{-- Scan Action Toggle --}}
        <flux:callout icon="triangle-alert" variant="secondary" inline>
            <flux:callout.heading>Increase Stock</flux:callout.heading>
            <flux:callout.text>Tick to <strong>increase</strong> stock instead of decreasing</flux:callout.text>
            <x-slot name="controls">
                <flux:checkbox wire:model.live="form.scanAction" />
            </x-slot>
        </flux:callout>

        {{-- Form-level Error --}}
        @error('form')
            <flux:callout icon="exclamation-triangle" color="red">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        {{-- Barcode Error (if validation fails on submit) --}}
        @error('form.barcode')
            <flux:callout icon="exclamation-triangle" color="red">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        {{-- Submit Button --}}
        <flux:button
            type="submit"
            variant="primary"
            icon="check"
            class="w-full"
            wire:loading.attr="disabled"
            wire:target="save"
        >
            <span wire:loading.remove wire:target="save">Submit Scan</span>
            <span wire:loading wire:target="save">Submitting...</span>
        </flux:button>
    </form>

    {{-- Additional Actions --}}
    <div class="grid grid-cols-2 gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
        {{-- Empty Bay Notification --}}
        <flux:button
            type="button"
            wire:click="emptyBayNotification"
            icon="exclamation-triangle"
        >
            Empty Bay
        </flux:button>
        <flux:button
            type="button"
            wire:click="showRefillBayForm"
            icon="arrow-up-tray"
        >
            Refill Bay
        </flux:button>
    </div>
</div>
