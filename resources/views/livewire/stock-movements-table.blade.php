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
                placeholder="Search SKU, product, location..."
                icon="magnifying-glass"
                clearable
            />
        </div>

        <div class="flex items-center gap-2">
            {{-- Filter Toggle --}}
            <flux:modal.trigger name="filters">
                <flux:button icon="funnel" variant="ghost">
                    Filters
                    @if ($movementType || $locationFilter || $dateFrom !== now()->subDays(30)->format('Y-m-d') || $dateTo !== now()->format('Y-m-d'))
                        <flux:badge size="sm" color="blue" class="ml-1">Active</flux:badge>
                    @endif
                </flux:button>
            </flux:modal.trigger>

            @if ($canCreate)
                <flux:button icon="plus" variant="primary" href="{{ route('locations.movements.create') }}">
                    New Movement
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Filters Modal --}}
    <flux:modal name="filters" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">Filters</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model.live="dateFrom" type="date" label="From Date" />
                <flux:input wire:model.live="dateTo" type="date" label="To Date" />

                <flux:select wire:model.live="movementType" label="Movement Type" placeholder="All types">
                    <flux:select.option value="">All Types</flux:select.option>
                    <flux:select.option value="bay_refill">Bay Refill</flux:select.option>
                    <flux:select.option value="manual_transfer">Manual Transfer</flux:select.option>
                    <flux:select.option value="scan_adjustment">Scan Adjustment</flux:select.option>
                </flux:select>

                <flux:input wire:model.live.debounce.300ms="locationFilter" label="Location" placeholder="Filter by location code..." />
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
    <flux:table :paginate="$movements">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortField === 'moved_at'" :direction="$sortDirection" wire:click="sort('moved_at')">
                Date/Time
            </flux:table.column>
            <flux:table.column>SKU</flux:table.column>
            <flux:table.column>Product</flux:table.column>
            <flux:table.column>Movement</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'quantity'" :direction="$sortDirection" wire:click="sort('quantity')">
                Qty
            </flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>User</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($movements as $movement)
                <flux:table.row :key="$movement->id">
                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                        {{ $movement->moved_at->format('M j, Y g:i A') }}
                    </flux:table.cell>
                    <flux:table.cell variant="strong">
                        <span class="font-mono text-sm">{{ $movement->product?->sku ?? '-' }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($movement->product?->name)
                            <span title="{{ $movement->product->name }}">
                                {{ Str::limit($movement->product->name, 30) }}
                            </span>
                        @else
                            <span class="text-zinc-400">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1 text-sm text-zinc-600 dark:text-zinc-400">
                            <span class="font-mono">{{ $movement->from_location_code ?? 'Unknown' }}</span>
                            <flux:icon.arrow-right class="size-3" />
                            <span class="font-mono">{{ $movement->to_location_code ?? 'Unknown' }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="text-center font-medium">
                        {{ number_format($movement->quantity) }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $typeColor = match($movement->type) {
                                'bay_refill' => 'green',
                                'manual_transfer' => 'blue',
                                'scan_adjustment' => 'amber',
                                default => 'zinc',
                            };
                        @endphp
                        <flux:badge size="sm" :color="$typeColor">
                            {{ $movement->formatted_type }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                        {{ $movement->user?->name ?? '-' }}
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" wire:click="view({{ $movement->id }})" />
                            @if ($canEdit)
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $movement->id }})" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-8">
                        <div class="flex flex-col items-center gap-2 text-zinc-500">
                            <flux:icon.arrow-path class="size-8" />
                            <span>No stock movements found</span>
                            @if ($search || $movementType || $locationFilter)
                                <flux:button wire:click="clearFilters" size="sm" variant="ghost">
                                    Clear Filters
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
