<div class="pb-4 sm:flex sm:items-center sm:justify-between sm:gap-4">
    {{-- Left side / Search --}}
    <div class="w-1/4">
        @if($this->hasSearch())
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search" id="search" name="search"/>
        @endif
    </div>
</div>