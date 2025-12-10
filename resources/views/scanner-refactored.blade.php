<x-scan-layout>
{{-- data-scanner-refactored marker tells pwa-camera-lifecycle.js to skip (we handle our own lifecycle) --}}
<div class="space-y-6 px-4 py-4" data-scanner-refactored>
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Product Scanner</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scan or enter product barcodes</p>
        </div>
        
        {{-- Navigation Link to Original Scanner --}}
        <div class="flex items-center space-x-3">
            <a 
                href="{{ route('scan.scan') }}" 
                class="inline-flex items-center px-4 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded-md text-sm font-medium transition-colors"
            >
                <flux:icon.arrow-left class="w-4 h-4 mr-2" />
                Original Scanner
            </a>
            
            <div class="inline-flex items-center px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs font-medium">
                <flux:icon.beaker class="w-3 h-3 mr-1" />
                Refactored
            </div>
        </div>
    </div>

    {{-- Main Scanner Container --}}
    <div class="flex justify-center">
        <div class="w-full max-w-md">
            {{-- Main Scanner Component --}}
            <livewire:scanner.product-scanner />
        </div>
    </div>

    {{-- Architecture Info (Debug Only) --}}
    @if (config('app.debug'))
        <div class="bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                <flux:icon.code-bracket class="w-4 h-4 mr-2" />
                Refactored Architecture
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-600 dark:text-gray-400">
                <div class="bg-white dark:bg-zinc-700 p-3 rounded border">
                    <strong class="text-gray-900 dark:text-gray-100">Services (3):</strong><br>
                    CameraManagerService<br>
                    UserFeedbackService<br>
                    LocationManagerService
                </div>
                <div class="bg-white dark:bg-zinc-700 p-3 rounded border">
                    <strong class="text-gray-900 dark:text-gray-100">Actions (7):</strong><br>
                    ProcessBarcodeAction<br>
                    ValidateScanDataAction<br>
                    CreateScanRecordAction<br>
                    + 4 more
                </div>
                <div class="bg-white dark:bg-zinc-700 p-3 rounded border">
                    <strong class="text-gray-900 dark:text-gray-100">Components (7):</strong><br>
                    ProductScanner<br>
                    CameraDisplay<br>
                    ProductInfo<br>
                    + 4 more
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Load scanner script only on this page --}}
@vite('resources/js/scanner-page.js')
</x-scan-layout>
