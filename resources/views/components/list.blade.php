@props([
    'items' => collect(), // The collection of items to display.
    "itemName" => "item", // The name of the item property.
    "itemDescription" => "description", // The name of the item description property.
    'routeName' => 'scan.show', // The route name to use for the "View" button.
    'noDataMessage' => 'No records? That doesn\'t seem right...' // Message to display when there are no items.
])

<ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700 w-full max-sm:w-full">
    @forelse($items as $item)
        <li class="flex justify-between gap-x-6 py-5">
            <div class="flex min-w-0 flex-1 items-center space-x-4">
                <div class="flex-1 min-w-0">
                    <!-- 
                         Display the barcode along with its associated SKU. 
                         Change these property names as needed for your data.
                    -->
                    <div class="text-sm font-medium text-gray-900 truncate dark:text-white">
                        {{$item->id}} - {{ $item->product->sku ?? 'No SKU' }}
                    </div>
                    <div class="text-sm text-gray-500 truncate dark:text-gray-400">
                        {{ $itemDescription }}
                    </div>
                </div>
            </div>
            <div class="shrink-0 sm:flex sm:flex-col sm:items-end">
                <flux:button variant="primary" href="{{route($routeName, $item->id)}}">{{__('View')}}</flux:button>
            </div>
        </li>
    @empty
        <div class="p-4 text-center">
            <p class="text-gray-500 dark:text-gray-400">{{ $noDataMessage }}</p>
        </div>
    @endforelse
</ul>
