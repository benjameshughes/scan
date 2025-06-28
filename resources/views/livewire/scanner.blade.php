<div class="dark:bg-zinc-800">
    <!-- Video element -->
        <div wire:x-cloak class="rounded-t mb-4">
            <div wire:loading class="flex justify-center">
                Loading camera...
            </div>
            <video
                    id="video"
                    class="w-full h-80 object-fill rounded-t"
                    playsinline
                    autoplay
            >
            </video>
            <!-- Controls -->
            <div class="flex justify-end gap-4 px-4 relative right-0 bottom-12">
                <flux:button type="button" loading wire:click="torchStatus" icon="flashlight" square size="xs" variant="primary" color="{{$isTorchOn ? 'orange' : 'primary'}}"/>
                <flux:button type="button" loading wire:click="camera" icon="video" square size="xs" variant="primary" class="{{$isScanning ? 'animate-pulse' : ''}}" color="{{$isScanning ? 'orange' : 'primary'}}"/>
            </div>
        </div>

    <!-- Video source select -->
    <div id="sourceSelectPanel" class="hidden">
        <label for="sourceSelect">Change video source:</label>
        <select id="sourceSelect" style="max-width:400px">
        </select>
    </div>
</div>