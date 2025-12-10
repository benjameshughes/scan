<div class="relative">
    {{-- Camera View Container --}}
    <div class="relative bg-black rounded-lg overflow-hidden aspect-video">
        {{-- Video Element --}}
        <video 
            id="video" 
            class="w-full h-full object-cover {{ $isScanning ? 'block' : 'hidden' }}"
            playsinline
            autoplay
            muted
        ></video>

        {{-- Scanning Overlay --}}
        @if ($isScanning && !$barcodeScanned)
            <div class="absolute inset-0 flex items-center justify-center">
                {{-- Scanning Guide --}}
                <div class="relative">
                    <div class="w-64 h-40 border-2 border-green-500 rounded-lg"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-full h-0.5 bg-red-500 animate-pulse"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Loading Camera State --}}
        @if ($loadingCamera)
            <div class="absolute inset-0 bg-zinc-900 flex flex-col items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <p class="text-white text-sm mt-2">Camera Waking Up...</p>
            </div>
        @endif

        {{-- Ready to Scan State --}}
        @if (!$isScanning && !$loadingCamera && !$cameraError)
            <div class="absolute inset-0 bg-zinc-800 flex flex-col items-center justify-center">
                <flux:icon.camera class="w-12 h-12 text-zinc-400 mb-4" />
                <p class="text-white text-sm">Ready to Scan</p>
                <p class="text-zinc-400 text-xs mt-1">Tap to start camera</p>
            </div>
        @endif

        {{-- Camera Error State --}}
        @if ($cameraError)
            <div class="absolute inset-0 bg-red-900 flex flex-col items-center justify-center p-4">
                <flux:icon.exclamation-triangle class="w-12 h-12 text-red-400 mb-4" />
                <p class="text-white text-sm text-center">{{ $cameraError }}</p>
                <button 
                    wire:click="clearError"
                    class="mt-4 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs rounded-md transition-colors"
                >
                    Try Again
                </button>
            </div>
        @endif

        {{-- Barcode Scanned Success State --}}
        @if ($barcodeScanned)
            <div class="absolute inset-0 bg-green-900 flex flex-col items-center justify-center">
                <flux:icon.check-circle class="w-12 h-12 text-green-400 mb-4" />
                <p class="text-white text-sm">Barcode Detected!</p>
            </div>
        @endif
    </div>

    {{-- Camera Controls --}}
    <div class="flex justify-between items-center mt-4">
        {{-- Camera Toggle --}}
        <button 
            wire:click="toggleCamera"
            class="flex items-center space-x-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors"
            wire:loading.attr="disabled"
        >
            @if ($isScanning)
                <flux:icon.pause class="w-4 h-4" />
                <span>Stop Camera</span>
            @else
                <flux:icon.play class="w-4 h-4" />
                <span>Start Camera</span>
            @endif
        </button>

        {{-- Torch Toggle --}}
        @if ($torchSupported && $isScanning)
            <button 
                wire:click="toggleTorch"
                class="flex items-center space-x-2 px-4 py-2 {{ $isTorchOn ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-zinc-600 hover:bg-zinc-700' }} text-white rounded-md transition-colors"
            >
                @if ($isTorchOn)
                    <flux:icon.light-bulb class="w-4 h-4" />
                    <span>Torch On</span>
                @else
                    <flux:icon.light-bulb class="w-4 h-4" />
                    <span>Torch Off</span>
                @endif
            </button>
        @endif
    </div>
</div>
