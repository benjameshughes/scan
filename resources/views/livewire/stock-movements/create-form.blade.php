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
        
        <!-- Product Selection -->
        <div>
            <label for="product_search" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Product <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    type="text"
                    id="product_search"
                    wire:model.live="product_search"
                    placeholder="Search by SKU or product name..."
                    class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
                @if($selected_product)
                    <button
                        type="button"
                        wire:click="clearProduct"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <flux:icon.x-mark class="size-5" />
                    </button>
                @endif
            </div>
            
            @if($selected_product)
                <div class="mt-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                    <div class="flex items-center gap-3">
                        <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                        <div>
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ $selected_product->sku }}</p>
                            @if($selected_product->name)
                                <p class="text-xs text-green-600 dark:text-green-300">{{ $selected_product->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            
            @error('product_id')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Type at least 2 characters to search</p>
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
                    placeholder="Enter quantity..."
                    class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
                @error('quantity')
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Location Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- From Location -->
            <div>
                <label for="from_location_code" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    From Location
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
                
                <!-- From Location ID (optional) -->
                <div class="mt-2">
                    <input
                        type="text"
                        wire:model="from_location_id"
                        placeholder="Location ID (optional)"
                        class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                </div>
            </div>

            <!-- To Location -->
            <div>
                <label for="to_location_code" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    To Location
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
                
                <!-- To Location ID (optional) -->
                <div class="mt-2">
                    <input
                        type="text"
                        wire:model="to_location_id"
                        placeholder="Location ID (optional)"
                        class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                </div>
            </div>
        </div>

        <!-- Available Locations Helper -->
        @if(count($this->availableLocations) > 0)
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Available Locations</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                @foreach($this->availableLocations as $location)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $location['code'] }}
                        @if($location['name'])
                            <span class="ml-1 text-blue-600 dark:text-blue-300">({{ $location['name'] }})</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
        @endif

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
                href="{{ route('locations.movements') }}"
                variant="ghost"
                wire:navigate
            >
                Cancel
            </flux:button>
            
            <flux:button
                type="submit"
                variant="filled"
                icon="plus"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">Create Movement</span>
                <span wire:loading wire:target="save">Creating...</span>
            </flux:button>
        </div>
    </form>
</div>