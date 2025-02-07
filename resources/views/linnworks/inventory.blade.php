<x-app-layout>
    <x-slot name="header">
        <h2 class="flex gap-4 font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Linnworks Inventory') }} @if(session('success'))<div class="text-green-600 dark:text-green-400">{{ session('success') }}</div>@endif
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200 rounded-t-lg dark:bg-gray-800">
            <div class="flex flex-wrap justify-center">
                <form class="w-full" action="{{ route('linnworks.fetchInventory') }}" method="get">
                    @csrf
                    <button type="submit" class="w-full md:w-1/2 lg:w-1/3 xl:w-1/4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Refresh Inventory
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200 rounded-t-lg dark:bg-gray-800">
            <h2 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                {{ __('Inventory') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Total Items') }}: {{$products->count()}}
            </p>
        </div>
        <div class="p-4 bg-white border-b border-gray-200 rounded-b-lg dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('SKU') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Name') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Barcode') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quantity
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $product->sku }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $product->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $product->barcode }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
