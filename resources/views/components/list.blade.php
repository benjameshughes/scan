@props([
    'items' => $items, // The collection of items to display.
    "itemName" => "item", // The name of the item property.
    "itemDescription" => "description", // The name of the item description property.
    'routeName' => 'scan.show', // The route name to use for the "View" button.
    'noDataMessage' => 'No records? That doesn\'t seem right...' // Message to display when there are no items.
])

<ul role="list" class="divide-y divide-zinc-200 dark:divide-zinc-700 w-full max-sm:w-full">
    @forelse($items as $item)
        <li class="flex items-center justify-between gap-x-6 py-5">
            <div class="flex min-w-0 flex-grow space-x-4">
                <div class="flex-1 min-w-0">
                    <!-- 
                         Display the barcode along with its associated SKU. 
                         Change these property names as needed for your data.
                    -->
                    <div class="text-sm font-medium text-gray-900 truncate dark:text-gray-100">
                        {{$item->id}} - {{ $item->product->sku ?? 'No SKU' }}
                    </div>
                    <div class="text-sm text-gray-500 truncate dark:text-gray-400">
                        {{ $itemDescription }}
                    </div>
                    <div class="text-sm text-gray-500 truncate dark:text-gray-400">
                        {{ $item->barcode }}
                    </div>
                    <div class="text-sm text-gray-500 truncate dark:text-gray-400">
                        {{ Str::ucfirst($item->sync_status) }}
                    </div>
                </div>
            </div>
            <div class="shrink-0 max-sm:flex-col items-end">
                <flux:button variant="primary" href="{{route($routeName, $item->id)}}" wire:navigate>{{__('View')}}</flux:button>
                @role('admin')
                <flux:button variant="primary" wire:click="markAsSubmitted('{{$item->id}}')">{{__('Mark As Submitted')}}</flux:button>
                @endrole()
            </div>
        </li>
    @empty
        <div class="p-4 text-center">
            <p class="text-gray-500 dark:text-gray-400">{{ $noDataMessage }}</p>
        </div>
    @endforelse
</ul>
