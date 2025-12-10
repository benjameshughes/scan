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

    {{-- Success Message --}}
    @if ($successMessage)
        <div class="p-3 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md">
            <div class="flex justify-between items-start">
                <p class="text-sm text-green-800 dark:text-green-200 flex items-center">
                    <flux:icon.check-circle class="w-4 h-4 mr-2" />
                    {{ $successMessage }}
                </p>
                <button 
                    wire:click="clearSuccess"
                    class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200"
                >
                    <flux:icon.x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Error Message --}}
    @if ($errorMessage)
        <div class="p-3 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md">
            <div class="flex justify-between items-start">
                <p class="text-sm text-red-800 dark:text-red-200 flex items-center">
                    <flux:icon.exclamation-triangle class="w-4 h-4 mr-2 flex-shrink-0" />
                    {{ $errorMessage }}
                </p>
                <button 
                    wire:click="clearError"
                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200"
                >
                    <flux:icon.x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    @if (!$successMessage)
        <div class="space-y-3">
            {{-- Submit Notification Button --}}
            <button 
                wire:click="submitNotification"
                class="w-full flex items-center justify-center space-x-2 px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-md font-medium transition-colors"
                wire:loading.attr="disabled"
                {{ $isProcessing ? 'disabled' : '' }}
            >
                <span wire:loading.remove>
                    <flux:icon.paper-airplane class="w-4 h-4" />
                </span>
                <span wire:loading>
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                </span>
                <span wire:loading.remove>Send Empty Bay Notification</span>
                <span wire:loading>Sending Notification...</span>
            </button>

            {{-- Cancel Button --}}
            <button 
                wire:click="closeNotification"
                class="w-full flex items-center justify-center space-x-2 px-4 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
                {{ $isProcessing ? 'disabled' : '' }}
            >
                <flux:icon.arrow-left class="w-4 h-4" />
                <span>Back to Scanner</span>
            </button>
        </div>
    @else
        {{-- Close Button (After Success) --}}
        <button 
            wire:click="closeNotification"
            class="w-full flex items-center justify-center space-x-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-colors"
        >
            <flux:icon.check class="w-4 h-4" />
            <span>Continue Scanning</span>
        </button>
    @endif
</div>