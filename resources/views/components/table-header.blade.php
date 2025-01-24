<thead class="bg-gray-50 dark:bg-gray-700">
<tr>
    @foreach($columns as $column)
        <th wire:click="sortBy('{{ $column['key'] }}')" scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            {{ $column['label'] }}
            <span class="inline-flex align-middle ml-2 text-gray-600 dark:text-gray-400">
                    @if($sortDirection === 'asc')
                    <!-- Ascending Arrow -->
                    <x-icons.chevron-up size="4"/>
                @else
                    <!-- Descending Arrow -->
                    <x-icons.chevron-down size="4"/>
                @endif
                </span>
        </th>
    @endforeach
    <th scope="col" class="relative px-6 py-3">
        <span class="sr-only">Actions</span>
    </th>
</tr>
</thead>
