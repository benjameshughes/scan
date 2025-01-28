<div class="dark:bg-gray-800">
    <!-- Video element -->
    <div class="mb-4 relative">
        <video
                id="video"
                class="w-full h-full object-cover"
                playsinline
                autoplay
        ></video>
{{--        <div id="overlay" class="absolute top-0 left-0 w-full h-full bg-black opacity-30 pointer-events-none border inset-3"></div>--}}

    </div>

    <!-- Controls -->
    <div class="flex flex-col px-4">
        @if(!$isScanning)
            <button
                    wire:click="startScan"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 mb-4 rounded w-full"
            >
                Start Scanner
            </button>
        @else
            <button
                    wire:click="stopScan"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full"
            >
                Stop Scanner
            </button>
        @endif
    </div>

    <!-- Video source select -->
    <div id="sourceSelectPanel" class="hidden">
        <label for="sourceSelect">Change video source:</label>
        <select id="sourceSelect" style="max-width:400px">
        </select>
    </div>
</div>