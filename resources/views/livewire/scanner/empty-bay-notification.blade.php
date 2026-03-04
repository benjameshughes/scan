<div class="space-y-4">
    {{-- Header --}}
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-100 dark:bg-amber-900 rounded-full mb-4">
            <flux:icon.exclamation-triangle class="w-8 h-8 text-amber-600 dark:text-amber-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Empty Bay Notification</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Report this location as empty</p>
    </div>

    {{-- Notification Details --}}
    <div class="bg-amber-50 dark:bg-amber-900 border border-amber-200 dark:border-amber-700 rounded-lg p-4 space-y-3">
        {{-- Barcode Info --}}
        @if ($barcode)
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-amber-800 dark:text-amber-200">Barcode:</span>
                <span class="text-sm font-mono text-amber-900 dark:text-amber-100 bg-white dark:bg-amber-800 px-2 py-1 rounded">{{ $barcode }}</span>
            </div>
        @endif

        {{-- Product Info --}}
        @if ($product)
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-amber-800 dark:text-amber-200">Product:</span>
                    <span class="text-sm text-amber-900 dark:text-amber-100">{{ $product->name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-amber-800 dark:text-amber-200">SKU:</span>
                    <span class="text-sm font-mono text-amber-900 dark:text-amber-100">{{ $product->sku }}</span>
                </div>
            </div>
        @endif

        {{-- Notification Description --}}
        <div class="pt-2 border-t border-amber-200 dark:border-amber-700">
            <p class="text-sm text-amber-800 dark:text-amber-200">
                This will notify the warehouse team that this bay is empty and needs to be refilled.
            </p>
        </div>
    </div>

    {{-- Error Message --}}
    @if ($errorMessage)
        <flux:callout icon="exclamation-triangle" color="red">
            <flux:callout.text>{{ $errorMessage }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Action Buttons --}}
    <div class="space-y-3">
        {{-- Submit Notification Button --}}
        <flux:button
            wire:click="submitNotification"
            variant="primary"
            icon="paper-airplane"
            class="w-full"
            wire:loading.attr="disabled"
            wire:target="submitNotification"
            :disabled="$isProcessing"
        >
            <span wire:loading.remove wire:target="submitNotification">Send Empty Bay Notification</span>
            <span wire:loading wire:target="submitNotification">Sending Notification...</span>
        </flux:button>

        {{-- Cancel Button --}}
        <flux:button
            wire:click="closeNotification"
            variant="ghost"
            icon="arrow-left"
            class="w-full"
            :disabled="$isProcessing"
        >
            Back to Scanner
        </flux:button>
    </div>
</div>