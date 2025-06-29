@props(['message' => null, 'type' => 'info'])

@if($message)
    @php
        $classes = match($type) {
            'success' => 'bg-green-100 border-green-400 text-green-800 dark:bg-green-900/50 dark:border-green-600 dark:text-green-200',
            'error', 'danger' => 'bg-red-100 border-red-400 text-red-800 dark:bg-red-900/50 dark:border-red-600 dark:text-red-200',
            'warning' => 'bg-amber-100 border-amber-400 text-amber-800 dark:bg-amber-900/50 dark:border-amber-600 dark:text-amber-200',
            default => 'bg-blue-100 border-blue-400 text-blue-800 dark:bg-blue-900/50 dark:border-blue-600 dark:text-blue-200'
        };
    @endphp
    
    <div class="{{ $classes }} border-l-4 p-4 mb-4 rounded-md" x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-start justify-between">
            <div class="flex-1">
                {{ $message }}
            </div>
            <button @click="show = false" class="ml-4 text-current opacity-60 hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>
@endif