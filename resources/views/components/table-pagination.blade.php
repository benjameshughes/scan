<div class="flex justify-between p-4 space-y-4">
    <!-- Per Page Selector -->
    <div>
        <select
                wire:model.live="perPage"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
        >
            @foreach($perPageOptions as $option)
                <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        </select>
        <span class="text-gray-600 text-sm">Per page</span>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $products->links() }}
    </div>
</div>
