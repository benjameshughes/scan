<tr wire:loading.class.delay="animate-pulse" class="hover:bg-gray-50 dark:hover:bg-gray-700">
    @foreach($columns as $column)
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
            {{ $row[$column['key']] }}
        </td>
    @endforeach
    <td wire:key="{{$row->key}}" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <div class="flex justify-end gap-2">
            <!-- Customize actions for different table types -->
            @forelse($actions as $action)
                <a href="{{ $action['url'] }}">
                    <button class="bg-{{$action['button-colour']}}-400 rounded text-{{$action['button-colour']}}-900 p-2">
                        {{$action['label']}}
                    </button>
                </a>
            @empty
                <span>No actions</span>
            @endforelse
        </div>
    </td>
</tr>
