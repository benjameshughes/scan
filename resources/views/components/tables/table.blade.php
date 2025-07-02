{{--<div>--}}
{{--    <div class="space-y-4">--}}
{{--        @include('components.tables.table-header')--}}

{{--        <div class="overflow-hidden shadow-sm rounded-lg border-gray-200 border-1 flex">--}}
{{--            <table class="min-w-full divide-y divide-gray-300 flex-col">--}}
{{--                @include('components.tables.table-columns')--}}
{{--                @include('components.tables.table-rows')--}}
{{--            </table>--}}
{{--        </div>--}}

{{--        @include('components.tables.table-pagination')--}}
{{--    </div>--}}
{{--</div>--}}

<div class="table-container">
    {{-- Search --}}
    @if($this->hasSearch())
        <div class="mb-4">
            <input wire:model.live.debounce.300ms="search"
                   placeholder="Search (min 2 chars)..."
                   class="form-input w-full">
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
            <tr>
                @foreach($table->getColumns() as $column)
                    <th class="px-4 py-2 text-left">
                        @if($column->isSortable())
                            <button wire:click="sortBy('{{ $column->getName() }}')"
                                    class="flex items-center gap-1">
                                {{ $column->getLabel() }}
                                @if($sortField === $column->getName())
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        @else
                            {{ $column->getLabel() }}
                        @endif
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr class="border-t">
                    @foreach($table->getColumns() as $column)
                        <td class="px-4 py-2">
                            {{ $column->getValue($row) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $data->links('pagination.custom') }}
    </div>
</div>