<x-scan-layout>
{{-- data-scanner-refactored marker tells pwa-camera-lifecycle.js to skip (we handle our own lifecycle) --}}
<div class="space-y-6 px-4 py-4" data-scanner-refactored>


    {{-- Main Scanner Container --}}
    <div class="flex justify-center">
        <div class="w-full max-w-md">
            {{-- Main Scanner Component --}}
            <livewire:scanner.product-scanner />
        </div>
    </div>
</div>

{{-- Load scanner script only on this page --}}
@vite('resources/js/scanner-page.js')
</x-scan-layout>
