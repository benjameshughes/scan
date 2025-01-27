<div>
    @empty($filters)

    @else
        <select wire:model="filter">
            @forelse($filters as $filter)
                <option value="{{ $filter['key'] }}">{{ $filter['label'] }}</option>
            @empty
                <option value="0">Not Submitted</option>
            @endforelse
        </select>
    @endif
</div>