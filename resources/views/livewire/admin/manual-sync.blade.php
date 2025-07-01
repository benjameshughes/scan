<div class="space-y-6" 
     x-data="{ 
         polling: false,
         localRunning: false,
         startPolling() {
             this.polling = true;
             this.localRunning = true;
             this.poll();
         },
         poll() {
             if (this.polling) {
                 console.log('Polling for progress... localRunning:', this.localRunning, 'wireRunning:', $wire.isRunning);
                 $wire.getProgress().then((result) => {
                     console.log('Progress update received:', result);
                     console.log('Current wire isRunning state:', $wire.isRunning);
                     // Continue polling if sync is still running (check both local and wire state)
                     if (this.localRunning && ($wire.isRunning || result?.is_running)) {
                         console.log('Sync still running, continuing to poll...');
                         setTimeout(() => this.poll(), 1000); // Poll every 1 second for faster updates
                     } else {
                         console.log('Sync completed, stopping polling');
                         this.polling = false;
                         this.localRunning = false;
                     }
                 }).catch((error) => {
                     console.error('Error polling progress:', error);
                     this.polling = false;
                     this.localRunning = false;
                 });
             }
         }
     }"
     x-init="
         $wire.isRunning && startPolling();
         $wire.on('sync-started', () => {
             console.log('Sync started event received, isRunning should now be true');
             // Give Livewire a moment to update the DOM
             setTimeout(() => {
                 console.log('Starting polling, isRunning is now:', $wire.isRunning);
                 startPolling();
             }, 100);
         });
     ">
    <!-- Session Flash Messages -->
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-md p-4 border border-green-200 dark:border-green-800">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-md p-4 border border-red-200 dark:border-red-800">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Manual Sync Card -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Manual Linnworks Sync</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manually trigger a full sync with Linnworks products</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(!$isRunning)
                        <flux:button 
                            wire:click="loadEstimatedInfo" 
                            variant="ghost" 
                            size="sm" 
                            icon="arrow-path">Refresh Info</flux:button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card Content -->
        <div class="p-6 space-y-6">
            
            <!-- Sync Information -->
            @if($estimatedInfo)
                <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Sync Information</h4>
                    
                    @if(isset($estimatedInfo['error']))
                        <div class="text-sm text-red-600 dark:text-red-400">
                            {{ $estimatedInfo['error'] }}
                        </div>
                    @else
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-300">Total Products</dt>
                                <dd class="text-gray-600 dark:text-gray-400">{{ $estimatedInfo['estimated_total'] }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-300">Estimated Batches</dt>
                                <dd class="text-gray-600 dark:text-gray-400">{{ $estimatedInfo['estimated_batches'] }} batches</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-300">Batch Size</dt>
                                <dd class="text-gray-600 dark:text-gray-400">{{ $estimatedInfo['batch_size'] }} products per batch</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-300">Last Daily Sync</dt>
                                <dd class="text-gray-600 dark:text-gray-400">{{ $estimatedInfo['last_sync']['last_run'] }}</dd>
                            </div>
                        </dl>
                    @endif
                </div>
            @endif

            <!-- Sync Options -->
            <div class="space-y-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Sync Options</h4>
                
                <div class="flex items-center">
                    <input 
                        wire:model.live="dryRun" 
                        type="checkbox" 
                        id="dryRun" 
                        class="h-4 w-4 text-blue-600 bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-600 rounded focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-1"
                    >
                    <label for="dryRun" class="ml-2 text-sm text-gray-900 dark:text-gray-100">
                        Dry Run (preview changes without applying them)
                    </label>
                </div>
                
                @if($dryRun)
                    <div class="bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 rounded-md p-3 border border-amber-200 dark:border-amber-800 text-sm">
                        <strong>Dry Run Mode:</strong> This will show you what changes would be made without actually applying them to your local products.
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <!-- Skeleton Loader for Results -->
            @if($isRunning && $currentProgress)
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-3">Sync Progress</h4>
                    
                    <div class="space-y-3">
                        <!-- Current Operation -->
                        <div>
                            <div class="text-sm text-blue-800 dark:text-blue-300">
                                {{ $currentProgress['operation'] ?? 'Processing...' }}
                            </div>
                        </div>
                        
                        <!-- Progress Bar (if percentage available) -->
                        @if(isset($currentProgress['progress_percentage']) && $currentProgress['progress_percentage'] !== null)
                            <div>
                                <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300 mb-1">
                                    <span>Progress</span>
                                    <span>{{ $currentProgress['progress_percentage'] }}%</span>
                                </div>
                                <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                    <div class="bg-blue-600 dark:bg-blue-400 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $currentProgress['progress_percentage'] }}%"></div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Current Stats -->
                        @if(isset($currentProgress['stats']))
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <div class="text-blue-600 dark:text-blue-400 font-medium">Processed</div>
                                    <div class="text-blue-800 dark:text-blue-200">{{ $currentProgress['stats']['total_processed'] ?? 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-green-600 dark:text-green-400 font-medium">Created</div>
                                    <div class="text-blue-800 dark:text-blue-200">{{ $currentProgress['stats']['created'] ?? 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-amber-600 dark:text-amber-400 font-medium">Queued</div>
                                    <div class="text-blue-800 dark:text-blue-200">{{ $currentProgress['stats']['queued'] ?? 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-red-600 dark:text-red-400 font-medium">Errors</div>
                                    <div class="text-blue-800 dark:text-blue-200">{{ $currentProgress['stats']['errors'] ?? 0 }}</div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Batch Info -->
                        @if(isset($currentProgress['batch_info']['current_batch']))
                            <div class="text-xs text-blue-700 dark:text-blue-300">
                                Processing batch {{ $currentProgress['batch_info']['current_batch'] }}
                                @if(isset($currentProgress['batch_info']['products_in_batch']))
                                    ({{ $currentProgress['batch_info']['products_in_batch'] }} products)
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <span x-show="localRunning || $wire.isRunning">Sync is running... This may take several minutes.</span>
                    <span x-show="!(localRunning || $wire.isRunning)">Click "Start Sync" to begin the synchronization process.</span>
                </div>
                
                <div class="flex items-center space-x-3">
                    @if($showResults)
                        <flux:button 
                            wire:click="clearResults" 
                            variant="ghost" 
                            size="sm">Clear Results</flux:button>
                    @endif
                    
                    <flux:button 
                        wire:click="executeSync" 
                        variant="filled" 
                        x-bind:loading="localRunning || $wire.isRunning"
                        x-bind:disabled="localRunning || $wire.isRunning"
                        icon="play"
                        x-on:click="
                            console.log('Sync button clicked, starting polling immediately');
                            startPolling();
                        ">
                        {{ $dryRun ? 'Start Dry Run' : 'Start Sync' }}
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <!-- Skeleton Loader for Sync Results -->
    <div x-show="localRunning && !$wire.showResults" class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Card Header Skeleton -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="animate-pulse">
                <div class="h-6 bg-zinc-200 dark:bg-zinc-700 rounded w-1/3"></div>
            </div>
        </div>

        <!-- Card Content Skeleton -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Summary Stats Skeleton -->
                <div class="space-y-3">
                    <div class="animate-pulse">
                        <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-1/2 mb-3"></div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-2/3"></div>
                                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-1/4"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-1/2"></div>
                                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-1/3"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-3/4"></div>
                                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-1/5"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Stats Skeleton -->
                <div class="space-y-3">
                    <div class="animate-pulse">
                        <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-1/3 mb-3"></div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-3/4"></div>
                                <div class="h-3 bg-green-200 dark:bg-green-800 rounded w-1/4"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-2/3"></div>
                                <div class="h-3 bg-amber-200 dark:bg-amber-800 rounded w-1/4"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-1/2"></div>
                                <div class="h-3 bg-red-200 dark:bg-red-800 rounded w-1/5"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Indicators Skeleton -->
                <div class="space-y-3">
                    <div class="animate-pulse">
                        <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-1/4 mb-3"></div>
                        <div class="space-y-2">
                            <div class="h-6 bg-blue-200 dark:bg-blue-800 rounded-full w-3/4"></div>
                            <div class="h-6 bg-zinc-200 dark:bg-zinc-700 rounded-full w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Results -->
    @if($showResults && $syncStats)
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
            <!-- Card Header -->
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ $syncStats['dry_run'] ? 'Dry Run' : 'Sync' }} Results
                </h3>
            </div>

            <!-- Card Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Summary Stats -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Summary</h4>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Total Processed:</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($syncStats['total_processed']) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Execution Time:</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $syncStats['execution_time'] }}s</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Batches Processed:</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $syncStats['batches_processed'] }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Action Stats -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Actions</h4>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Products Created:</dt>
                                <dd class="font-medium text-green-600 dark:text-green-400">{{ number_format($syncStats['created']) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Updates Queued:</dt>
                                <dd class="font-medium text-amber-600 dark:text-amber-400">{{ number_format($syncStats['queued']) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Errors:</dt>
                                <dd class="font-medium text-red-600 dark:text-red-400">{{ number_format($syncStats['errors']) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Status Indicators -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Status</h4>
                        <div class="space-y-2">
                            @if($syncStats['dry_run'])
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Dry Run - No Changes Applied
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Sync Completed
                                </span>
                            @endif
                            
                            @if($syncStats['errors'] > 0)
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    {{ $syncStats['errors'] }} Error(s)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                @if(!$syncStats['dry_run'] && $syncStats['queued'] > 0)
                    <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <div class="bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 rounded-md p-4 border border-amber-200 dark:border-amber-800">
                            <div class="flex">
                                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium">Review Required</h4>
                                    <p class="mt-1 text-sm">
                                        {{ number_format($syncStats['queued']) }} product update(s) have been queued for review. 
                                        <a href="{{ route('admin.pending-updates') }}" class="underline font-medium">Review pending updates</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>