<div class="space-y-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" dismissible>
            {{ session('message') }}
        </flux:callout>
    @endif

    {{-- Header with Search and Actions --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1 max-w-sm">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search SKU, name, barcode..."
                icon="magnifying-glass"
                clearable
            />
        </div>

        <div class="flex items-center gap-2">
            {{-- Bulk Actions Dropdown --}}
            @if (count($selected) > 0)
                <flux:dropdown>
                    <flux:button icon="chevron-down" iconTrailing>
                        {{ count($selected) }} selected
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item wire:click="syncSelected" icon="arrow-path">
                            Sync with Linnworks
                        </flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item wire:click="deleteSelected" wire:confirm="Delete selected products?" icon="trash" variant="danger">
                            Delete Selected
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            @endif

            {{-- Filter Toggle --}}
            <flux:modal.trigger name="filters">
                <flux:button icon="funnel" variant="ghost">
                    Filters
                    @if ($hasBarcode2 || $updatedAfter)
                        <flux:badge size="sm" color="blue" class="ml-1">Active</flux:badge>
                    @endif
                </flux:button>
            </flux:modal.trigger>

            <flux:button icon="plus" variant="primary" href="{{ route('products.create') }}">
                New Product
            </flux:button>
        </div>
    </div>

    {{-- Filters Modal --}}
    <flux:modal name="filters" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Filters</flux:heading>

            <div class="space-y-4">
                <flux:select wire:model.live="hasBarcode2" label="Secondary Barcode" placeholder="All products">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="1">Has Barcode 2</flux:select.option>
                    <flux:select.option value="0">No Barcode 2</flux:select.option>
                </flux:select>

                <flux:input wire:model.live="updatedAfter" type="date" label="Updated After" />
            </div>

            <div class="flex justify-between pt-4">
                <flux:button wire:click="clearFilters" variant="ghost">
                    Clear All
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="primary">Done</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Table --}}
    <flux:table :paginate="$products">
        <flux:table.columns>
            <flux:table.column class="w-12">
                <flux:checkbox wire:model.live="selectAll" />
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'sku'" :direction="$sortDirection" wire:click="sort('sku')">
                SKU
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortDirection" wire:click="sort('name')">
                Name
            </flux:table.column>
            <flux:table.column>Primary Barcode</flux:table.column>
            <flux:table.column>Barcode 2</flux:table.column>
            <flux:table.column>Barcode 3</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'updated_at'" :direction="$sortDirection" wire:click="sort('updated_at')">
                Updated
            </flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($products as $product)
                <flux:table.row :key="$product->id">
                    <flux:table.cell>
                        <flux:checkbox wire:model.live="selected" value="{{ $product->id }}" />
                    </flux:table.cell>
                    <flux:table.cell variant="strong">
                        <span class="font-mono text-sm">{{ $product->sku }}</span>
                    </flux:table.cell>
                    <flux:table.cell>{{ $product->name }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($product->barcode)
                            <span class="font-mono text-sm text-zinc-600 dark:text-zinc-400">{{ $product->barcode }}</span>
                        @else
                            <span class="text-zinc-400 dark:text-zinc-500">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($product->barcode_2)
                            <span class="font-mono text-sm text-zinc-600 dark:text-zinc-400">{{ $product->barcode_2 }}</span>
                        @else
                            <span class="text-zinc-400 dark:text-zinc-500">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($product->barcode_3)
                            <span class="font-mono text-sm text-zinc-600 dark:text-zinc-400">{{ $product->barcode_3 }}</span>
                        @else
                            <span class="text-zinc-400 dark:text-zinc-500">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                        {{ $product->updated_at->diffForHumans() }}
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" href="{{ route('products.show', $product) }}" />
                            <flux:button size="sm" variant="ghost" icon="pencil" href="{{ route('products.edit', $product) }}" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $product->id }})" wire:confirm="Delete this product?" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-8">
                        <div class="flex flex-col items-center gap-2 text-zinc-500">
                            <flux:icon.inbox class="size-8" />
                            <span>No products found</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
