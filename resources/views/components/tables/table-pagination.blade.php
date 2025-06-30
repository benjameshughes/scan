<div class="py-4 sm:py-6">
    <div class="flex items-center justify-between">
        {{-- Per page selector --}}
        <div class="flex items-center space-x-2 dark:text-gray-400 dark:bg-gray-800 dark:border-gray-100">
            <flux:select wire:model.change="perPage">
                @foreach($perPageOptions as $option)
                    <flux:select.option value="{{$option}}">{{$option}} Per Page</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Pagination links --}}
        <div>
            {{ $data->links('pagination.custom') }}
        </div>
    </div>
</div>