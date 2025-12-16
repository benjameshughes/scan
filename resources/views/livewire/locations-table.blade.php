<div class="space-y-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" dismissible>
            {{ session('message') }}
        </flux:callout>
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="x-circle" dismissible>
            {{ session('error') }}
        </flux:callout>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <flux:icon.map-pin class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Locations</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Active</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->stats['active'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 dark:bg-amber-900 rounded-lg">
                    <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Used (30 days)</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->stats['recently_used'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Header with Search and Actions --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1 max-w-sm">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search locations..."
                icon="magnifying-glass"
                clearable
            />
        </div>

        <div class="flex items-center gap-3">
            <flux:switch wire:model.live="showInactive" label="Show Inactive" />

            <flux:button
                wire:click="syncFromLinnworks"
                wire:loading.attr="disabled"
                wire:target="syncFromLinnworks"
                icon="arrow-path"
                variant="primary"
            >
                <span wire:loading.remove wire:target="syncFromLinnworks">Sync from Linnworks</span>
                <span wire:loading wire:target="syncFromLinnworks">Syncing...</span>
            </flux:button>
        </div>
    </div>

    {{-- Table --}}
    <flux:table :paginate="$locations">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortField === 'code'" :direction="$sortDirection" wire:click="sort('code')">
                Location
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'use_count'" :direction="$sortDirection" wire:click="sort('use_count')">
                Usage
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'is_active'" :direction="$sortDirection" wire:click="sort('is_active')">
                Status
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'last_used_at'" :direction="$sortDirection" wire:click="sort('last_used_at')">
                Last Used
            </flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($locations as $location)
                <flux:table.row :key="$location->id">
                    <flux:table.cell>
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $location->code }}</div>
                            @if ($location->name && $location->name !== $location->code)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $location->name }}</div>
                            @endif
                            @if ($location->qr_code)
                                <div class="flex items-center gap-1 mt-1">
                                    <flux:icon.qr-code class="size-3 text-zinc-400 dark:text-zinc-500" />
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500 font-mono">{{ $location->qr_code }}</span>
                                </div>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:icon.arrow-trending-up class="size-4" />
                            <span>{{ $location->use_count }} uses</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$location->is_active ? 'green' : 'zinc'">
                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                        {{ $location->last_used_at ? $location->last_used_at->diffForHumans() : 'Never' }}
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $location->id }})" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                :icon="$location->is_active ? 'eye-slash' : 'eye'"
                                wire:click="toggle({{ $location->id }})"
                            />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="delete({{ $location->id }})"
                                wire:confirm="Are you sure you want to delete this location? This action cannot be undone."
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-8">
                        <div class="flex flex-col items-center gap-2 text-zinc-500">
                            <flux:icon.map-pin class="size-8" />
                            <span>No locations found</span>
                            <flux:button wire:click="syncFromLinnworks" size="sm" variant="ghost">
                                Sync from Linnworks
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Edit Location Modal --}}
    <flux:modal name="edit-location" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Edit Location</flux:heading>

            <form wire:submit="saveLocation" class="space-y-4">
                <flux:input
                    wire:model="editCode"
                    label="Location Code"
                    placeholder="e.g., A1-01"
                    required
                />

                <flux:input
                    wire:model="editName"
                    label="Display Name"
                    placeholder="Optional friendly name"
                />

                <flux:input
                    wire:model="editQrCode"
                    label="QR Code"
                    placeholder="QR code identifier"
                />

                <flux:switch wire:model="editIsActive" label="Active" />

                <div class="flex justify-end gap-2 pt-4">
                    <flux:button type="button" wire:click="cancelEdit" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Save Changes
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
