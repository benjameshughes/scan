<div
    class="bg-white dark:bg-zinc-800 shadow-lg rounded-lg border border-zinc-200 dark:border-zinc-700"
    x-data
    x-init="$nextTick(() => $store.scanner.init())"
    @visibilitychange.window="$store.scanner.handleVisibilityChange()"
    @focus.window="$store.scanner.handleWindowFocus()"
    @blur.window="$store.scanner.handleWindowBlur()"
    @play-success-sound.window="
        if (typeof window.playSuccessSound === 'function') {
            window.playSuccessSound();
        }
    "
    @trigger-vibration.window="
        if (typeof window.triggerVibration === 'function') {
            window.triggerVibration($event.detail);
        }
    "
>
    {{-- Main Content --}}
    <div class="p-6 space-y-6">
        {{-- Camera Section - hidden class just hides visually, video stays in DOM for ZXing --}}
        <div class="{{ $barcodeScanned ? 'hidden' : '' }}">
            {{-- Camera View Container --}}
            <div class="relative bg-black rounded-lg overflow-hidden aspect-video">
                {{-- Video Element - ZXing manages this directly via decodeFromVideoDevice --}}
                <video
                    id="video"
                    class="w-full h-full object-cover"
                    playsinline
                    autoplay
                    muted
                ></video>

                {{-- Scanning Overlay --}}
                @if ($isScanning && !$barcodeScanned)
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
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

                {{-- Ready to Scan State (camera not yet started) --}}
                @if (!$isScanning && !$loadingCamera && !$cameraError)
                    <div class="absolute inset-0 bg-zinc-800 flex flex-col items-center justify-center">
                        <flux:icon.camera class="w-12 h-12 text-zinc-400 mb-4" />
                        <p class="text-white text-sm">Ready to Scan</p>
                        <p class="text-zinc-400 text-xs mt-1">Camera starting...</p>
                    </div>
                @endif

                {{-- Camera Error State --}}
                @if ($cameraError)
                    <div class="absolute inset-0 bg-red-900/90 flex flex-col items-center justify-center p-4">
                        <flux:icon.exclamation-triangle class="w-12 h-12 text-red-400 mb-4" />
                        <p class="text-white text-sm text-center">{{ $cameraError }}</p>
                        <button
                            wire:click="$dispatch('error-cleared')"
                            class="mt-4 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs rounded-md transition-colors"
                        >
                            Try Again
                        </button>
                    </div>
                @endif
            </div>

            {{-- Camera Controls --}}
            <div class="flex justify-between items-center mt-4">
                {{-- Camera Toggle --}}
                <button
                    wire:click="$dispatch('camera-toggle-requested')"
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
                        wire:click="$dispatch('torch-toggle-requested')"
                        class="flex items-center space-x-2 px-4 py-2 {{ $isTorchOn ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-zinc-600 hover:bg-zinc-700' }} text-white rounded-md transition-colors"
                    >
                        <flux:icon.light-bulb class="w-4 h-4" />
                        <span>{{ $isTorchOn ? 'Torch On' : 'Torch Off' }}</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Manual Entry --}}
        @if (!$barcodeScanned)
            <livewire:scanner.manual-entry 
                :barcode="$barcode"
                :barcodeScanned="$barcodeScanned"
                :showRefillForm="$showRefillForm"
                wire:key="manual-entry"
            />
        @endif

        {{-- Product Information --}}
        @if ($barcodeScanned && $product)
            <livewire:scanner.product-info
                :product="$product"
                :barcode="$barcode"
                :barcodeScanned="$barcodeScanned"
                wire:key="product-info"
            />
        @endif

        {{-- Auto-Submit Indicator --}}
        @if ($autoSubmitInProgress && $product)
            <div class="p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-green-600 dark:border-green-400"></div>
                    <div>
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">Auto-submitting scan...</p>
                        <p class="text-xs text-green-600 dark:text-green-300 mt-1">{{ $product->name }} (Qty: 1)</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Scan Form --}}
        @if ($barcodeScanned && $product && !$showRefillForm && !$autoSubmitInProgress)
            <livewire:scanner.scan-form
                :barcode="$barcode"
                :product="$product"
                wire:key="scan-form"
            />
        @endif

        {{-- Refill Form --}}
        @if ($showRefillForm && $product)
            <livewire:scanner.refill-form 
                :product="$product"
                :isEmailRefill="$isEmailRefill"
                wire:key="refill-form"
            />
        @endif

        {{-- Empty Bay Notification --}}
        @if ($showEmptyBayNotification)
            <livewire:scanner.empty-bay-notification 
                :barcode="$barcode"
                :product="$product"
                wire:key="empty-bay-notification"
            />
        @endif
    </div>
</div>
