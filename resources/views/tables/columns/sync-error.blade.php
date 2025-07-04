@if($row->sync_status === 'failed' && ($row->sync_error_message || $row->sync_error_type))
    <div class="space-y-1">
        @if($row->sync_error_type)
            <div class="flex items-center gap-1">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">
                    {{ $row->error_type_display }}
                </span>
                @if($row->sync_attempts > 1)
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        ({{ $row->sync_attempts }} attempts)
                    </span>
                @endif
            </div>
        @endif
        
        @if($row->sync_error_message)
            <div class="group relative">
                <p class="text-xs text-red-700 dark:text-red-400 line-clamp-2 cursor-help" 
                   title="{{ $row->sync_error_message }}">
                    {{ Str::limit($row->sync_error_message, 100) }}
                </p>
                
                @if(strlen($row->sync_error_message) > 100)
                    <div class="absolute left-0 top-full mt-1 z-50 hidden group-hover:block bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 shadow-lg max-w-md">
                        <p class="text-xs text-red-700 dark:text-red-300 whitespace-pre-wrap">{{ $row->sync_error_message }}</p>
                    </div>
                @endif
            </div>
        @endif
        
        @if($row->last_sync_attempt)
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Last attempt: {{ $row->last_sync_attempt->diffForHumans() }}
            </p>
        @endif
    </div>
@elseif($row->sync_status === 'pending')
    <div class="text-xs text-amber-600 dark:text-amber-400">
        @if($row->sync_attempts > 0)
            Retrying... ({{ $row->sync_attempts }} previous attempts)
        @else
            Queued for sync
        @endif
    </div>
@else
    <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
@endif