<div>
    <!-- Messages -->
    @if($success_message)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                    <span class="text-sm text-green-700 dark:text-green-300">{{ $success_message }}</span>
                </div>
            </div>
        </div>
    @endif

    @if($error_message)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                    <span class="text-sm text-red-700 dark:text-red-300">{{ $error_message }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        
        <!-- Product Information (Read-only) -->
        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-md p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-md flex items-center justify-center">
                    <flux:icon.cube class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Product Information</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Product cannot be changed after creation</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">SKU</label>
                    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-md px-3 py-2">
                        <span class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $movement->product->sku }}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Product Name</label>
                    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-md px-3 py-2">
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $movement->product->name ?: 'No name available' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movement Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Column -->
            <div class="space-y-6">
                
                <!-- Movement Type -->
                <div>
                    <flux:label for="type" required>Movement Type</flux:label>
                    <flux:select id="type" wire:model="type">
                        @foreach($this->movementTypes as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="type" />
                </div>

                <!-- Quantity -->
                <div>
                    <flux:label for="quantity" required>Quantity</flux:label>
                    <flux:input
                        type="number"
                        id="quantity"
                        wire:model="quantity"
                        min="1"
                        icon="calculator"
                    />
                    <flux:error name="quantity" />
                </div>

                <!-- Notes -->
                <div>
                    <flux:label for="notes">Notes</flux:label>
                    <flux:textarea
                        id="notes"
                        wire:model="notes"
                        rows="4"
                        placeholder="Optional notes about this movement..."
                        resize="vertical"
                    />
                    <flux:error name="notes" />
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum 500 characters</div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                
                <!-- Location Information Card -->
                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-md p-4 space-y-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <flux:icon.map-pin class="size-4" />
                        Location Transfer
                    </h3>
                    
                    <!-- From Location -->
                    <div>
                        <flux:label for="from_location_code">From Location</flux:label>
                        <flux:input
                            id="from_location_code"
                            wire:model="from_location_code"
                            placeholder="e.g., 12B-3"
                            icon="arrow-up-tray"
                        />
                        <flux:error name="from_location_code" />
                    </div>

                    <!-- Transfer Arrow -->
                    <div class="flex justify-center">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <flux:icon.arrow-down class="size-4 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>

                    <!-- To Location -->
                    <div>
                        <flux:label for="to_location_code">To Location</flux:label>
                        <flux:input
                            id="to_location_code"
                            wire:model="to_location_code"
                            placeholder="e.g., Default"
                            icon="arrow-down-tray"
                        />
                        <flux:error name="to_location_code" />
                    </div>
                </div>
            </div>
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