<div class="p-4 sm:flex sm:items-center sm:justify-between sm:gap-4">
    {{-- Left side / Search --}}
    <div class="sm:flex-1 sm:flex sm:items-center sm:gap-4">
        @if($this->hasSearch())
            <div wire:model.live.delay="search"class="mt-2 sm:mt-0 sm:w-72">
                <label for="search" class="sr-only">Search</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input
                            type="text"
                            wire:model.debounce.300ms="search"
                            id="search"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:placeholder-gray-400 dark:focus:border-gray-500 dark:focus:ring-gray-500"
                            placeholder="Search..."
                    >
                </div>
            </div>
        @endif
    </div>

    {{-- Right side / Filters --}}
    @if($this->hasFilters())
        <div class="mt-2 sm:mt-0 sm:w-72">
            <div class="flex items-center gap-4">
                @foreach($this->getFilters() as $filter)
                    <div wire:key="{{ $filter['key'] }}" class="flex items-center gap-2">
                        <input
                                type="text"
                                wire:model.debounce.300ms="filters.{{ $filter['key'] }}"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="{{ $filter['label'] }}"
                        >
                        <button wire:click="removeFilter('{{ $filter['key'] }}')" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($this->hasActions())
        <div class="mt-2 sm:mt-0 sm:w-72">
            <div class="flex items-center gap-4">
                @foreach($this->getActions() as $action)
                    <button wire:click="{{ $action->getAction() }}" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>