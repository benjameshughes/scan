<div class="flex items-center gap-2">
    @foreach($actions as $action)
        @if($action['type'] === 'delete')
            <button wire:click="{{ $action['action'] }}"
                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-{{ $action['color'] }}-700 dark:text-{{ $action['color'] }}-300 bg-{{ $action['color'] }}-100 dark:bg-{{ $action['color'] }}-900 rounded hover:bg-{{ $action['color'] }}-200 dark:hover:bg-{{ $action['color'] }}-800 focus:outline-none focus:ring-1 focus:ring-{{ $action['color'] }}-500"
                    title="{{ $action['label'] }}">
                @if($action['icon'] === 'trash')
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                @endif
                <span class="ml-1">{{ $action['label'] }}</span>
            </button>
        @elseif($action['url'])
            <a href="{{ $action['url'] }}"
               class="inline-flex items-center px-2 py-1 text-xs font-medium text-{{ $action['color'] }}-700 dark:text-{{ $action['color'] }}-300 bg-{{ $action['color'] }}-100 dark:bg-{{ $action['color'] }}-900 rounded hover:bg-{{ $action['color'] }}-200 dark:hover:bg-{{ $action['color'] }}-800 focus:outline-none focus:ring-1 focus:ring-{{ $action['color'] }}-500"
               title="{{ $action['label'] }}">
                @if($action['icon'] === 'eye')
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                @elseif($action['icon'] === 'pencil')
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                @endif
                <span class="ml-1">{{ $action['label'] }}</span>
            </a>
        @endif
    @endforeach
</div>