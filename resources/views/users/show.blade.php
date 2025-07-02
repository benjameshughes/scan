<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
                {{ __('User Details') }} - {{ $user->name }}
            </h2>
            <div class="flex gap-2">
                @can('update', $user)
                    <flux:button 
                        href="{{ route('users.edit', $user) }}" 
                        wire:navigate
                        variant="primary" 
                        size="sm" 
                        icon="pencil">
                        Edit User
                    </flux:button>
                @endcan
                <flux:button 
                    href="{{ route('users.index') }}" 
                    wire:navigate
                    variant="ghost" 
                    size="sm" 
                    icon="arrow-left">
                    Back to Users
                </flux:button>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- User Details Card -->
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
            <!-- Card Header -->
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="h-12 w-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                {{ $user->initials() }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($user->status === 'active')
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1"></span>
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Basic Information</h4>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Full Name</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                            </div>
                            
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email Address</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</div>
                            </div>
                            
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</label>
                                <div class="mt-1">
                                    @if($user->status === 'active')
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions & Roles -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Permissions & Roles</h4>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Roles</label>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-gray-500 dark:text-gray-400">No roles assigned</span>
                                    @endforelse
                                </div>
                            </div>
                            
                            @if($user->getAllPermissions()->isNotEmpty())
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Direct Permissions</label>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($user->getDirectPermissions() as $permission)
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Account Information</h4>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Member Since</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M j, Y') }}</div>
                            </div>
                            
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Updated</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->updated_at->diffForHumans() }}</div>
                            </div>
                            
                            @if($user->email_verified_at)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email Verified</label>
                                    <div class="mt-1 text-sm text-green-600 dark:text-green-400">
                                        <flux:icon.check-circle class="inline w-4 h-4 mr-1" />
                                        {{ $user->email_verified_at->format('M j, Y') }}
                                    </div>
                                </div>
                            @else
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email Verified</label>
                                    <div class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        <flux:icon.x-circle class="inline w-4 h-4 mr-1" />
                                        Not verified
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity or Additional Information -->
        @if(method_exists($user, 'scans'))
            <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Latest scans and user activity</p>
                </div>
                <div class="p-6">
                    @php
                        $recentScans = $user->scans()->latest()->limit(5)->get();
                    @endphp
                    
                    @if($recentScans->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentScans as $scan)
                                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md">
                                    <div class="flex items-center space-x-3">
                                        <flux:icon.qr-code class="w-5 h-5 text-gray-400" />
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                Scan #{{ $scan->id }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $scan->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Qty: {{ $scan->quantity }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity found.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>