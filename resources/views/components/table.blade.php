<div>
    <div class="py-4 flex items-center justify-between">
        <!-- Search Input -->
        <x-table-search />
    </div>

    <div class="shadow bg-white border-b border-gray-200 rounded-xl dark:bg-gray-800">
        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Table Header -->
                <x-table-header :columns="$columns" :sortDirection="$sortDirection" />

                <!-- Table Body -->
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @foreach($rows as $row)
                    <x-table-row :row="$row" :columns="$columns" :actions="$actions" :key="$row->id" />
                @endforeach

                @if($rows->isEmpty())
                    <tr>
                        <td colspan="{{ count($columns) + 1 }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">
                            No records found
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

    <x-table-pagination :perPageOptions="$perPageOptions" :products="$rows" />
</div>
