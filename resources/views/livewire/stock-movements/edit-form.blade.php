<div>
    <!-- Messages -->
    @if($success_message)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                <span class="text-sm text-green-700 dark:text-green-300">{{ $success_message }}</span>
            </div>
        </div>
    @endif

    @if($error_message)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                <span class="text-sm text-red-700 dark:text-red-300">{{ $error_message }}</span>
            </div>
        </div>
    @endif

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        
        <!-- Product Information (Read-only) -->
        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-md p-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Product Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">SKU</label>
                    <p class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $movement->product->sku }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Product Name</label>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $movement->product->name ?: 'No name available' }}</p>
                </div>
            </div>
        </div>

        <!-- Movement Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Movement Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    Movement Type <span class="text-red-500">*</span>
                </label>
                <select
                    id="type"
                    wire:model="type"
                    class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
                    @foreach($this->movementTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    Quantity <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    id="quantity"
                    wire:model="quantity"
                    min="1"
                    class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
                @error('quantity')
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Location Codes -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- From Location -->
            <div>
                <label for="from_location_code" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    From Location Code
                </label>
                <input
                    type="text"
                    id="from_location_code"
                    wire:model="from_location_code"
                    placeholder="e.g., 12B-3"
                    class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
                @error('from_location_code')
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- To Location -->
            <div>
                <label for="to_location_code" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    To Location Code
                </label>
                <input
                    type="text"
                    id="to_location_code"
                    wire:model="to_location_code"
                    placeholder="e.g., Default"
                    class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
                @error('to_location_code')
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Notes
            </label>
            <textarea
                id="notes"
                wire:model="notes"
                rows="3"
                placeholder="Optional notes about this movement..."
                class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-vertical"
            ></textarea>
            @error('notes')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum 500 characters</p>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-6 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button
                href="{{ route('locations.movements.show', $movement) }}"
                variant="ghost"
                wire:navigate
            >
                Cancel
            </flux:button>
            
            <flux:button
                type="submit"
                variant="filled"
                icon="check"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">Update Movement</span>
                <span wire:loading wire:target="save">Updating...</span>
            </flux:button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('movement-updated', () => {
            setTimeout(() => {
                window.location.href = '{{ route("locations.movements.show", $movement) }}';
            }, 1500);
        });
    });
</script>