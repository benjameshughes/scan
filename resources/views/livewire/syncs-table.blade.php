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
                placeholder="Search barcodes..."
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
                        <flux:menu.item wire:click="retrySync" icon="arrow-path">
                            Retry Sync
                        </flux:menu.item>
                        <flux:menu.item wire:click="retryFailedOnly" icon="arrow-path">
                            Retry Failed Only
                        </flux:menu.item>
                        <flux:menu.item wire:click="markSynced" icon="check">
                            Mark as Synced
                        </flux:menu.item>
                        <flux:menu.item wire:click="clearErrors" icon="x-circle">
                            Clear Errors
                        </flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item wire:click="deleteSelected" wire:confirm="Delete selected scans?" icon="trash" variant="danger">
                            Delete Selected
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            @endif

            {{-- Filter Toggle --}}
            <flux:modal.trigger name="filters">
                <flux:button icon="funnel" variant="ghost">
                    Filters
                    @if ($syncStatus || $errorType || $submitted !== '' || $action || $dateFrom || $dateTo)
                        <flux:badge size="sm" color="blue" class="ml-1">Active</flux:badge>
                    @endif
                </flux:button>
            </flux:modal.trigger>

            <flux:button icon="plus" variant="primary" href="{{ route('scans.create') }}">
                New Scan
            </flux:button>
        </div>
    </div>

    {{-- Filters Modal --}}
    <flux:modal name="filters" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">Filters</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model.live="syncStatus" label="Sync Status" placeholder="All statuses">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="synced">Synced</flux:select.option>
                    <flux:select.option value="failed">Failed</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="errorType" label="Error Type" placeholder="All errors">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="network">Network Error</flux:select.option>
                    <flux:select.option value="auth">Authentication Error</flux:select.option>
                    <flux:select.option value="rate_limit">Rate Limit</flux:select.option>
                    <flux:select.option value="product_not_found">Product Not Found</flux:select.option>
                    <flux:select.option value="api_error">API Error</flux:select.option>
                    <flux:select.option value="timeout">Timeout</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="submitted" label="Submission Status" placeholder="All">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="1">Submitted</flux:select.option>
                    <flux:select.option value="0">Pending</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="action" label="Action Type" placeholder="All actions">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="increase">Increase</flux:select.option>
                    <flux:select.option value="decrease">Decrease</flux:select.option>
                </flux:select>

                <flux:input wire:model.live="dateFrom" type="date" label="From Date" />
                <flux:input wire:model.live="dateTo" type="date" label="To Date" />
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
    <flux:table :paginate="$scans">
        <flux:table.columns>
            <flux:table.column class="w-12">
                <flux:checkbox wire:model.live="selectAll" />
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'barcode'" :direction="$sortDirection" wire:click="sort('barcode')">
                Barcode
            </flux:table.column>
            <flux:table.column>Qty</flux:table.column>
            <flux:table.column>Action</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">
                Date
            </flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($scans as $scan)
                <flux:table.row :key="$scan->id">
                    <flux:table.cell>
                        <flux:checkbox wire:model.live="selected" value="{{ $scan->id }}" />
                    </flux:table.cell>
                    <flux:table.cell variant="strong">
                        <span class="font-mono text-sm">{{ $scan->barcode }}</span>
                    </flux:table.cell>
                    <flux:table.cell>{{ $scan->quantity }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$scan->action === 'increase' ? 'green' : 'red'">
                            {{ ucfirst($scan->action ?? 'decrease') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge
                            size="sm"
                            :color="match($scan->sync_status) {
                                'synced' => 'green',
                                'failed' => 'red',
                                default => 'yellow'
                            }"
                            :icon="match($scan->sync_status) {
                                'synced' => 'check-circle',
                                'failed' => 'x-circle',
                                default => 'clock'
                            }"
                        >
                            {{ ucfirst($scan->sync_status ?? 'pending') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                        {{ $scan->created_at->diffForHumans() }}
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" href="{{ route('scans.show', $scan) }}" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $scan->id }})" wire:confirm="Delete this scan?" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-8">
                        <div class="flex flex-col items-center gap-2 text-zinc-500">
                            <flux:icon.inbox class="size-8" />
                            <span>No scans found</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
