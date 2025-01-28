<tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
@foreach($data as $row)
    <tr wire:key="{{ $row->id }}">
        @foreach($columns as $column)
            <td class="px-6 py-4 whitespace-nowrap text-{{ $column->getAlignment() }} text-sm dark:text-gray-400 dark:bg-gray-800 dark:border-gray-100">
                {!! $column->render($row) !!}
            </td>
        @endforeach
    </tr>
@endforeach
</tbody>