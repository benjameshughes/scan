@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Simple Pagination Navigation" class="flex items-center justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 cursor-default rounded-md">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Previous
            </span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled" rel="prev" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:text-gray-500 dark:hover:text-gray-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 active:bg-zinc-100 dark:active:bg-zinc-600 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Previous
            </button>
        @endif

        {{-- Page Info --}}
        <div class="flex items-center">
            <span class="text-sm text-gray-700 dark:text-gray-300 px-3">
                <span class="font-medium">{{ $paginator->currentPage() }}</span>
                <span class="text-gray-500 dark:text-gray-400">of</span>
                <span class="font-medium">{{ $paginator->lastPage() }}</span>
            </span>
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled" rel="next" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:text-gray-500 dark:hover:text-gray-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 active:bg-zinc-100 dark:active:bg-zinc-600 transition ease-in-out duration-150">
                Next
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
        @else
            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 cursor-default rounded-md">
                Next
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </span>
        @endif
    </nav>
@endif