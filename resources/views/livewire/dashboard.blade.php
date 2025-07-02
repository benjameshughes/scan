<div class="py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700 mb-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Dashboard
                        </h1>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            Overview of warehouse scanning activity and performance
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        @can('view users')
                            <flux:button
                                wire:click="redispatch"
                                variant="filled"
                                icon="arrow-path"
                                size="sm"
                            >
                                Re-sync Data
                            </flux:button>
                            
                            @if($retryCount > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ $retryCount }} items queued
                                </span>
                            @endif
                        @endcan

                    <!-- Notifications Button -->
                    <div x-data="{open: false}" class="relative">
                        <flux:button
                            x-on:click="open = !open"
                            variant="ghost"
                            size="sm"
                            square
                            class="relative"
                        >
                            @if($notifications->count() > 0)
                                <flux:icon.bell class="size-5 text-red-500 animate-pulse" />
                                <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full"></span>
                            @else
                                <flux:icon.bell class="size-5" />
                            @endif
                        </flux:button>

                        <!-- Notifications Slide-over -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 z-50" 
                             style="display: none;">
                            
                            <!-- Backdrop -->
                            <div class="fixed inset-0 bg-black bg-opacity-25" x-on:click="open = false"></div>
                            
                            <!-- Slide-over panel -->
                            <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white dark:bg-zinc-800 shadow-xl">
                                <div class="flex flex-col h-full">
                                    <!-- Header -->
                                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                                        <div class="flex items-center justify-between">
                                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                Notifications
                                            </h2>
                                            <div class="flex items-center gap-3">
                                                @if($notifications->count() > 0)
                                                    <flux:button
                                                        wire:click="readAll"
                                                        variant="ghost"
                                                        size="sm"
                                                    >
                                                        Mark all read
                                                    </flux:button>
                                                @endif
                                                <flux:button
                                                    x-on:click="open = false"
                                                    variant="ghost"
                                                    size="sm"
                                                    square
                                                    icon="x-mark"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Notifications list -->
                                    <div class="flex-1 overflow-y-auto">
                                        @forelse($notifications as $notification)
                                            <div wire:key="{{ $notification->id }}" class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                                                <div class="flex gap-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-2 h-2 bg-red-500 rounded-full mt-2"></div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm text-gray-900 dark:text-gray-100">
                                                            {{ $notification->data['message'] }}
                                                        </p>
                                                        <div class="mt-2 space-y-1">
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                Scan ID: {{ $notification->data['scan_id'] }}
                                                            </p>
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                Barcode: {{ $notification->data['barcode'] }}
                                                            </p>
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                {{ $notification->created_at->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                        <flux:button
                                                            wire:click="markAsRead('{{ $notification->id }}')"
                                                            variant="ghost"
                                                            size="xs"
                                                            class="mt-3"
                                                        >
                                                            Mark as read
                                                        </flux:button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="flex flex-col items-center justify-center py-12">
                                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5a7.5 7.5 0 00-7.5-7.5H2a1 1 0 000 2h5.5A5.5 5.5 0 0113 11.5V17z"/>
                                                </svg>
                                                <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">No notifications</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Scans -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Scans</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format($stats['total_scans']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <flux:icon.chart-bar class="size-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <!-- Pending Scans -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Pending Sync</p>
                            <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                                {{ number_format($stats['pending_scans']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                            <flux:icon.clock class="size-6 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                </div>

                <!-- Completed Today -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Today's Scans</p>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                                {{ number_format($stats['today_scans']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <flux:icon.check-circle class="size-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <!-- Failed Scans -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Failed Syncs</p>
                            <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                                {{ number_format($stats['failed_scans']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <flux:icon.exclamation-triangle class="size-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                </div>
            </div>

        <!-- Scanning Trends -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Scanning Activity Trends</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Daily scanning activity over the past week</p>
            </div>
            <div class="p-6">
                <div class="flex items-end justify-between h-32 gap-2">
                    @foreach($scanning_trends as $trend)
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-blue-100 dark:bg-blue-900 rounded-t-md" 
                                 style="height: {{ $trend['count'] > 0 ? max(8, ($trend['count'] / max(array_column($scanning_trends, 'count'))) * 100) : 4 }}%">
                                <div class="w-full bg-blue-600 dark:bg-blue-400 rounded-t-md h-full"></div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                                {{ $trend['date'] }}
                            </div>
                            <div class="text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ $trend['count'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Personal Performance Stats -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Your Performance</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Personal scanning statistics and achievements</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    <!-- Today's Scans -->
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <flux:icon.calendar class="size-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['user_today_scans']) }}</p>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Today</p>
                    </div>
                    
                    <!-- This Week -->
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <flux:icon.chart-bar class="size-6 text-green-600 dark:text-green-400" />
                        </div>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['user_weekly_scans']) }}</p>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">This Week</p>
                    </div>
                    
                    <!-- This Month -->
                    <div class="text-center">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <flux:icon.chart-pie class="size-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['user_monthly_scans']) }}</p>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">This Month</p>
                    </div>
                    
                    <!-- Your Rank -->
                    <div class="text-center">
                        <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <flux:icon.trophy class="size-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                            @if($stats['user_rank'])
                                #{{ $stats['user_rank'] }}
                            @else
                                --
                            @endif
                        </p>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Your Rank</p>
                    </div>
                    
                    <!-- Daily Average -->
                    <div class="text-center">
                        <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <flux:icon.arrow-trending-up class="size-6 text-cyan-600 dark:text-cyan-400" />
                        </div>
                        <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ number_format($stats['user_daily_average'], 1) }}</p>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Daily Average</p>
                    </div>
                    
                    <!-- Current Streak -->
                    <div class="text-center">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <flux:icon.fire class="size-6 text-red-600 dark:text-red-400" />
                        </div>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['user_streak'] }}</p>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Day Streak</p>
                    </div>
                </div>
                
                <!-- Performance Summary -->
                <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Contribution</p>
                            @php
                                $percentage = $stats['total_scans'] > 0 ? round(($stats['user_total_scans'] / $stats['total_scans']) * 100, 1) : 0;
                            @endphp
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $percentage }}% of all scans</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Ranking Position</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                @if($stats['user_rank'] && $stats['total_active_users'])
                                    {{ $stats['user_rank'] }} of {{ $stats['total_active_users'] }} users
                                @else
                                    Not ranked yet
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Lifetime Total</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['user_total_scans']) }} scans</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Performing Users -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Top Performers</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Most active users in the past 30 days</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($top_users as $index => $user)
                            <div class="flex items-center justify-between py-3 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $user->name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $user->scan_count }} scans
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <flux:icon.users class="size-12 text-gray-400 mx-auto mb-4" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">No user activity yet</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    Start scanning to see top performers
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Top Scanned Products -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Most Scanned Products</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Popular products from the past week</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($top_products as $index => $product)
                            <div class="flex items-center justify-between py-3 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $product->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $product->sku }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $product->scan_count }} scans
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <flux:icon.cube class="size-12 text-gray-400 mx-auto mb-4" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">No product scanning yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Attention Required Section -->
        @if($scans->count() > 0)
            <div class="mt-8 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Attention Required</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Scans that need manual review</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($scans as $scan)
                            <div class="flex items-center justify-between py-3 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <flux:icon.exclamation-triangle class="size-4 text-red-600 dark:text-red-400" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $scan->product->name ?? 'Unknown Product' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $scan->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <flux:button
                                    wire:click="markAsSubmitted({{ $scan->id }})"
                                    variant="ghost"
                                    size="xs"
                                >
                                    Mark Complete
                                </flux:button>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        {{ $scans->links('pagination.simple') }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="mt-8 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Quick Actions</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Common tasks and navigation shortcuts</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('scan.scan') }}" 
                       wire:navigate
                       class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors duration-200 group">
                        <flux:icon.qr-code class="size-8 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-200" />
                        <span class="mt-2 text-sm font-medium text-blue-700 dark:text-blue-300">Start Scanning</span>
                    </a>
                    
                    <a href="{{ route('products.index') }}" 
                       wire:navigate
                       class="flex flex-col items-center p-4 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition-colors duration-200 group">
                        <flux:icon.cube class="size-8 text-green-600 dark:text-green-400 group-hover:scale-110 transition-transform duration-200" />
                        <span class="mt-2 text-sm font-medium text-green-700 dark:text-green-300">View Products</span>
                    </a>
                    
                    @can('manage products')
                    <a href="{{ route('locations.dashboard') }}" 
                       wire:navigate
                       class="flex flex-col items-center p-4 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 rounded-lg transition-colors duration-200 group">
                        <flux:icon.map-pin class="size-8 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform duration-200" />
                        <span class="mt-2 text-sm font-medium text-purple-700 dark:text-purple-300">Locations</span>
                    </a>
                    @endcan
                    
                    @can('view scans')
                    <a href="{{ route('scans.index') }}" 
                       wire:navigate
                       class="flex flex-col items-center p-4 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 rounded-lg transition-colors duration-200 group">
                        <flux:icon.chart-bar class="size-8 text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform duration-200" />
                        <span class="mt-2 text-sm font-medium text-amber-700 dark:text-amber-300">Scan History</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>