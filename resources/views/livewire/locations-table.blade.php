<div class="py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        
        <!-- Messages -->
        @if($successMessage)
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                        <span class="text-sm text-green-700 dark:text-green-300">{{ $successMessage }}</span>
                    </div>
                    <flux:button
                        wire:click="clearMessages"
                        variant="ghost"
                        size="xs"
                        square
                        icon="x-mark"
                    />
                </div>
            </div>
        @endif

        @if($errorMessage)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                        <span class="text-sm text-red-700 dark:text-red-300">{{ $errorMessage }}</span>
                    </div>
                    <flux:button
                        wire:click="clearMessages"
                        variant="ghost"
                        size="xs"
                        square
                        icon="x-mark"
                    />
                </div>
            </div>
        @endif

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Locations</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($stats['total']) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <flux:icon.map-pin class="size-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Active Locations</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                            {{ number_format($stats['active']) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <flux:icon.check-circle class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Recently Used</p>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                            {{ number_format($stats['recently_used']) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <flux:icon.clock class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Table Component -->
        <div class="table-container bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
            {{-- Header with search, filters, and actions --}}
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $table->getTitle() }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $table->getDescription() }}</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <flux:button
                            wire:click="syncFromLinnworks"
                            variant="filled"
                            icon="arrow-path"
                            :disabled="$isProcessingSync"
                            size="sm"
                        >
                            @if($isProcessingSync)
                                Syncing...
                            @else
                                Sync from Linnworks
                            @endif
                        </flux:button>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-700">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search locations..."
                            icon="magnifying-glass"
                        />
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                            <flux:checkbox
                                wire:model.live="showInactiveLocations"
                            />
                            Show inactive
                        </label>
                    </div>
                </div>
            </div>
            
            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            @foreach($table->getColumns() as $column)
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">
                                    @if($column->isSortable())
                                        <button wire:click="sortBy('{{ $column->getName() }}')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-100">
                                            {{ $column->getLabel() }}
                                            @if($sortField === $column->getName())
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    @if($sortDirection === 'asc')
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                    @else
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    @endif
                                                </svg>
                                            @endif
                                        </button>
                                    @else
                                        {{ $column->getLabel() }}
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse($data as $row)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                @foreach($table->getColumns() as $column)
                                    <td class="px-6 py-4">
                                        {!! $column->getValue($row) !!}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($table->getColumns()) }}" class="px-6 py-12 text-center">
                                    <flux:icon.map-pin class="size-12 text-zinc-400 dark:text-zinc-500 mx-auto mb-4" />
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No locations found</p>
                                    @if($search)
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                                            Try adjusting your search or filters
                                        </p>
                                    @else
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                                            Sync from Linnworks to get started
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($data->hasPages())
                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $data->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-full p-4">
                <div class="fixed inset-0 bg-black bg-opacity-25" wire:click="cancelEdit"></div>
                
                <div class="relative bg-white dark:bg-zinc-800 rounded-lg shadow-lg max-w-md w-full">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Edit Location
                        </h3>
                    </div>
                    
                    <form wire:submit="saveLocation" class="p-6 space-y-4">
                        <div>
                            <flux:input
                                wire:model="editCode"
                                label="Location Code"
                                placeholder="e.g., 11A, 12B-3"
                                required
                            />
                            <flux:error name="editCode" />
                        </div>
                        
                        <div>
                            <flux:input
                                wire:model="editName"
                                label="Display Name"
                                placeholder="Optional display name"
                            />
                            <flux:error name="editName" />
                        </div>
                        
                        <div>
                            <flux:input
                                wire:model="editQrCode"
                                label="QR Code"
                                placeholder="Optional QR code"
                            />
                            <flux:error name="editQrCode" />
                        </div>
                        
                        <div>
                            <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                                <flux:checkbox
                                    wire:model="editIsActive"
                                />
                                Active location
                            </label>
                        </div>
                        
                        <div class="flex gap-3 pt-4">
                            <flux:button
                                type="button"
                                wire:click="cancelEdit"
                                variant="ghost"
                                class="flex-1"
                            >
                                Cancel
                            </flux:button>
                            <flux:button
                                type="submit"
                                variant="filled"
                                class="flex-1"
                            >
                                Save Changes
                            </flux:button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>