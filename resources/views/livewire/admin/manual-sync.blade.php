<div class="space-y-6">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="x-circle" heading="{{ session('error') }}" />
    @endif

    <!-- Manual Sync Card -->
    <flux:card class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Sync Products from Linnworks</flux:heading>
                <flux:text class="mt-1">Pull the latest product catalog from Linnworks to update your local database</flux:text>
            </div>
            @if(!$isRunning)
                <flux:button wire:click="loadEstimatedInfo" variant="ghost" size="sm" icon="arrow-path">
                    Refresh Info
                </flux:button>
            @endif
        </div>

        <!-- Sync Information -->
        @if($estimatedInfo)
            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-3">Sync Information</h4>

                @if(isset($estimatedInfo['error']))
                    <flux:callout variant="danger" icon="x-circle" heading="{{ $estimatedInfo['error'] }}" />
                @else
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-zinc-600 dark:text-zinc-400">Total Products</dt>
                            <dd class="text-zinc-900 dark:text-zinc-100 font-semibold mt-1">{{ $estimatedInfo['estimated_total'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-zinc-600 dark:text-zinc-400">Estimated Batches</dt>
                            <dd class="text-zinc-900 dark:text-zinc-100 font-semibold mt-1">{{ $estimatedInfo['estimated_batches'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-zinc-600 dark:text-zinc-400">Batch Size</dt>
                            <dd class="text-zinc-900 dark:text-zinc-100 font-semibold mt-1">{{ $estimatedInfo['batch_size'] }} products</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-zinc-600 dark:text-zinc-400">Last Daily Sync</dt>
                            <dd class="text-zinc-900 dark:text-zinc-100 font-semibold mt-1">{{ $estimatedInfo['last_sync']['last_run'] }}</dd>
                        </div>
                    </dl>
                @endif
            </div>
        @endif

        <!-- Sync Options -->
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Options</h4>

            <flux:checkbox wire:model.live="dryRun" label="Dry Run (preview changes without applying them)" />

            @if($dryRun)
                <flux:callout variant="warning" icon="exclamation-circle">
                    <flux:callout.heading>Dry Run Mode</flux:callout.heading>
                    <flux:callout.text>This will show you what changes would be made without actually applying them to your local products.</flux:callout.text>
                </flux:callout>
            @endif
        </div>

        <!-- Running Status -->
        @if($isRunning)
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                <div class="flex items-center space-x-3">
                    <flux:icon.arrow-path class="size-5 text-blue-600 dark:text-blue-400 animate-spin" />
                    <div>
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Sync in Progress</p>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">This may take several minutes. Please wait...</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:text size="sm">
                @if($isRunning)
                    Sync is running... This may take several minutes.
                @else
                    Click "Start Sync" to begin pulling products from Linnworks.
                @endif
            </flux:text>

            <div class="flex items-center space-x-3">
                @if($showResults)
                    <flux:button wire:click="clearResults" variant="ghost" size="sm">
                        Clear Results
                    </flux:button>
                @endif

                <flux:button
                    wire:click="executeSync"
                    variant="primary"
                    :disabled="$isRunning"
                    icon="play">
                    {{ $dryRun ? 'Start Dry Run' : 'Start Sync' }}
                </flux:button>
            </div>
        </div>
    </flux:card>

    <!-- Sync Results -->
    @if($showResults && $syncStats)
        <flux:card class="space-y-6">
            <flux:heading size="lg">{{ $syncStats['dry_run'] ? 'Dry Run' : 'Sync' }} Results</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Summary Stats -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Summary</h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Total Processed:</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($syncStats['total_processed']) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Execution Time:</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $syncStats['execution_time'] }}s</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Batches Processed:</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $syncStats['batches_processed'] }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Action Stats -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Actions</h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Products Created:</dt>
                            <dd class="font-semibold text-green-600 dark:text-green-400">{{ number_format($syncStats['created']) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Updates Queued:</dt>
                            <dd class="font-semibold text-amber-600 dark:text-amber-400">{{ number_format($syncStats['queued']) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Errors:</dt>
                            <dd class="font-semibold text-red-600 dark:text-red-400">{{ number_format($syncStats['errors']) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Status Indicators -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Status</h4>
                    <div class="space-y-2">
                        @if($syncStats['dry_run'])
                            <flux:badge color="blue" size="lg">Dry Run - No Changes Applied</flux:badge>
                        @else
                            <flux:badge color="green" size="lg">Sync Completed</flux:badge>
                        @endif

                        @if($syncStats['errors'] > 0)
                            <flux:badge color="red" size="lg">{{ $syncStats['errors'] }} Error(s)</flux:badge>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            @if(!$syncStats['dry_run'] && $syncStats['queued'] > 0)
                <div class="pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:callout variant="warning" icon="exclamation-triangle" inline>
                        <flux:callout.heading>Review Required</flux:callout.heading>
                        <flux:callout.text>
                            {{ number_format($syncStats['queued']) }} product update(s) have been queued for review.
                        </flux:callout.text>
                        <x-slot name="actions">
                            <flux:button href="{{ route('admin.pending-updates') }}" wire:navigate>Review pending updates</flux:button>
                        </x-slot>
                    </flux:callout>
                </div>
            @endif
        </flux:card>
    @endif
</div>
