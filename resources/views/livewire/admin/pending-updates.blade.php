<div class="space-y-6">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif

    <!-- Header Card -->
    <flux:card>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Pending Product Updates</flux:heading>
                <flux:text class="mt-1">
                    Review changes detected during Linnworks sync
                    @if($pendingCount > 0)
                        <flux:badge color="amber" class="ml-2">{{ $pendingCount }} pending</flux:badge>
                    @endif
                </flux:text>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Filter -->
                <flux:select wire:model.live="filter" size="sm" class="w-48">
                    <option value="pending">Pending ({{ $pendingCount }})</option>
                    <option value="auto_accepted">Auto-Accepted ({{ $autoAcceptedCount }})</option>
                    <option value="approved">Approved ({{ $approvedCount }})</option>
                    <option value="rejected">Rejected ({{ $rejectedCount }})</option>
                </flux:select>

                <!-- Bulk Actions -->
                @if(count($selectedUpdates) > 0 && $filter === 'pending')
                    <flux:button wire:click="bulkApprove" variant="primary" size="sm" icon="check">
                        Approve ({{ count($selectedUpdates) }})
                    </flux:button>
                    <flux:button wire:click="bulkReject" variant="ghost" size="sm" icon="x-mark">
                        Reject
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:card>

    <!-- Select All (for pending items) -->
    @if($filter === 'pending' && $updates->count() > 0)
        <div class="bg-zinc-50 dark:bg-zinc-900 px-6 py-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:checkbox wire:model.live="selectAll" label="Select all {{ $updates->count() }} items on this page" />
        </div>
    @endif

    <!-- Updates List -->
    @forelse($updates as $update)
        <flux:card>
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4 flex-1">
                    @if($update->status === 'pending')
                        <div class="pt-1">
                            <flux:checkbox wire:model.live="selectedUpdates" value="{{ $update->id }}" />
                        </div>
                    @endif

                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <flux:heading size="md">{{ $update->product->name }}</flux:heading>
                            <flux:badge color="zinc" size="sm">SKU: {{ $update->product->sku }}</flux:badge>
                        </div>

                        <!-- Changes Preview -->
                        @if(count($update->changes_detected) > 0)
                            <div class="mt-4 space-y-2">
                                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Detected Changes:</p>
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 space-y-2 border border-zinc-200 dark:border-zinc-700">
                                    @foreach($update->changes_detected as $field => $change)
                                        <div class="flex items-center text-sm space-x-3">
                                            <span class="font-semibold text-zinc-700 dark:text-zinc-300 min-w-[120px]">
                                                {{ str_replace('_', ' ', ucfirst($field)) }}:
                                            </span>
                                            <div class="flex items-center space-x-2 flex-1">
                                                <code class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded text-xs line-through">
                                                    {{ $change['local'] ?? 'empty' }}
                                                </code>
                                                <flux:icon.arrow-right class="size-4 text-zinc-400" />
                                                <code class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded text-xs">
                                                    {{ $change['linnworks'] ?? 'empty' }}
                                                </code>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">No changes detected</p>
                        @endif

                        <div class="mt-3 flex items-center space-x-4 text-xs text-zinc-500 dark:text-zinc-400">
                            <span>Detected {{ $update->created_at->diffForHumans() }}</span>
                            @if($update->isReviewed())
                                <span>-</span>
                                <span>{{ ucfirst($update->status) }} by {{ $update->reviewer->name ?? 'Unknown' }}</span>
                                <span>-</span>
                                <span>{{ $update->reviewed_at->diffForHumans() }}</span>
                            @elseif($update->isAutoAccepted())
                                <span>-</span>
                                <span>Auto-accepted by system</span>
                                <span>-</span>
                                <span>{{ $update->accepted_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-3 ml-4">
                    @if($update->status === 'pending')
                        <flux:button wire:click="approveUpdate({{ $update->id }})" variant="primary" size="sm" icon="check">
                            Approve
                        </flux:button>
                        <flux:button wire:click="rejectUpdate({{ $update->id }})" variant="ghost" size="sm" icon="x-mark">
                            Reject
                        </flux:button>
                    @else
                        @if($update->status === 'approved')
                            <flux:badge color="green" size="lg">Approved</flux:badge>
                        @elseif($update->status === 'auto_accepted')
                            <flux:badge color="blue" size="lg">Auto-Accepted</flux:badge>
                        @else
                            <flux:badge color="red" size="lg">Rejected</flux:badge>
                        @endif
                    @endif
                </div>
            </div>
        </flux:card>
    @empty
        <flux:card>
            <div class="py-12 text-center">
                <flux:icon.check-circle class="size-16 mx-auto text-zinc-300 dark:text-zinc-600 mb-4" />
                <flux:heading size="lg" class="text-zinc-500 dark:text-zinc-400">No updates found</flux:heading>
                <flux:text class="mt-2">
                    @if($filter === 'pending')
                        All products are up to date with Linnworks.
                    @else
                        No {{ $filter }} updates to display.
                    @endif
                </flux:text>
            </div>
        </flux:card>
    @endforelse

    <!-- Pagination -->
    @if($updates->hasPages())
        <div class="flex justify-center">
            {{ $updates->links() }}
        </div>
    @endif
</div>
