@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            {{-- Previous Page Link (Mobile) --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 cursor-default leading-5 rounded-md">
                    Previous
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" rel="prev" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 leading-5 rounded-md hover:text-gray-500 dark:hover:text-gray-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 active:bg-zinc-100 dark:active:bg-zinc-600 active:text-gray-700 dark:active:text-gray-200 transition ease-in-out duration-150">
                    Previous
                </button>
            @endif

            {{-- Next Page Link (Mobile) --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" rel="next" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 leading-5 rounded-md hover:text-gray-500 dark:hover:text-gray-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 active:bg-zinc-100 dark:active:bg-zinc-600 active:text-gray-700 dark:active:text-gray-200 transition ease-in-out duration-150">
                    Next
                </button>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 cursor-default leading-5 rounded-md">
                    Next
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-5">
                    Showing
                    @if ($paginator->firstItem())
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        to
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    of
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-md shadow-sm">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Previous">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <button wire:click="previousPage" wire:loading.attr="disabled" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-l-md leading-5 hover:text-gray-400 dark:hover:text-gray-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 active:bg-zinc-100 dark:active:bg-zinc-600 active:text-gray-500 dark:active:text-gray-400 transition ease-in-out duration-150" aria-label="Previous">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 cursor-default leading-5">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-blue-600 dark:bg-blue-700 border border-blue-600 dark:border-blue-700 cursor-default leading-5">{{ $page }}</span>
                                    </span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 leading-5 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 active:bg-zinc-100 dark:active:bg-zinc-600 active:text-gray-700 dark:active:text-gray-200 transition ease-in-out duration-150" aria-label="Go to page {{ $page }}">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" wire:loading.attr="disabled" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-r-md leading-5 hover:text-gray-400 dark:hover:text-gray-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 active:bg-zinc-100 dark:active:bg-zinc-600 active:text-gray-500 dark:active:text-gray-400 transition ease-in-out duration-150" aria-label="Next">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @else
                        <span aria-disabled="true" aria-label="Next">
                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif