<div class="space-y-6">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl">Pending Product Updates</flux:heading>
            <flux:text class="mt-1">Review changes detected during Linnworks sync</flux:text>
        </div>

        <!-- Bulk Actions -->
        @if(count($selectedUpdates) > 0 && $filter === 'pending')
            <div class="flex items-center gap-2">
                <flux:badge color="amber">{{ count($selectedUpdates) }} selected</flux:badge>
                <flux:button wire:click="bulkApprove" variant="primary" size="sm" icon="check">
                    Approve All
                </flux:button>
                <flux:button wire:click="bulkReject" variant="danger" size="sm" icon="x-mark">
                    Reject All
                </flux:button>
            </div>
        @endif
    </div>

    <!-- Filters Bar -->
    <flux:card class="!p-4">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by product name, SKU, or barcode..."
                    icon="magnifying-glass"
                    clearable
                />
            </div>

            <!-- Status Filter -->
            <flux:select wire:model.live="filter" class="w-full lg:w-48">
                <flux:select.option value="pending">Pending ({{ $pendingCount }})</flux:select.option>
                <flux:select.option value="auto_accepted">Auto-Accepted ({{ $autoAcceptedCount }})</flux:select.option>
                <flux:select.option value="approved">Approved ({{ $approvedCount }})</flux:select.option>
                <flux:select.option value="rejected">Rejected ({{ $rejectedCount }})</flux:select.option>
            </flux:select>

            <!-- Change Type Filter -->
            @if(count($changeTypes) > 0)
                <flux:select wire:model.live="changeType" class="w-full lg:w-48" placeholder="All changes">
                    <flux:select.option value="">All Changes</flux:select.option>
                    @foreach($changeTypes as $type)
                        <flux:select.option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
        </div>
    </flux:card>

    <!-- Select All (for pending items) -->
    @if($filter === 'pending' && $updates->count() > 0)
        <div class="flex items-center gap-3 px-4 py-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:checkbox wire:model.live="selectAll" />
            <flux:text size="sm">Select all {{ $updates->total() }} items</flux:text>
        </div>
    @endif

    <!-- Table -->
    @if($updates->count() > 0)
        <flux:table :paginate="$updates">
            <flux:table.columns>
                @if($filter === 'pending')
                    <flux:table.column class="w-12"></flux:table.column>
                @endif
                <flux:table.column sortable :sorted="$sortField === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">Product</flux:table.column>
                <flux:table.column>Changes</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($updates as $update)
                    <flux:table.row :key="$update->id">
                        @if($filter === 'pending')
                            <flux:table.cell>
                                <flux:checkbox wire:model.live="selectedUpdates" value="{{ $update->id }}" />
                            </flux:table.cell>
                        @endif

                        <flux:table.cell>
                            <div class="space-y-1">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $update->product->name }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" color="zinc">SKU: {{ $update->product->sku }}</flux:badge>
                                    <flux:text size="xs">{{ $update->created_at->diffForHumans() }}</flux:text>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if(count($update->changes_detected) > 0)
                                <div class="space-y-1">
                                    @foreach($update->changes_detected as $field => $change)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="font-medium text-zinc-600 dark:text-zinc-400 min-w-20">
                                                {{ ucfirst(str_replace('_', ' ', $field)) }}:
                                            </span>
                                            <code class="px-1.5 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded text-xs line-through">
                                                {{ Str::limit($change['local'] ?? 'empty', 20) }}
                                            </code>
                                            <flux:icon.arrow-right class="size-3 text-zinc-400" />
                                            <code class="px-1.5 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded text-xs">
                                                {{ Str::limit($change['linnworks'] ?? 'empty', 20) }}
                                            </code>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <flux:text size="sm" class="text-zinc-500">No changes</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($update->status === 'pending')
                                <flux:badge color="amber">Pending</flux:badge>
                            @elseif($update->status === 'approved')
                                <div class="space-y-1">
                                    <flux:badge color="green">Approved</flux:badge>
                                    <flux:text size="xs">by {{ $update->reviewer->name ?? 'Unknown' }}</flux:text>
                                </div>
                            @elseif($update->status === 'auto_accepted')
                                <flux:badge color="sky">Auto-Accepted</flux:badge>
                            @else
                                <div class="space-y-1">
                                    <flux:badge color="red">Rejected</flux:badge>
                                    <flux:text size="xs">by {{ $update->reviewer->name ?? 'Unknown' }}</flux:text>
                                </div>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            @if($update->status === 'pending')
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button
                                        wire:click="approveUpdate({{ $update->id }})"
                                        variant="primary"
                                        size="sm"
                                        icon="check"
                                    >
                                        Approve
                                    </flux:button>
                                    <flux:button
                                        wire:click="rejectUpdate({{ $update->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="x-mark"
                                    />
                                </div>
                            @else
                                <flux:text size="xs" class="text-zinc-500">
                                    {{ $update->reviewed_at?->diffForHumans() ?? $update->accepted_at?->diffForHumans() }}
                                </flux:text>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <flux:card>
            <div class="py-12 text-center">
                <flux:icon.check-circle class="size-16 mx-auto text-zinc-300 dark:text-zinc-600 mb-4" />
                <flux:heading size="lg" class="text-zinc-500 dark:text-zinc-400">No updates found</flux:heading>
                <flux:text class="mt-2">
                    @if($search)
                        No results for "{{ $search }}". Try a different search term.
                    @elseif($filter === 'pending')
                        All products are up to date with Linnworks.
                    @else
                        No {{ str_replace('_', ' ', $filter) }} updates to display.
                    @endif
                </flux:text>
            </div>
        </flux:card>
    @endif
</div>
