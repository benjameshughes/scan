<div class="dark:bg-zinc-800">
    <!-- Camera Loading State -->
    @if($loadingCamera)
        <div class="flex justify-center items-center h-80 bg-zinc-100 dark:bg-zinc-700 rounded-t">
            <div class="text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Loading camera...</p>
            </div>
        </div>
    @else
        <!-- Video element -->
        <div class="relative rounded-t mb-4">
            <video
                id="video"
                class="w-full h-80 object-cover rounded-t {{ $isScanning ? '' : 'opacity-50' }}"
                playsinline
                autoplay
                muted
            >
            </video>
            
            <!-- Camera Status Overlay -->
            @if(!$isScanning)
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-t">
                    <p class="text-white text-lg">Camera Stopped</p>
                </div>
            @endif
            
            <!-- Controls -->
            <div class="flex justify-end gap-2 px-4 py-2 absolute bottom-0 right-0">
                <!-- Torch Button -->
                <flux:button 
                    type="button" 
                    wire:click="torchStatus" 
                    icon="flashlight{{ $isTorchOn ? '' : '-off' }}" 
                    square 
                    size="sm" 
                    variant="filled"
                    color="{{ $isTorchOn ? 'orange' : 'zinc' }}"
                    class="{{ $torchSupported ? '' : 'opacity-50' }}"
                    title="{{ $torchSupported ? ($isTorchOn ? 'Turn off flashlight' : 'Turn on flashlight') : 'Flashlight not supported' }}"
                />
                
                <!-- Camera Toggle Button -->
                <flux:button 
                    type="button" 
                    wire:click="camera" 
                    icon="video" 
                    square 
                    size="sm" 
                    variant="filled"
                    color="{{ $isScanning ? 'red' : 'green' }}"
                    class="{{ $isScanning ? 'animate-pulse' : '' }}"
                    title="{{ $isScanning ? 'Stop camera' : 'Start camera' }}"
                />
            </div>
        </div>
    @endif

    <!-- Error Display -->
    @if($cameraError)
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded dark:bg-red-900 dark:border-red-600 dark:text-red-300">
            <div class="flex justify-between items-center">
                <span class="text-sm">{{ $cameraError }}</span>
                <flux:button 
                    wire:click="clearError" 
                    size="xs" 
                    variant="ghost" 
                    icon="circle-x"
                    title="Dismiss error"
                />
            </div>
        </div>
    @endif

    <!-- Camera Status Info -->
    <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">
        <div class="flex justify-between items-center">
            <span>
                Status: <span class="font-medium {{ $isScanning ? 'text-green-600 dark:text-green-400' : 'text-zinc-600 dark:text-zinc-300' }}">
                    {{ $isScanning ? 'Scanning' : 'Stopped' }}
                </span>
            </span>
            @if($torchSupported)
                <span>
                    Torch: <span class="font-medium {{ $isTorchOn ? 'text-orange-600 dark:text-orange-400' : 'text-zinc-600 dark:text-zinc-300' }}">
                        {{ $isTorchOn ? 'On' : 'Off' }}
                    </span>
                </span>
            @endif
        </div>
    </div>
</div>