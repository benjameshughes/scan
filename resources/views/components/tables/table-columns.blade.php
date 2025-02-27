<thead class="">
<tr>
        @foreach($columns as $column)
                <th
                        scope="col"
                        wire:click="sortBy('{{ $column->getName() }}')"
                        class="px-6 py-3 text-{{ $column->getAlignment() }} text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                >
                        {{ $column->getLabel() }}
                        @if($sortField === $column->getName())
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                </th>
        @endforeach
</tr>
</thead>