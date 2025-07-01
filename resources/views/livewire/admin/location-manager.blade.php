<div class="py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700 mb-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Location Management
                        </h1>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            Manage warehouse locations and their settings
                        </p>
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
        </div>

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

        <!-- Filters -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-8">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search locations..."
                            icon="magnifying-glass"
                        />
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <flux:checkbox
                                wire:model.live="showInactiveLocations"
                            />
                            Show inactive
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Locations Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Locations</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Location
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Usage Stats
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Last Used
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse($locations as $location)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $location->code }}
                                        </div>
                                        @if($location->name && $location->name !== $location->code)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $location->name }}
                                            </div>
                                        @endif
                                        @if($location->qr_code)
                                            <div class="flex items-center gap-1 mt-1">
                                                <flux:icon.qr-code class="size-3 text-gray-400" />
                                                <span class="text-xs text-gray-400 font-mono">{{ $location->qr_code }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center gap-1">
                                            <flux:icon.arrow-trending-up class="size-3" />
                                            {{ $location->use_count }} uses
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $location->is_active 
                                           ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                           : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                        {{ $location->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($location->last_used_at)
                                        {{ $location->last_used_at->diffForHumans() }}
                                    @else
                                        Never
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button
                                            wire:click="editLocation({{ $location->id }})"
                                            variant="ghost"
                                            size="xs"
                                            icon="pencil"
                                        >
                                            Edit
                                        </flux:button>
                                        
                                        <flux:button
                                            wire:click="toggleLocationStatus({{ $location->id }})"
                                            variant="ghost"
                                            size="xs"
                                            :icon="$location->is_active ? 'eye-slash' : 'eye'"
                                        >
                                            {{ $location->is_active ? 'Deactivate' : 'Activate' }}
                                        </flux:button>
                                        
                                        <flux:button
                                            wire:click="deleteLocation({{ $location->id }})"
                                            wire:confirm="Are you sure you want to delete this location? This action cannot be undone."
                                            variant="ghost"
                                            size="xs"
                                            icon="trash"
                                            class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            Delete
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <flux:icon.map-pin class="size-12 text-gray-400 mx-auto mb-4" />
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No locations found</p>
                                    @if($search)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            Try adjusting your search or filters
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            Sync from Linnworks to get started
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($locations->hasPages())
                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $locations->links() }}
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
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
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