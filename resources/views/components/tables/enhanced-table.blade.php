<div class="table-container bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
    {{-- Session Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border-b border-green-200 dark:border-green-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.check-circle class="h-5 w-5 text-green-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.x-circle class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Header with search, filters, and actions --}}
    <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            {{-- Search --}}
            @if($this->hasSearch())
                <div class="flex-1 max-w-md">
                    <input wire:model.live="search"
                           placeholder="Search..."
                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                {{-- Filters Toggle --}}
                @if(!empty($table->getFilters()))
                    <button wire:click="toggleFilters"
                            class="px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                            </svg>
                            Filters
                        </span>
                    </button>
                @endif

                {{-- Export --}}
                @if($table->isExportable())
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            Export
                        </button>
                        <div x-show="open" @click.away="open = false"
                             class="absolute right-0 mt-1 w-48 bg-white dark:bg-zinc-700 rounded-md shadow-lg z-10 border border-zinc-200 dark:border-zinc-600">
                            @foreach($table->getExportFormats() as $format)
                                <button wire:click="export('{{ $format }}')"
                                        class="block w-full px-4 py-2 text-sm text-left text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-600">
                                    Export as {{ strtoupper($format) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Create Button --}}
                @if($table->getCreateRoute() || method_exists($this, 'create'))
                    <button wire:click="create"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 dark:bg-blue-700 rounded-md hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        Create New
                    </button>
                @endif
            </div>
        </div>

        {{-- Filters Panel --}}
        @if($showFilters && !empty($table->getFilters()))
            <div class="mt-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-md">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($table->getFilters() as $filter)
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-1">
                                {{ $filter['label'] }}
                            </label>
                            @if($filter['type'] === 'select')
                                <select wire:model.live="filters.{{ $filter['key'] }}"
                                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    @foreach($filter['options'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            @elseif($filter['type'] === 'date')
                                <input type="date"
                                       wire:model.live="filters.{{ $filter['key'] }}"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @else
                                <input type="text"
                                       wire:model.live="filters.{{ $filter['key'] }}"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    <button wire:click="resetFilters"
                            class="px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-700">
                        Reset Filters
                    </button>
                </div>
            </div>
        @endif

        {{-- Bulk Actions --}}
        @if(!empty($table->getBulkActions()) && $table->isSelectable())
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        @if($isSelectingAll)
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400 italic">Selecting...</span>
                            </div>
                        @else
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                {{ count($bulkSelectedIds) }} {{ Str::plural('item', count($bulkSelectedIds)) }} selected
                                @if($selectAllPages && $totalRecordsCount > $perPage)
                                    <span class="text-zinc-500 dark:text-zinc-400">(all {{ $totalRecordsCount }} records)</span>
                                @elseif(count($bulkSelectedIds) > 0 && !$selectAllPages && $totalRecordsCount > $perPage)
                                    <button wire:click="selectAllAcrossPages" class="ml-2 text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                        Select all {{ $totalRecordsCount }} records
                                    </button>
                                @endif
                            </span>
                        @endif
                        
                        @if(count($bulkSelectedIds) > 0)
                            <button wire:click="clearSelection" 
                                    class="ml-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                                <flux:icon.x-mark class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                    
                    @if(!empty($bulkSelectedIds))
                        <div class="flex gap-2 border-l border-zinc-200 dark:border-zinc-700 pl-4">
                            @foreach($table->getBulkActions() as $bulkAction)
                                <button wire:click="executeBulkAction('{{ $bulkAction['name'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 cursor-not-allowed"
                                        class="px-3 py-1 text-sm font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900 rounded-md hover:bg-red-200 dark:hover:bg-red-800 transition-colors duration-200">
                                    <span wire:loading.remove wire:target="executeBulkAction('{{ $bulkAction['name'] }}')">
                                        {{ $bulkAction['label'] }}
                                    </span>
                                    <span wire:loading wire:target="executeBulkAction('{{ $bulkAction['name'] }}')">
                                        Processing...
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    {{-- Bulk Select Column --}}
                    @if($table->isSelectable())
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            <div x-data="{
                                indeterminate: @entangle('bulkSelectedIds').defer,
                                selectAll: @entangle('selectAll').defer,
                                init() {
                                    this.$watch('indeterminate', value => {
                                        const hasSelection = value.length > 0;
                                        const dataCount = {{ $data->count() }};
                                        const isPartial = hasSelection && value.filter(id => {{ json_encode($data->pluck('id')->toArray()) }}.includes(id)).length < dataCount;
                                        this.$refs.checkbox.indeterminate = isPartial;
                                    });
                                }
                            }">
                                <input type="checkbox"
                                       x-ref="checkbox"
                                       wire:model.live="selectAll"
                                       :disabled="$wire.isSelectingAll"
                                       class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-wait">
                            </div>
                        </th>
                    @endif

                    {{-- Column Headers --}}
                    @foreach($table->getColumns() as $column)
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            @if($column->isSortable())
                                <button wire:click="sortBy('{{ $column->getName() }}')"
                                        class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-100">
                                    {{ $column->getLabel() }}
                                    @if($sortField === $column->getName())
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            @if($sortDirection === 'asc')
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                            @else
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            @endif
                                        </svg>
                                    @endif
                                </button>
                            @else
                                {{ $column->getLabel() }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($data as $row)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        {{-- Bulk Select Cell --}}
                        @if($table->isSelectable())
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox"
                                       wire:click="toggleBulkSelect({{ $row->id }})"
                                       @if(in_array($row->id, $bulkSelectedIds)) checked @endif
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </td>
                        @endif

                        {{-- Data Cells --}}
                        @foreach($table->getColumns() as $column)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {!! $column->getValue($row) !!}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($table->getColumns()) + ($table->isSelectable() ? 1 : 0) }}"
                            class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination and Per Page --}}
    <div class="px-6 py-3 border-t border-zinc-200 dark:border-zinc-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            {{-- Per Page Selector --}}
            <div class="flex items-center gap-2">
                <label for="perPage" class="text-sm font-medium text-gray-700 dark:text-gray-200">Show</label>
                <select id="perPage" 
                        wire:model.live="perPage" 
                        class="block w-20 px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                    @foreach($this->perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>
            </div>
            
            {{-- Pagination Links --}}
            @if($data->hasPages())
                <div class="flex-1">
                    {{ $data->links('pagination.custom') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-show="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"></div>
                <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Confirm Delete</h3>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            Are you sure you want to delete this record? This action cannot be undone.
                        </p>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="confirmDelete"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 dark:bg-red-700 text-base font-medium text-white hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button wire:click="cancelDelete"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>