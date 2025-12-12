<div class="space-y-4">
    {{-- Success Indicator --}}
    <div class="flex gap-10">
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

    {{-- Action Buttons --}}
    <div class="flex space-x-3">
        <button 
            wire:click="startNewScan"
            class="flex-1 flex items-center justify-center space-x-2 px-4 py-3 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
        >
            <flux:icon.arrow-path class="w-4 h-4" />
            <span>New Scan</span>
        </button>

        <div class="flex-1">
            {{-- This space will be filled by the scan form component --}}
        </div>
    </div>
</div>