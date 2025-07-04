@php
    $statusColors = [
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
        'synced' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    ];
    
    $statusColor = $statusColors[$row->sync_status] ?? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200';
    $statusText = $row->sync_status_display;
    $hasErrors = $row->sync_status === 'failed' && $row->sync_error_message;
    $hasMultipleFailures = $row->hasMultipleFailures();
@endphp

<div class="flex items-center gap-2">
    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
        @if($row->sync_status === 'synced')
            <flux:icon.check class="size-3 mr-1" />
        @elseif($row->sync_status === 'failed')
            <flux:icon.x-mark class="size-3 mr-1" />
        @else
            <flux:icon.clock class="size-3 mr-1" />
        @endif
        {{ $statusText }}
    </span>
    
    @if($hasMultipleFailures)
        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300" title="Multiple failures detected">
            <flux:icon.exclamation-triangle class="size-3" />
        </span>
    @endif
    
    @if($row->sync_status === 'synced' && $row->synced_at)
        <span class="text-xs text-gray-500 dark:text-gray-400" title="Synced at {{ $row->synced_at->format('M j, Y g:i A') }}">
            {{ $row->synced_at->diffForHumans() }}
        </span>
    @endif
</div>