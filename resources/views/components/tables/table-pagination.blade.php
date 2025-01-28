<div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6 dark:bg-gray-800 dark:border-gray-700">
    <div class="flex items-center justify-between">
        {{-- Per page selector --}}
        <div class="flex items-center space-x-2 dark:text-gray-400 dark:bg-gray-800 dark:border-gray-100">
            <select
                    wire:model.change="perPage"
                    class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                @foreach([10, 25, 50, 100] as $value)
                    <option value="{{ $value }}">{{ $value }} per page</option>
                @endforeach
            </select>
        </div>

        {{-- Pagination links --}}
        <div>
            {{ $data->links() }}
        </div>
    </div>
</div>