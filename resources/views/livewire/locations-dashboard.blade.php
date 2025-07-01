<div class="py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700 mb-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Locations Dashboard
                        </h1>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            Overview of warehouse location usage and analytics
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <flux:button
                            href="{{ route('locations.manage') }}"
                            variant="filled"
                            icon="cog-6-tooth"
                            size="sm"
                        >
                            Manage Locations
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Locations -->
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

            <!-- Active Locations -->
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

            <!-- Recently Used -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Used This Month</p>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                            {{ number_format($stats['recently_used']) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <flux:icon.clock class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
            </div>

            <!-- Never Used -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Never Used</p>
                        <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                            {{ number_format($stats['never_used']) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                        <flux:icon.exclamation-triangle class="size-6 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Trends -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Location Usage Trends</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Daily location usage over the past week</p>
            </div>
            <div class="p-6">
                <div class="flex items-end justify-between h-32 gap-2">
                    @foreach($usageTrends as $trend)
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-blue-100 dark:bg-blue-900 rounded-t-md" 
                                 style="height: {{ $trend['count'] > 0 ? max(8, ($trend['count'] / max(array_column($usageTrends, 'count'))) * 100) : 4 }}%">
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

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Locations by Usage -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Most Used Locations</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Locations ranked by total usage</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($topLocationsByUsage as $index => $location)
                            <div class="flex items-center justify-between py-3 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $location->code }}
                                        </div>
                                        @if($location->name && $location->name !== $location->code)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $location->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $location->use_count }} uses
                                    </div>
                                    @if($location->last_used_at)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $location->last_used_at->diffForHumans() }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <flux:icon.map-pin class="size-12 text-gray-400 mx-auto mb-4" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">No location usage data yet</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    Start using locations to see analytics
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recently Used Locations -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recently Used</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Latest location activity</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($recentlyUsedLocations as $location)
                            <div class="flex items-center justify-between py-3 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <flux:icon.clock class="size-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $location->code }}
                                        </div>
                                        @if($location->name && $location->name !== $location->code)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $location->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $location->last_used_at->diffForHumans() }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $location->use_count }} total uses
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <flux:icon.clock class="size-12 text-gray-400 mx-auto mb-4" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">No recent location activity</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Locations Needing Attention -->
        @if($locationsNeedingAttention->count() > 0)
            <div class="mt-8 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Locations Needing Attention</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Inactive or unused locations</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($locationsNeedingAttention as $location)
                            <div class="flex items-center justify-between py-3 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900 rounded-full flex items-center justify-center">
                                        <flux:icon.exclamation-triangle class="size-4 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $location->code }}
                                        </div>
                                        @if($location->name && $location->name !== $location->code)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $location->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if(!$location->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            Inactive
                                        </span>
                                    @elseif(!$location->last_used_at)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                            Never Used
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 text-center">
                        <flux:button
                            href="{{ route('locations.manage') }}"
                            variant="ghost"
                            size="sm"
                            icon="arrow-right"
                            iconTrailing
                        >
                            Manage All Locations
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>