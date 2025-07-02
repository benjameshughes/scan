<div class="px-6 py-3 border-t border-zinc-200 dark:border-zinc-700">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        {{-- Per Page Selector --}}
        <div class="flex items-center gap-2">
            <label for="perPage" class="text-sm font-medium text-gray-700 dark:text-gray-200">Show</label>
            <select id="perPage" 
                    wire:model.live="perPage" 
                    class="block w-20 px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                @foreach($perPageOptions as $option)
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