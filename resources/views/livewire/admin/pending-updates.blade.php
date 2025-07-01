<div class="space-y-6">
    <!-- Session Messages -->
    @if (session()->has('message'))
        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-800 rounded-md p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pending Product Updates</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Review changes detected during Linnworks sync
                        @if($pendingCount > 0)
                            <span class="ml-2 inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                {{ $pendingCount }} pending
                            </span>
                        @endif
                    </p>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- Filter -->
                    <flux:select wire:model.live="filter" size="sm">
                        <flux:select.option value="pending">Pending ({{ $pendingCount }})</flux:select.option>
                        <flux:select.option value="auto_accepted">Auto-Accepted ({{ $autoAcceptedCount }})</flux:select.option>
                        <flux:select.option value="approved">Approved ({{ $approvedCount }})</flux:select.option>
                        <flux:select.option value="rejected">Rejected ({{ $rejectedCount }})</flux:select.option>
                    </flux:select>
                    
                    <!-- Bulk Actions -->
                    @if(count($selectedUpdates) > 0 && $filter === 'pending')
                    <div class="flex items-center space-x-3">
                        <flux:button wire:click="bulkApprove" variant="filled" size="sm" icon="check">
                            Approve Selected ({{ count($selectedUpdates) }})
                        </flux:button>
                        <flux:button wire:click="bulkReject" variant="ghost" size="sm" icon="x-mark">
                            Reject Selected
                        </flux:button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Select All Checkbox (for pending items) -->
    @if($filter === 'pending' && $updates->count() > 0)
    <div class="bg-zinc-50 dark:bg-zinc-800 px-6 py-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
        <label class="flex items-center space-x-2">
            <input type="checkbox" wire:model.live="selectAll" 
                   class="h-4 w-4 text-blue-600 bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-600 rounded focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-1">
            <span class="text-sm text-gray-700 dark:text-gray-300">Select all {{ $updates->count() }} items on this page</span>
        </label>
    </div>
    @endif
    
    <!-- Updates List -->
    @forelse($updates as $update)
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3 flex-1">
                    @if($update->status === 'pending')
                    <input type="checkbox" wire:model.live="selectedUpdates" value="{{ $update->id }}" 
                           class="mt-1 h-4 w-4 text-blue-600 bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-600 rounded focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-1">
                    @endif
                    
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $update->product->name }} 
                            <span class="text-sm text-gray-500 dark:text-gray-400">(SKU: {{ $update->product->sku }})</span>
                        </h4>
                        
                        <!-- Changes Preview -->
                        @if(count($update->changes_detected) > 0)
                        <div class="mt-3 space-y-2">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Detected Changes:</p>
                            @foreach($update->changes_detected as $field => $change)
                            <div class="flex items-center text-sm space-x-2">
                                <span class="font-medium text-gray-600 dark:text-gray-400 min-w-[100px]">{{ str_replace('_', ' ', ucfirst($field)) }}:</span>
                                <span class="text-red-600 dark:text-red-400 line-through">{{ $change['local'] ?? 'empty' }}</span>
                                <span class="text-gray-500">→</span>
                                <span class="text-green-600 dark:text-green-400">{{ $change['linnworks'] ?? 'empty' }}</span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">No changes detected</p>
                        @endif
                        
                        <div class="mt-3 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                            <span>Detected {{ $update->created_at->diffForHumans() }}</span>
                            @if($update->isReviewed())
                                <span>•</span>
                                <span>{{ ucfirst($update->status) }} by {{ $update->reviewer->name ?? 'Unknown' }}</span>
                                <span>•</span>
                                <span>{{ $update->reviewed_at->diffForHumans() }}</span>
                            @elseif($update->isAutoAccepted())
                                <span>•</span>
                                <span>Auto-accepted by system</span>
                                <span>•</span>
                                <span>{{ $update->accepted_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($update->status === 'pending')
                <div class="flex items-center space-x-3 ml-4">
                    <flux:button wire:click="approveUpdate({{ $update->id }})" variant="filled" size="sm" icon="check">
                        Approve
                    </flux:button>
                    <flux:button wire:click="rejectUpdate({{ $update->id }})" variant="ghost" size="sm" icon="x-mark">
                        Reject
                    </flux:button>
                </div>
                @else
                <div class="flex items-center ml-4">
                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium 
                        @if($update->status === 'approved')
                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($update->status === 'auto_accepted')
                            bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        @else
                            bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @endif">
                        @if($update->status === 'auto_accepted')
                            Auto-Accepted
                        @else
                            {{ ucfirst($update->status) }}
                        @endif
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">No updates found</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                @if($filter === 'pending')
                    All products are up to date with Linnworks.
                @else
                    No {{ $filter }} updates to display.
                @endif
            </p>
        </div>
    </div>
    @endforelse
    
    <!-- Pagination -->
    @if($updates->hasPages())
    <div class="bg-white dark:bg-zinc-800 px-6 py-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
        {{ $updates->links() }}
    </div>
    @endif
</div>