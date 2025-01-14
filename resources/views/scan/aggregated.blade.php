<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Aggregated') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 rounded-t-lg dark:bg-gray-800">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Total Quantity</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($aggregatedScans as $scan)
                            <tr>
                                <td>{{ $scan->barcode }}</td>
                                <td>{{ $scan->total_quantity }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>