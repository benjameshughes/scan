<x-scan-layout>
    <div class="">
        <div class="w-full mx-auto">
            <div class="bg-white dark:bg-zinc-900 overflow-hidden">
                <div>
                    <livewire:scanner/>

                    <livewire:scan-form/>
                </div>
            </div>
        </div>
    </div>
    @vite('resources/js/barcode-scanner.js')
</x-scan-layout>