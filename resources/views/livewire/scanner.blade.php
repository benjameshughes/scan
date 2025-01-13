<div>
    <!-- Video feed -->

    <div wire:loading.class.delay="opacity-100" wire:target="loadingCamera" class="mb-4">
        <video
                id="video"
                class="w-full h-full border rounded-xl"
                playsinline
                autoplay
        ></video>
    </div>

    <!-- Controls -->
    <div class="flex space-y-4">
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

    <div class="flex space-x-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{$loadingCamera ? 'On' : 'Off'}}
        </p>
    </div>

    <!-- Video source select -->
    <div id="sourceSelectPanel" class="hidden">
        <label for="sourceSelect">Change video source:</label>
        <select id="sourceSelect" style="max-width:400px">
        </select>
    </div>

    <!-- Results -->

{{--        <div id="result" class="mt-4 p-4 bg-green-100 border border-green-400 rounded">--}}
{{--            <p class="font-bold">Scanned Code:</p>--}}
{{--            <p>{{ $barcode }}</p>--}}
{{--        </div>--}}

{{--    <label>Result:</label>--}}
{{--    <pre><code id="result"></code></pre>--}}
</div>