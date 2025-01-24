<div class="flex w-1/4 items-center gap-4">
    <x-text-input
            wire:model.live.debounce="search"
            type="search"
            placeholder="Search..."
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
    ></x-text-input>

    <div wire:loading.delay class="animate-spin w-6 h-6">
        <div class="w-6 h-6 border-4 border-t-gray-200 border-r-gray-200 rounded-full animate-spin"></div>
    </div>
</div>
