<x-scan-layout>
    <div class="">
        <div class="w-full mx-auto">
            <div class="bg-white overflow-hidden">
                <div class="p-0 bg-white">

                    <livewire:scanner/>
                    <livewire:scan-form/>

                </div>
            </div>
        </div>
    </div>
    @vite('resources/js/barcode-scanner.js')
</x-scan-layout>