<div class="w-full">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Sync Queue Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitor and manage background sync operations</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        wire:click="refreshQueue"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2 px-3 py-2 text-sm bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md transition-colors"
                    >
                        <flux:icon.arrow-path class="size-4 {{ $refreshing ? 'animate-spin' : '' }}" />
                        <span wire:loading.remove wire:target="refreshQueue">Refresh</span>
                        <span wire:loading wire:target="refreshQueue">Refreshing...</span>
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

            <!-- Queue Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Pending Jobs</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($queueStats['pending_count']) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                            <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Failed Jobs</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($queueStats['failed_count']) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <flux:icon.x-mark class="size-5 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Old Jobs (1h+)</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($queueStats['old_jobs_count']) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                            <flux:icon.exclamation-triangle class="size-5 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Queue Health</p>
                            <p class="text-lg font-semibold {{ $queueStats['queue_healthy'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $queueStats['queue_healthy'] ? 'Healthy' : 'Issues' }}
                            </p>
                        </div>
                        <div class="w-10 h-10 {{ $queueStats['queue_healthy'] ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-lg flex items-center justify-center">
                            @if($queueStats['queue_healthy'])
                                <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                            @else
                                <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Pending Jobs -->
                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pending Jobs</h2>
                        @if($queueStats['pending_count'] > 0)
                            <span class="text-sm text-gray-500 dark:text-gray-400">Showing first 50</span>
                        @endif
                    </div>

                    @if($pendingJobs->isEmpty())
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <flux:icon.check-circle class="size-12 mx-auto mb-2 text-green-400" />
                            <p>No pending jobs</p>
                            <p class="text-sm mt-1">Queue is empty</p>
                        </div>
                    @else
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($pendingJobs as $job)
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ $job['job_class'] }}
                                            </p>
                                            <div class="flex items-center gap-4 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <span>Queue: {{ $job['queue'] }}</span>
                                                <span>Attempts: {{ $job['attempts'] }}</span>
                                                <span>Age: {{ $job['age_minutes'] }}min</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($job['is_delayed'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Delayed
                                                </span>
                                            @endif
                                            @if($job['age_minutes'] > 60)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                                    Old
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Failed Jobs -->
                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Failed Jobs</h2>
                        @if($queueStats['failed_count'] > 0)
                            <div class="flex items-center gap-2">
                                <button
                                    wire:click="retryAllFailed"
                                    wire:loading.attr="disabled"
                                    class="px-3 py-1 text-xs bg-amber-600 hover:bg-amber-700 text-white rounded transition-colors"
                                >
                                    <span wire:loading.remove wire:target="retryAllFailed">Retry All</span>
                                    <span wire:loading wire:target="retryAllFailed">Retrying...</span>
                                </button>
                                <button
                                    wire:click="flushFailedJobs"
                                    wire:loading.attr="disabled"
                                    wire:confirm="Are you sure you want to delete all failed jobs?"
                                    class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded transition-colors"
                                >
                                    <span wire:loading.remove wire:target="flushFailedJobs">Delete All</span>
                                    <span wire:loading wire:target="flushFailedJobs">Deleting...</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    @if($failedJobs->isEmpty())
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <flux:icon.check-circle class="size-12 mx-auto mb-2 text-green-400" />
                            <p>No failed jobs</p>
                            <p class="text-sm mt-1">Everything is working smoothly</p>
                        </div>
                    @else
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($failedJobs as $job)
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-red-200 dark:border-red-700">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ $job['job_class'] }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    {{ $job['error_type'] }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $job['failed_at']->diffForHumans() }}
                                                </span>
                                            </div>
                                            
                                            <p class="text-xs text-red-700 dark:text-red-400 mt-2 line-clamp-2">
                                                {{ $job['error_message'] }}
                                            </p>
                                            
                                            @if(isset($showFailedDetails[$job['id']]))
                                                <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
                                                    <pre class="text-xs text-red-700 dark:text-red-300 whitespace-pre-wrap max-h-40 overflow-y-auto">{{ $job['full_exception'] }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-red-200 dark:border-red-700">
                                        <button
                                            wire:click="toggleFailedJobDetails({{ $job['id'] }})"
                                            class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 transition-colors"
                                        >
                                            {{ isset($showFailedDetails[$job['id']]) ? 'Hide Details' : 'Show Details' }}
                                        </button>
                                        
                                        <div class="flex items-center gap-2">
                                            <button
                                                wire:click="retryJob({{ $job['id'] }})"
                                                class="px-2 py-1 text-xs bg-amber-600 hover:bg-amber-700 text-white rounded transition-colors"
                                            >
                                                Retry
                                            </button>
                                            <button
                                                wire:click="deleteFailedJob({{ $job['id'] }})"
                                                wire:confirm="Are you sure you want to delete this failed job?"
                                                class="px-2 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded transition-colors"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>