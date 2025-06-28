<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-zinc-900 dark:to-zinc-800">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white text-center">
                Product Scanner
            </h1>
            <p class="text-center text-gray-600 dark:text-gray-300 mt-2">
                Scan barcodes to track inventory
            </p>
        </div>

        <!-- Main Card -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl overflow-hidden">
                
                <!-- Camera Section -->
                <div class="relative">
                    @if($loadingCamera)
                        <!-- Loading State -->
                        <div class="flex flex-col items-center justify-center h-80 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-zinc-700 dark:to-zinc-600">
                            <div class="relative">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600"></div>
                                <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-300 animate-pulse"></div>
                            </div>
                            <p class="mt-4 text-lg font-medium text-gray-700 dark:text-gray-200">
                                Initializing Camera...
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Please allow camera access
                            </p>
                        </div>
                    @else
                        <!-- Camera View -->
                        <div class="relative">
                            <video
                                id="video"
                                class="w-full h-80 object-cover {{ $isScanning ? '' : 'opacity-50 grayscale' }}"
                                playsinline
                                autoplay
                                muted
                            ></video>
                            
                            <!-- Camera Overlay -->
                            @if(!$isScanning)
                                <div class="absolute inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                                    <div class="text-center text-white">
                                        <flux:icon.video class="w-16 h-16 mx-auto mb-3 opacity-80" />
                                        <p class="text-xl font-semibold">Camera Paused</p>
                                        <p class="text-sm opacity-90">Tap to resume scanning</p>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Scanning Animation -->
                            @if($isScanning && !$barcodeScanned)
                                <div class="absolute inset-0 pointer-events-none">
                                    <!-- Scanning Line -->
                                    <div class="absolute left-1/2 transform -translate-x-1/2 w-64 h-0.5 bg-gradient-to-r from-transparent via-green-400 to-transparent animate-pulse"></div>
                                    <!-- Corner Guides -->
                                    <div class="absolute top-20 left-20 w-8 h-8 border-l-4 border-t-4 border-green-400"></div>
                                    <div class="absolute top-20 right-20 w-8 h-8 border-r-4 border-t-4 border-green-400"></div>
                                    <div class="absolute bottom-20 left-20 w-8 h-8 border-l-4 border-b-4 border-green-400"></div>
                                    <div class="absolute bottom-20 right-20 w-8 h-8 border-r-4 border-b-4 border-green-400"></div>
                                </div>
                            @endif
                            
                            <!-- Controls -->
                            <div class="absolute bottom-4 right-4 flex gap-3">
                                <!-- Torch Button -->
                                <flux:button 
                                    wire:click="toggleTorch" 
                                    icon="flashlight{{ $isTorchOn ? '' : '-off' }}" 
                                    square 
                                    variant="filled"
                                    color="{{ $isTorchOn ? 'orange' : 'zinc' }}"
                                    class="shadow-lg {{ $torchSupported ? 'hover:scale-105' : 'opacity-50 cursor-not-allowed' }} transition-all duration-200"
                                    title="{{ $torchSupported ? ($isTorchOn ? 'Turn off flashlight' : 'Turn on flashlight') : 'Flashlight not supported' }}"
                                    :disabled="!$torchSupported"
                                />
                                
                                <!-- Camera Toggle Button -->
                                <flux:button 
                                    wire:click="toggleCamera" 
                                    icon="video" 
                                    square 
                                    variant="filled"
                                    color="{{ $isScanning ? 'red' : 'green' }}"
                                    class="shadow-lg hover:scale-105 transition-all duration-200 {{ $isScanning ? 'animate-pulse' : '' }}"
                                    title="{{ $isScanning ? 'Stop camera' : 'Start camera' }}"
                                />
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Status Bar -->
                <div class="px-6 py-3 bg-gray-50 dark:bg-zinc-700 border-t dark:border-zinc-600">
                    <div class="flex justify-between items-center text-sm">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full {{ $isScanning ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></div>
                                <span class="font-medium {{ $isScanning ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300' }}">
                                    {{ $isScanning ? 'Scanning' : 'Stopped' }}
                                </span>
                            </span>
                            
                            @if($torchSupported)
                                <span class="flex items-center gap-2">
                                    <flux:icon.flashlight class="w-3 h-3 {{ $isTorchOn ? 'text-orange-500' : 'text-gray-400' }}" />
                                    <span class="font-medium {{ $isTorchOn ? 'text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-300' }}">
                                        {{ $isTorchOn ? 'On' : 'Off' }}
                                    </span>
                                </span>
                            @endif
                        </div>
                        
                        @if($barcodeScanned)
                            <span class="flex items-center gap-2 text-green-600 dark:text-green-400">
                                <flux:icon.check-circle class="w-4 h-4" />
                                <span class="font-medium">Barcode Detected</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Error Display -->
                @if($cameraError)
                    <div class="mx-6 mt-4">
                        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <flux:icon.exclamation-triangle class="w-5 h-5" />
                                    <span class="font-medium">{{ $cameraError }}</span>
                                </div>
                                <flux:button 
                                    wire:click="clearError" 
                                    variant="ghost" 
                                    icon="circle-x"
                                    class="text-red-500 hover:text-red-700"
                                    title="Dismiss error"
                                />
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Success Message -->
                @if($showSuccessMessage)
                    <div class="mx-6 mt-4">
                        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
                            <div class="flex items-center gap-3">
                                <flux:icon.check-circle class="w-5 h-5" />
                                <span class="font-medium">{{ $successMessage }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Product Information -->
                @if($barcodeScanned && $product)
                    <div class="p-6 border-t dark:border-zinc-600">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Product Details
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Product Name</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $product->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">SKU</label>
                                    <p class="text-lg font-mono text-gray-700 dark:text-gray-200">{{ $product->sku }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Barcode</label>
                                    <p class="text-lg font-mono text-blue-600 dark:text-blue-400">{{ $barcode }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Manual Barcode Input (always visible) -->
                <div class="p-6 border-t dark:border-zinc-600">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Barcode (Manual Entry)
                        </label>
                        <flux:input 
                            wire:model.live="barcode" 
                            placeholder="Enter barcode or scan with camera"
                            type="number"
                            class="font-mono"
                        />
                        @error('barcode') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>

                <!-- Form Section -->
                @if($barcodeScanned || $barcode)
                    <div class="p-6 border-t dark:border-zinc-600">
                        <form wire:submit="save" class="space-y-6">
                            <!-- Quantity Control -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Quantity
                                </label>
                                <div class="flex items-center gap-4">
                                    <flux:button
                                        type="button"
                                        wire:click="decrementQuantity"
                                        icon="minus"
                                        variant="outline"
                                        class="w-12 h-12"
                                        :disabled="$quantity <= 1"
                                    />
                                    
                                    <div class="flex-1 text-center">
                                        <div class="text-3xl font-bold text-gray-900 dark:text-white bg-gray-50 dark:bg-zinc-700 rounded-xl py-3">
                                            {{ $quantity }}
                                        </div>
                                    </div>
                                    
                                    <flux:button
                                        type="button"
                                        wire:click="incrementQuantity"
                                        icon="plus"
                                        variant="outline"
                                        class="w-12 h-12"
                                    />
                                </div>
                            </div>

                            <!-- Action Toggle -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-700 rounded-xl">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Scan Action
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Enable for special scan operations
                                    </p>
                                </div>
                                <flux:switch wire:model.live="scanAction" />
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-3">
                                <flux:button
                                    type="submit"
                                    variant="filled"
                                    color="blue"
                                    class="w-full"
                                    icon="check"
                                >
                                    Save Scan
                                </flux:button>
                                
                                <div class="flex gap-3">
                                    <flux:button
                                        type="button"
                                        wire:click="emptyBayNotification"
                                        variant="outline"
                                        color="orange"
                                        class="flex-1"
                                        icon="mail"
                                    >
                                        Empty Bay
                                    </flux:button>
                                    
                                    <flux:button
                                        type="button"
                                        wire:click="startNewScan"
                                        variant="outline"
                                        color="green"
                                        class="flex-1"
                                        icon="arrow-path"
                                    >
                                        New Scan
                                    </flux:button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Scan Instructions -->
                    <div class="p-6 text-center">
                        <div class="max-w-md mx-auto">
                            <flux:icon.qr-code class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" />
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                Ready to Scan
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Point your camera at a barcode to get started. Make sure the barcode is clearly visible and well-lit.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>