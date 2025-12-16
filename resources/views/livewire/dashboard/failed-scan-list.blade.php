<div class="space-y-4">
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" dismissible>
            {{ session('message') }}
        </flux:callout>
    @endif

    <flux:table :paginate="$scans">
        <flux:table.columns>
            <flux:table.column>Scan</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($scans as $scan)
                <flux:table.row :key="$scan->id">
                    <flux:table.cell>
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $scan->product?->sku ?? 'No SKU' }}
                            </div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400 font-mono">
                                {{ $scan->barcode }}
                            </div>
                            <div class="text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $scan->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge
                            size="sm"
                            :color="match($scan->sync_status) {
                                'synced' => 'green',
                                'failed' => 'red',
                                default => 'amber'
                            }"
                            :icon="match($scan->sync_status) {
                                'synced' => 'check-circle',
                                'failed' => 'x-circle',
                                default => 'clock'
                            }"
                        >
                            {{ ucfirst($scan->sync_status ?? 'pending') }}
                        </flux:badge>
                        @if (!$scan->submitted_at)
                            <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                Not submitted
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" href="{{ route('scans.show', $scan) }}" />
                            @if ($scan->sync_status === 'failed')
                                <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="retrySync({{ $scan->id }})" />
                            @endif
                            @role('admin')
                                <flux:button size="sm" variant="ghost" icon="check" wire:click="markAsSubmitted({{ $scan->id }})" />
                            @endrole
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="text-center py-8">
                        <div class="flex flex-col items-center gap-2 text-zinc-500">
                            <flux:icon.check-circle class="size-8 text-green-500" />
                            <span>All scans submitted successfully!</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
