<div class="w-full">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Sync Dashboard</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitor and manage Linnworks sync operations</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        wire:click="refreshDashboard"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2 px-3 py-2 text-sm bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
                    >
                        <flux:icon.arrow-path class="size-4 {{ $refreshing ? 'animate-spin' : '' }}" />
                        <span wire:loading.remove wire:target="refreshDashboard">Refresh</span>
                        <span wire:loading wire:target="refreshDashboard">Refreshing...</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Status Messages -->
            @if (session()->has('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                        <span class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-center gap-2">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                        <span class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center gap-2">
                        <flux:icon.information-circle class="size-5 text-blue-600 dark:text-blue-400" />
                        <span class="text-sm text-blue-800 dark:text-blue-200">{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Main Stats -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Sync Health Overview -->
                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Sync Health Overview</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Last Successful Sync -->
                            <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Last Successful Sync</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            @if($syncStats['last_successful_sync'])
                                                {{ $syncStats['last_successful_sync']->diffForHumans() }}
                                            @else
                                                Never
                                            @endif
                                        </p>
                                    </div>
                                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                        <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                                    </div>
                                </div>
                            </div>

                            <!-- Pending Scans -->
                            <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Pending Scans</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ number_format($syncStats['pending_scans']) }}
                                        </p>
                                    </div>
                                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                                        <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                </div>
                            </div>

                            <!-- Success Rate Grid -->
                            <div class="md:col-span-2">
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700 text-center">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">24h Success Rate</p>
                                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $syncStats['success_rate_24h'] }}%</p>
                                        <p class="text-xs text-gray-400">{{ $syncStats['total_scans_24h'] }} scans</p>
                                    </div>
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700 text-center">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">7d Success Rate</p>
                                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $syncStats['success_rate_7d'] }}%</p>
                                        <p class="text-xs text-gray-400">{{ $syncStats['total_scans_7d'] }} scans</p>
                                    </div>
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700 text-center">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">30d Success Rate</p>
                                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $syncStats['success_rate_30d'] }}%</p>
                                        <p class="text-xs text-gray-400">{{ $syncStats['total_scans_30d'] }} scans</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Sync Activity -->
                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Sync Activity</h2>
                        
                        <div class="space-y-3">
                            @forelse($recentActivity as $activity)
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                                {{ $activity['status'] === 'completed' ? 'bg-green-100 dark:bg-green-900' : 
                                                   ($activity['status'] === 'failed' ? 'bg-red-100 dark:bg-red-900' : 'bg-amber-100 dark:bg-amber-900') }}">
                                                @if($activity['status'] === 'completed')
                                                    <flux:icon.check class="size-4 text-green-600 dark:text-green-400" />
                                                @elseif($activity['status'] === 'failed')
                                                    <flux:icon.x-mark class="size-4 text-red-600 dark:text-red-400" />
                                                @else
                                                    <flux:icon.clock class="size-4 text-amber-600 dark:text-amber-400" />
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ ucfirst(str_replace('_', ' ', $activity['type'])) }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    by {{ $activity['user_name'] }} • {{ $activity['created_at']->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                {{ $activity['status'] === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                   ($activity['status'] === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200') }}">
                                                {{ ucfirst($activity['status']) }}
                                            </span>
                                            @if($activity['duration'])
                                                <p class="text-xs text-gray-400 mt-1">{{ $activity['duration'] }}s</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if($activity['error_message'])
                                        <div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded text-xs text-red-700 dark:text-red-300">
                                            {{ $activity['error_message'] }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <flux:icon.clock class="size-12 mx-auto mb-2 text-gray-300" />
                                    <p>No recent sync activity</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Right Column - Controls & Status -->
                <div class="space-y-6">
                    
                    <!-- Quick Actions -->
                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h2>
                        
                        <div class="space-y-3">
                            <button
                                wire:click="syncAllPending"
                                wire:loading.attr="disabled"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded-lg transition-colors disabled:opacity-50"
                            >
                                <flux:icon.arrow-path class="size-4 {{ $bulkSyncing ? 'animate-spin' : '' }}" />
                                <span wire:loading.remove wire:target="syncAllPending">Sync All Pending</span>
                                <span wire:loading wire:target="syncAllPending">Syncing...</span>
                            </button>

                            <button
                                wire:click="smartRetryFailed"
                                wire:loading.attr="disabled"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded-lg transition-colors disabled:opacity-50"
                            >
                                <flux:icon.sparkles class="size-4 {{ $smartRetrying ? 'animate-spin' : '' }}" />
                                <span wire:loading.remove wire:target="smartRetryFailed">Smart Retry</span>
                                <span wire:loading wire:target="smartRetryFailed">Processing...</span>
                            </button>

                            <button
                                wire:click="retryAllFailed"
                                wire:loading.attr="disabled"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-amber-600 hover:bg-amber-700 dark:bg-amber-700 dark:hover:bg-amber-600 text-white rounded-lg transition-colors disabled:opacity-50"
                            >
                                <flux:icon.arrow-path class="size-4 {{ $retryingFailed ? 'animate-spin' : '' }}" />
                                <span wire:loading.remove wire:target="retryAllFailed">Retry All Failed</span>
                                <span wire:loading wire:target="retryAllFailed">Retrying...</span>
                            </button>

                            <button
                                wire:click="runFullSync"
                                wire:loading.attr="disabled"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white rounded-lg transition-colors disabled:opacity-50"
                            >
                                <flux:icon.arrow-down-tray class="size-4" />
                                <span wire:loading.remove wire:target="runFullSync">Run Full Sync</span>
                                <span wire:loading wire:target="runFullSync">Starting...</span>
                            </button>

                            <button
                                wire:click="clearOldSyncHistory"
                                wire:loading.attr="disabled"
                                wire:confirm="Are you sure you want to clear sync history older than 30 days?"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-zinc-600 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-white rounded-lg transition-colors disabled:opacity-50"
                            >
                                <flux:icon.trash class="size-4" />
                                <span wire:loading.remove wire:target="clearOldSyncHistory">Clear Old History</span>
                                <span wire:loading wire:target="clearOldSyncHistory">Clearing...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Error Breakdown -->
                    @if(!empty($errorBreakdown))
                        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Error Breakdown (7 days)</h2>
                            
                            <div class="space-y-3">
                                @foreach($errorBreakdown as $errorType => $count)
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $errorType }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Queue Status -->
                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Queue Status</h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full {{ $queueStatus['pending_jobs'] > 0 ? 'bg-amber-500' : 'bg-green-500' }}"></div>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">Pending Jobs</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $queueStatus['pending_jobs'] }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full {{ $queueStatus['failed_jobs'] > 0 ? 'bg-red-500' : 'bg-green-500' }}"></div>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">Failed Jobs</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $queueStatus['failed_jobs'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Retry Recommendations -->
                    @if(!empty($retryRecommendations))
                        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Retry Recommendations</h2>
                            
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($retryRecommendations as $recommendation)
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        {{ ucfirst(str_replace('_', ' ', $recommendation['error_type'])) }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $recommendation['failed_count'] }} failures
                                                    </span>
                                                    @if($recommendation['retryable_count'] > 0)
                                                        <span class="text-xs text-amber-600 dark:text-amber-400">
                                                            {{ $recommendation['retryable_count'] }} retryable
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-gray-700 dark:text-gray-300">
                                                    {{ $recommendation['recommendation'] }}
                                                </p>
                                            </div>
                                            <div class="ml-3 flex-shrink-0">
                                                @if($recommendation['priority'] >= 80)
                                                    <flux:icon.exclamation-triangle class="size-4 text-red-500" />
                                                @elseif($recommendation['priority'] >= 50)
                                                    <flux:icon.exclamation-circle class="size-4 text-amber-500" />
                                                @else
                                                    <flux:icon.information-circle class="size-4 text-blue-500" />
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- API Health -->
                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">API Health</h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full {{ $apiHealth['status'] === 'healthy' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">Status</span>
                                </div>
                                <span class="text-sm font-medium capitalize {{ $apiHealth['status'] === 'healthy' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $apiHealth['status'] }}
                                </span>
                            </div>
                            
                            @if($apiHealth['status'] === 'healthy')
                                <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <span class="text-sm text-gray-900 dark:text-gray-100">Response Time</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $apiHealth['response_time'] }}ms</span>
                                </div>
                            @endif
                            
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <span class="text-sm text-gray-900 dark:text-gray-100">Last Checked</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $apiHealth['last_checked']->diffForHumans() }}</span>
                            </div>
                            
                            @if($apiHealth['status'] === 'unhealthy')
                                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                    <p class="text-sm text-red-700 dark:text-red-300">{{ $apiHealth['error'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>