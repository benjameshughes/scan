<div class="dark:bg-gray-800">
    <!-- Video element -->
    <div
{{--            x-show="showVideo"--}}
            class="mb-4 relative">
        <video
                id="video"
                class="w-full h-auto object-cover"
                playsinline
                autoplay
        ></video>
    </div>

    <!-- Controls -->
    <div class="flex gap-4 px-4">
        @if(!$isScanning)
            <button
                    type="button"
                    wire:click="startScan"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full"
            >
                Start Scanner
            </button>
        @else
            <button
                    type="button"
                    wire:click="stopScan"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full"
            >
                Stop Scanner
            </button>
        @endif

            <button
                    type="button"
                    x-data="{ torch: false, bgColor: 'bg-blue-500' }"
                    @click="torch = ! torch; $wire.dispatch(torch ? 'torchOn' : 'torchOff'); bgColor = torch ? 'bg-red-500' : 'bg-blue-500'"
                    :class="bgColor"
                    x-text="torch ? 'Torch On' : 'Torch Off'"
                    class="hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full"
            >
                Torch Off
            </button>

    </div>

    <!-- Video source select -->
    <div id="sourceSelectPanel" class="hidden">
        <label for="sourceSelect">Change video source:</label>
        <select id="sourceSelect" style="max-width:400px">
        </select>
    </div>
</div>