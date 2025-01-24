<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Linnworks Stock Item Details') }}
        </h2>
    </x-slot>

    @foreach($data as $item)
        <div>{{ $item['ItemTitle'] }}</div>
        <div>{{ $item['StockLevels']['StockLevel'] }}</div>
    @endforeach
</x-app-layout>