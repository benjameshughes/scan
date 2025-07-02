<!-- Mobile-First Scanner Layout -->
<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <!-- Header Bar (Mobile) -->
    <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 px-4 py-3 sm:px-6">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Product Scanner
            </h1>
            <div class="flex items-center gap-2">
                @if($torchSupported)
                    <span class="text-xs px-2 py-1 rounded-full {{ $isTorchOn ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400' }}">
                        Flash {{ $isTorchOn ? 'On' : 'Off' }}
                    </span>
                @endif
                <span class="text-xs px-2 py-1 rounded-full {{ $isScanning ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400' }}">
                    {{ $isScanning ? 'Scanning' : 'Stopped' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Email Refill Context Banner -->
    @if($isEmailRefill)
        <div class="bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800 px-4 py-3 sm:px-6">
            <div class="flex items-center gap-3">
                <flux:icon.envelope class="size-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        Email Refill Request
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-300 mt-0.5">
                        You've been directed here from an empty bay notification email
                    </p>
                </div>
                <flux:button
                    wire:click="resetScan"
                    variant="ghost"
                    size="sm"
                    class="text-blue-600 dark:text-blue-400"
                >
                    Normal Scanning
                </flux:button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="px-4 py-4 sm:px-6">
        <!-- Camera Section (only show if no barcode scanned and not in refill mode) -->
        @if(!$barcodeScanned && !$showRefillForm)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden mb-4">
                <div class="relative">
                    @if($loadingCamera)
                        <!-- Loading State -->
                        <div class="flex flex-col items-center justify-center h-64 sm:h-80 bg-zinc-100 dark:bg-zinc-700">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-zinc-300 border-t-blue-600 mb-3"></div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Starting Camera...
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Please allow camera access
                            </p>
                        </div>
                    @else
                        <!-- Camera View -->
                        <div class="relative">
                            <video
                                id="video"
                                class="w-full h-64 sm:h-80 object-cover {{ $isScanning ? '' : 'opacity-50' }}"
                                playsinline
                                autoplay
                                muted
                            ></video>
                            
                            <!-- Camera Overlay -->
                            @if(!$isScanning)
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50">
                                    <div class="text-center text-white px-4">
                                        <svg class="w-12 h-12 mx-auto mb-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-lg font-medium">Camera Paused</p>
                                        <p class="text-sm opacity-90 mt-1">Tap to start scanning</p>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Scanning Guides -->
                            @if($isScanning && !$barcodeScanned)
                                <div class="absolute inset-0 pointer-events-none">
                                    <!-- Center Focus Area -->
                                    <div class="absolute inset-8 border-2 border-green-400 rounded-lg opacity-60">
                                        <!-- Corner Markers -->
                                        <div class="absolute -top-1 -left-1 w-4 h-4 border-l-4 border-t-4 border-green-400"></div>
                                        <div class="absolute -top-1 -right-1 w-4 h-4 border-r-4 border-t-4 border-green-400"></div>
                                        <div class="absolute -bottom-1 -left-1 w-4 h-4 border-l-4 border-b-4 border-green-400"></div>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 border-r-4 border-b-4 border-green-400"></div>
                                    </div>
                                    <!-- Scanning Line -->
                                    <div class="absolute left-8 right-8 top-1/2 h-0.5 bg-gradient-to-r from-transparent via-green-400 to-transparent animate-pulse"></div>
                                </div>
                            @endif
                            
                            <!-- Camera Controls -->
                            <div class="absolute bottom-3 right-3 flex gap-2">
                                <!-- Torch Button -->
                                <button 
                                    wire:click="toggleTorch" 
                                    class="p-3 rounded-full shadow-lg transition-all duration-200 {{ $isTorchOn ? 'bg-orange-600 text-white' : 'bg-white dark:bg-zinc-700 text-gray-600 dark:text-gray-300' }} {{ $torchSupported ? 'active:scale-95' : 'opacity-50' }}"
                                    title="{{ $torchSupported ? ($isTorchOn ? 'Turn off flashlight' : 'Turn on flashlight') : 'Flashlight not supported' }}"
                                    {{ !$torchSupported ? 'disabled' : '' }}
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($isTorchOn)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        @endif
                                    </svg>
                                </button>
                                
                                <!-- Camera Toggle Button -->
                                <button 
                                    wire:click="toggleCamera" 
                                    class="p-3 rounded-full shadow-lg transition-all duration-200 active:scale-95 {{ $isScanning ? 'bg-red-600 text-white animate-pulse' : 'bg-green-600 text-white' }}"
                                    title="{{ $isScanning ? 'Stop camera' : 'Start camera' }}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($isScanning)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M15 14h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @endif
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Messages -->
        @if($cameraError)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-red-200 dark:border-red-800 mb-4">
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-red-700 dark:text-red-300">{{ $cameraError }}</span>
                        </div>
                        <button 
                            wire:click="clearError" 
                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                            title="Dismiss error"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($showSuccessMessage && !$product)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-green-200 dark:border-green-800 mb-4">
                <div class="p-4 bg-green-50 dark:bg-green-900/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-green-700 dark:text-green-300">{{ $successMessage }}</span>
                        </div>
                        <button 
                            wire:click="$set('showSuccessMessage', false)" 
                            class="text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                            title="Dismiss message"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success Message (when product is found) -->
        @if($showSuccessMessage && $product)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-green-200 dark:border-green-800 mb-4">
                <div class="p-4 bg-green-50 dark:bg-green-900/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-green-700 dark:text-green-300">{{ $successMessage }}</span>
                        </div>
                        <button 
                            wire:click="$set('showSuccessMessage', false)" 
                            class="text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                            title="Dismiss message"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Product Information -->
        @if($product)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-4">
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Product Found
                        </h3>
                        <button 
                            wire:click="startNewScan" 
                            class="text-xs px-3 py-1 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-800 dark:text-blue-200 rounded-full font-medium transition-colors duration-200"
                        >
                            Scan Another
                        </button>
                    </div>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product Name</label>
                            <p class="text-base font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $product->name }}</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">SKU</label>
                                <p class="text-sm font-mono text-gray-700 dark:text-gray-200 mt-1">{{ $product->sku }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barcode</label>
                                <p class="text-sm font-mono text-blue-600 dark:text-blue-400 mt-1">{{ $barcode }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($barcodeScanned)
            <!-- Product Not Found -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-4">
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Product Not Found
                        </h3>
                        <button 
                            wire:click="startNewScan" 
                            class="text-xs px-3 py-1 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-800 dark:text-blue-200 rounded-full font-medium transition-colors duration-200"
                        >
                            Scan Another
                        </button>
                    </div>
                </div>
                <div class="p-4">
                    <div class="text-center py-4">
                        <div class="w-16 h-16 mx-auto bg-amber-100 dark:bg-amber-900/20 rounded-full flex items-center justify-center mb-3">
                            <flux:icon.exclamation-triangle class="w-8 h-8 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">No Product Found</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                            No product matches barcode: <span class="font-mono">{{ $barcode }}</span>
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">
                            You can still submit this scan for inventory tracking
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Manual Barcode Input (only show if no barcode scanned and not in refill mode) -->
        @if(!$barcodeScanned && !$showRefillForm)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-4">
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                        Manual Entry
                    </h3>
                </div>
                <form class="p-4 space-y-4">
                    <div>
                        <flux:input 
                            wire:model.live.debounce.3s="barcode"
                            id="barcode"
                            name="barcode"
                            type="text" 
                            label="Barcode *"
                            placeholder="Enter barcode or scan with camera"
                            class="w-full font-mono"
                            inputmode="numeric"
                            required
                        />
                        <flux:error name="barcode"/>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Enter the barcode manually or use the camera scanner above
                        </p>
                    </div>
                </form>
            </div>
        @endif

        <!-- Scan Details Section (show when barcode is scanned and not in refill mode) -->
        @if($barcodeScanned && !$showRefillForm)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-4">
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                        Scan Details
                    </h3>
                </div>
                <form wire:submit="save" class="p-4 space-y-6">
                    <!-- Quantity Control -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <flux:button
                                type="button"
                                wire:click="decrementQuantity"
                                variant="ghost"
                                size="sm"
                                square
                                icon="minus"
                                :disabled="$quantity <= 1"
                                aria-label="Decrease quantity"
                            />
                            
                            <div class="flex-1 text-center">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 bg-zinc-50 dark:bg-zinc-700 rounded-md py-3 border border-zinc-200 dark:border-zinc-600">
                                    {{ $quantity }}
                                </div>
                            </div>
                            
                            <flux:button
                                type="button"
                                wire:click="incrementQuantity"
                                variant="ghost"
                                size="sm"
                                square
                                icon="plus"
                                aria-label="Increase quantity"
                            />
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Use + and - buttons or swipe to adjust quantity
                        </p>
                    </div>

                    <!-- Action Toggle -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                            <div>
                                <label for="scanAction" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                    Increase stock amount
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Enable this toggle to increase the stock amount
                                </p>
                            </div>
                            <flux:switch 
                                wire:model.live="scanAction" 
                                id="scanAction"
                                name="scanAction"
                            />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        @if($product)
                            <div class="grid grid-cols-3 gap-3">
                                <flux:button
                                    type="submit"
                                    variant="primary"
                                    icon="check"
                                >
                                    Submit
                                </flux:button>
                                
                                @can('refill bays')
                                    <flux:button
                                        type="button"
                                        wire:click="showRefillBayForm"
                                        variant="ghost"
                                        :icon="$isProcessingRefill ? 'arrow-path' : 'arrow-path'"
                                        :disabled="$isProcessingRefill"
                                    >
                                        @if($isProcessingRefill)
                                            <span class="hidden sm:inline">Loading...</span>
                                        @else
                                            Refill Bay
                                        @endif
                                    </flux:button>
                                @endcan
                                
                                <flux:button
                                    type="button"
                                    wire:click="emptyBayNotification"
                                    variant="ghost"
                                    icon="exclamation-triangle"
                                >
                                    Empty Bay
                                </flux:button>
                            </div>
                        @else
                            <!-- Save button only for unknown products -->
                            <div class="flex justify-center">
                                <flux:button
                                    type="submit"
                                    variant="filled"
                                    color="green"
                                    icon="check"
                                    class="w-full sm:w-auto px-8"
                                >
                                    <span class="hidden sm:inline">Save Unknown Barcode</span>
                                    <span class="sm:hidden">Save</span>
                                </flux:button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        @endif

        <!-- Refill Bay Form (Full Page View) -->
        @if($showRefillForm)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-4">
                <!-- Form Header -->
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                                {{ $isEmailRefill ? 'Email Refill Request' : 'Refill Bay' }}
                            </h3>
                            @if($isEmailRefill)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Refilling from empty bay notification
                                </p>
                            @endif
                        </div>
                        <flux:button
                            wire:click="cancelRefill"
                            variant="ghost"
                            size="sm"
                            square
                            icon="x-mark"
                            aria-label="Back to scanner"
                        />
                    </div>
                    @if($product)
                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <p class="font-medium">{{ $product->name }}</p>
                            <p class="font-mono text-xs">{{ $product->sku }}</p>
                        </div>
                    @endif
                </div>

                <!-- Form Body -->
                <form wire:submit="submitRefill" class="p-4 space-y-4">
                            <!-- Error Display -->
                            @if($refillError)
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                                    <div class="flex">
                                        <flux:icon.exclamation-triangle class="size-5 text-red-400 flex-shrink-0" />
                                        <div class="ml-3">
                                            <p class="text-sm text-red-800 dark:text-red-200">
                                                {{ $refillError }}
                                            </p>
                                        </div>
                                        <button
                                            wire:click="clearRefillError"
                                            type="button"
                                            class="ml-auto -mx-1.5 -my-1.5 bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 rounded-lg p-1.5 hover:bg-red-100 dark:hover:bg-red-900/40"
                                        >
                                            <flux:icon.x-mark class="size-3" />
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Smart Location Selection -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Transfer From <span class="text-red-500">*</span>
                                </label>
                                
                                @livewire('components.smart-location-selector', [
                                    'locations' => $this->smartLocationSelectorData,
                                    'selectedLocationId' => $selectedLocationId,
                                    'placeholder' => 'Select location to transfer from...',
                                    'showSearch' => true,
                                    'showFavorites' => true,
                                    'showRecent' => true
                                ])
                                
                                <flux:error name="selectedLocationId" />
                                @if(empty($this->smartLocationSelectorData))
                                    <p class="text-xs text-amber-600 dark:text-amber-400">
                                        <flux:icon.exclamation-triangle class="inline w-3 h-3 mr-1" />
                                        No locations with stock found for this product.
                                    </p>
                                @elseif($selectedLocationId && count($this->smartLocationSelectorData) === 1)
                                    <p class="text-xs text-blue-600 dark:text-blue-400">
                                        <flux:icon.information-circle class="inline w-3 h-3 mr-1" />
                                        Auto-selected - only one transfer location available.
                                    </p>
                                @endif
                            </div>

                            <!-- Quantity Control -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Quantity to Transfer <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <flux:button
                                        type="button"
                                        wire:click="decrementRefillQuantity"
                                        variant="ghost"
                                        size="sm"
                                        square
                                        icon="minus"
                                        :disabled="$refillQuantity <= 1"
                                        aria-label="Decrease quantity"
                                    />
                                    
                                    <div class="flex-1 text-center">
                                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100 bg-zinc-50 dark:bg-zinc-700 rounded-md py-2 border border-zinc-200 dark:border-zinc-600">
                                            {{ $refillQuantity }}
                                        </div>
                                    </div>
                                    
                                    <flux:button
                                        type="button"
                                        wire:click="incrementRefillQuantity"
                                        variant="ghost"
                                        size="sm"
                                        square
                                        icon="plus"
                                        aria-label="Increase quantity"
                                    />
                                </div>
                                <flux:error name="refillQuantity" />
                                @if($selectedLocationId)
                                    @php
                                        $selectedLocation = collect($availableLocations)->first(function($location, $index) {
                                            $locationId = $location['Location']['StockLocationId'] ?? $location['LocationId'] ?? $location['locationId'] ?? $location['id'] ?? $index;
                                            return $locationId == $this->selectedLocationId;
                                        });
                                        $maxStock = 0;
                                        if ($selectedLocation) {
                                            $maxStock = $selectedLocation['StockLevel'] ?? $selectedLocation['stockLevel'] ?? $selectedLocation['stock'] ?? 0;
                                        }
                                    @endphp
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Maximum available: {{ $maxStock }} units
                                    </p>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                                <flux:button
                                    type="button"
                                    wire:click="cancelRefill"
                                    variant="ghost"
                                    class="flex-1"
                                >
                                    Cancel
                                </flux:button>
                                <flux:button
                                    type="submit"
                                    variant="filled"
                                    :icon="$isProcessingRefill ? 'arrow-path' : 'arrow-right'"
                                    class="flex-1"
                                    :disabled="$isProcessingRefill || !$selectedLocationId"
                                >
                                    @if($isProcessingRefill)
                                        Processing...
                                    @else
                                        Transfer to Bay
                                    @endif
                                </flux:button>
                            </div>
                </form>
            </div>
        @endif

        <!-- Scan Instructions (only show when no barcode scanned and not in refill mode) -->
        @if(!$barcodeScanned && !$showRefillForm)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-4">
                <div class="p-6 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4m-4 0h.01M12 16V8l4 4-4 4z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        Ready to Scan
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 max-w-sm mx-auto">
                        Point your camera at a barcode or enter it manually above. Make sure the barcode is clearly visible and well-lit.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>