<thead class="bg-gray-50 dark:bg-gray-700 dark:border-gray-700 dark:divide-gray-100">
<tr>
        @foreach($columns as $column)
                <th
                        scope="col"
                        wire:click="sortBy('{{ $column->getName() }}')"
                        class="px-6 py-3 text-{{ $column->getAlignment() }} text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer dark:text-gray-400 dark:hover:text-gray-300 dark:bg-gray-800"
                >
                        {{ $column->getLabel() }}
                        @if($sortField === $column->getName())
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                </th>
        @endforeach
</tr>
</thead>