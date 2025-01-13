<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Scan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 rounded-t-lg dark:bg-gray-800">
                    @if(session('success'))
                        <div class="p-4 mb-4 bg-green-100 border border-green-400 rounded">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-error bg-red-100 border border-red-400 rounded">
                            {{ session('error') }}
                        </div>
                    @endif
                        <livewire:scanner />
                    <livewire:scan-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>