<table class="w-full table-auto divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-800">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scan Date</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted At</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
    </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700 text-center">
    @foreach($scans as $scan)
        <tr>
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                {{ $scan->barcode }}
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                {{ $scan->quantity }}
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{$scan->created_at}}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                {{$scan->submitted_at ?? 'Not Submitted'}}
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white cursor-pointer">
                <x-dropdown>
                    <x-slot name="trigger">
                        Actions
                    </x-slot>
                    <x-slot name="content">
                        <a href="{{route('scan.show', $scan)}}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">View</a>
                        <a href="{{route('scan.sync', $scan)}}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Sync</a>
                    </x-slot>
                </x-dropdown>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
