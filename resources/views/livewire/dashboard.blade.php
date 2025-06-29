<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <!-- Header Section -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm border rounded-xl border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                        Welcome back, {{ auth()->user()->name }}
                    </h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                        Here's what's happening with your warehouse today
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Re-sync Button -->
                    <button 
                        wire:click="redispatch" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Re-sync Data
                    </button>
                    
                    @if($retryCount > 0)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            {{ $retryCount }} items queued
                        </span>
                    @endif

                    <!-- Notifications Button -->
                    <div x-data="{open: false}" class="relative">
                        <button 
                            x-on:click="open = !open"
                            class="relative p-2 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition-colors duration-200">
                            @if($notifications->count() > 0)
                                <svg class="w-6 h-6 text-red-500 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C13.1 2 14 2.9 14 4C14 4.74 13.6 5.39 13 5.73V7H14C16.21 7 18 8.79 18 11V16L20 18V19H4V18L6 16V11C6 8.79 7.79 7 10 7H11V5.73C10.4 5.39 10 4.74 10 4C10 2.9 10.9 2 12 2ZM10 21C10 22.1 10.9 23 12 23S14 22.1 14 21H10Z"/>
                                </svg>
                                <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full"></span>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5a7.5 7.5 0 00-7.5-7.5H2a1 1 0 000 2h5.5A5.5 5.5 0 0113 11.5V17z"/>
                                </svg>
                            @endif
                        </button>

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
                                                    <button 
                                                        wire:click="readAll"
                                                        class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                                        Mark all read
                                                    </button>
                                                @endif
                                                <button x-on:click="open = false" class="text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
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
                                                        <button 
                                                            wire:click="markAsRead('{{ $notification->id }}')"
                                                            class="mt-3 inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/50 dark:text-blue-200 dark:hover:bg-blue-900 rounded-md transition-colors duration-200">
                                                            Mark as read
                                                        </button>
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

    <!-- Main Content -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Stats Grid -->
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
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
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
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
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
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
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
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Stats Section -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 mb-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Your Performance</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['user_total_scans']) }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Scans by You</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['user_weekly_scans']) }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">This Week</p>
                    </div>
                    <div class="text-center">
                        @php
                            $percentage = $stats['total_scans'] > 0 ? round(($stats['user_total_scans'] / $stats['total_scans']) * 100, 1) : 0;
                        @endphp
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $percentage }}%</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Your Contribution</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Failed Scans Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Activity -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Latest scanning activity across the warehouse</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @forelse($recent_scans->take(6) as $scan)
                                <div class="flex items-center justify-between py-3 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $scan->product->name ?? 'Unknown Product' }}
                                            </p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                by {{ $scan->user->name }} • {{ $scan->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $scan->submitted ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' }}">
                                            {{ $scan->submitted ? 'Synced' : 'Pending' }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">No recent activity</p>
                                </div>
                            @endforelse
                        </div>
                        
                        @if($recent_scans->count() > 6)
                            <div class="mt-6 text-center">
                                <a href="{{ route('scans.index') }}" 
                                   class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                    View all activity →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Failed Scans -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Attention Required</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Scans that need manual review</p>
                    </div>
                    <div class="p-6">
                        @if($scans->count() > 0)
                            <div class="space-y-4">
                                @foreach($scans as $scan)
                                    <div class="flex items-center justify-between py-3 border-b border-zinc-100 dark:border-zinc-700 last:border-b-0">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $scan->product->name ?? 'Unknown Product' }}
                                                </p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $scan->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        <button 
                                            wire:click="markAsSubmitted({{ $scan->id }})"
                                            class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/50 dark:text-blue-200 dark:hover:bg-blue-900 rounded-md transition-colors duration-200">
                                            Mark Complete
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-6">
                                {{ $scans->links() }}
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-green-400 dark:text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">All scans are up to date!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('scan.scan') }}" 
                       class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors duration-200 group">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="mt-2 text-sm font-medium text-blue-700 dark:text-blue-300">Start Scanning</span>
                    </a>
                    
                    <a href="{{ route('products.index') }}" 
                       class="flex flex-col items-center p-4 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition-colors duration-200 group">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="mt-2 text-sm font-medium text-green-700 dark:text-green-300">View Products</span>
                    </a>
                    
                    @can('view scans')
                    <a href="{{ route('scans.index') }}" 
                       class="flex flex-col items-center p-4 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 rounded-lg transition-colors duration-200 group">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="mt-2 text-sm font-medium text-purple-700 dark:text-purple-300">Scan History</span>
                    </a>
                    @endcan
                    
                    @can('view users')
                    <a href="{{ route('users.index') }}" 
                       class="flex flex-col items-center p-4 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 rounded-lg transition-colors duration-200 group">
                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        <span class="mt-2 text-sm font-medium text-amber-700 dark:text-amber-300">Manage Users</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>