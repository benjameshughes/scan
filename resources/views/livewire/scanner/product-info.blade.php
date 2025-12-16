<div class="relative space-y-4">
    {{-- Dismiss/New Scan Button --}}
    <div class="absolute top-0 right-0">
        <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="$dispatch('new-scan-requested')" />
    </div>

    {{-- Success Indicator --}}
    <div class="flex gap-10 pr-10">
        @if($this->product)
            <div class="inline-flex items-center justify-center">
                <flux:icon.check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
        <div class="flex-col">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $this->product->name ?? 'Product not found'}} - {{ $this->product->sku }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Barcode: {{ $barcode }}</p>
        </div>
        @else
            <div class="inline-flex items-center justify-center">
                <flux:icon.x class="w-8 h-8 text-red-600 dark:text-red-400" />
            </div>
            <div class="flex-col">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">No Product</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Barcode: {{ $barcode }}</p>
            </div>
        @endif
    </div>


</div>