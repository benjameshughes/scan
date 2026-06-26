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

        {{-- Default Location Stock Level --}}
        @if ($defaultLocationStock !== null)
            <div class="flex items-center justify-between px-3 py-2 rounded-lg {{ $defaultLocationStock === 0 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-zinc-50 dark:bg-zinc-800' }}">
                <span class="text-sm font-medium {{ $defaultLocationStock === 0 ? 'text-red-700 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}">Default Location Stock</span>
                <span class="text-sm font-bold {{ $defaultLocationStock === 0 ? 'text-red-700 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ $defaultLocationStock }}</span>
            </div>
        @endif

        {{-- Zero Stock Warning --}}
        @if ($defaultLocationStock === 0 && !$form->scanAction)
            <flux:callout icon="exclamation-triangle" color="red">
                <flux:callout.heading>No stock in the bay</flux:callout.heading>
                <flux:callout.text>Cannot decrease stock when Default location is at zero. Switch to increase or restock first.</flux:callout.text>
            </flux:callout>
        @endif

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
        @php
            $isDecrease = !$form->scanAction;
            $stockBlocked = $isDecrease && $defaultLocationStock === 0;
            $quantityExceedsStock = $isDecrease && $defaultLocationStock !== null && ($form->quantity ?? 1) > $defaultLocationStock;
        @endphp
        <flux:button
            type="submit"
            variant="primary"
            icon="check"
            class="w-full"
            wire:loading.attr="disabled"
            wire:target="save"
            :disabled="$stockBlocked"
        >
            <span wire:loading.remove wire:target="save">
                @if ($stockBlocked)
                    No Stock to Remove
                @elseif ($quantityExceedsStock)
                    Only {{ $defaultLocationStock }} Available
                @else
                    Submit Scan
                @endif
            </span>
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
            wire:loading.attr="disabled"
            wire:target="emptyBayNotification"
        >
            Empty Bay
        </flux:button>
        <flux:button
            type="button"
            wire:click="showRefillBayForm"
            icon="arrow-up-tray"
            wire:loading.attr="disabled"
            wire:target="showRefillBayForm"
        >
            <span wire:loading.remove wire:target="showRefillBayForm">Refill Bay</span>
            <span wire:loading wire:target="showRefillBayForm">Loading...</span>
        </flux:button>
    </div>
</div>
