<div class="flex items-center gap-2">
    @foreach($actions as $action)
        @php
            $buttonClasses = "inline-flex items-center px-2 py-1 text-xs font-medium text-{$action['color']}-700 dark:text-{$action['color']}-300 bg-{$action['color']}-100 dark:bg-{$action['color']}-900 rounded hover:bg-{$action['color']}-200 dark:hover:bg-{$action['color']}-800 focus:outline-none focus:ring-1 focus:ring-{$action['color']}-500 transition-colors duration-200";
            $confirmClick = isset($action['confirm']) ? "if(!confirm('{$action['confirm']}')) return false;" : '';
        @endphp

        @if($action['type'] === 'delete')
            <button wire:click="{{ $action['action'] }}"
                    onclick="{{ $confirmClick }}"
                    class="{{ $buttonClasses }}"
                    title="{{ $action['label'] }}"
                    {!! isset($action['attributes']) ? collect($action['attributes'])->map(fn($v, $k) => "$k=\"$v\"")->implode(' ') : '' !!}>
                @include('components.tables.icons.' . $action['icon'])
                <span class="ml-1">{{ $action['label'] }}</span>
            </button>

        @elseif($action['type'] === 'livewire')
            <button onclick="{{ $confirmClick }} {{ str_replace('javascript:', '', $action['url']) }}"
                    class="{{ $buttonClasses }}"
                    title="{{ $action['label'] }}"
                    {!! isset($action['attributes']) ? collect($action['attributes'])->map(fn($v, $k) => "$k=\"$v\"")->implode(' ') : '' !!}>
                @include('components.tables.icons.' . $action['icon'])
                <span class="ml-1">{{ $action['label'] }}</span>
            </button>

        @elseif($action['type'] === 'javascript')
            <button onclick="{{ $confirmClick }} {{ str_replace('javascript:', '', $action['url']) }}"
                    class="{{ $buttonClasses }}"
                    title="{{ $action['label'] }}"
                    {!! isset($action['attributes']) ? collect($action['attributes'])->map(fn($v, $k) => "$k=\"$v\"")->implode(' ') : '' !!}>
                @include('components.tables.icons.' . $action['icon'])
                <span class="ml-1">{{ $action['label'] }}</span>
            </button>

        @elseif($action['url'] && Str::startsWith($action['url'], 'javascript:'))
            <button onclick="{{ $confirmClick }} {{ str_replace('javascript:', '', $action['url']) }}"
                    class="{{ $buttonClasses }}"
                    title="{{ $action['label'] }}"
                    {!! isset($action['attributes']) ? collect($action['attributes'])->map(fn($v, $k) => "$k=\"$v\"")->implode(' ') : '' !!}>
                @include('components.tables.icons.' . $action['icon'])
                <span class="ml-1">{{ $action['label'] }}</span>
            </button>

        @elseif($action['url'])
            <a href="{{ $action['url'] }}"
               onclick="{{ $confirmClick }}"
               class="{{ $buttonClasses }}"
               title="{{ $action['label'] }}"
               {!! isset($action['attributes']) ? collect($action['attributes'])->map(fn($v, $k) => "$k=\"$v\"")->implode(' ') : '' !!}>
                @include('components.tables.icons.' . $action['icon'])
                <span class="ml-1">{{ $action['label'] }}</span>
            </a>
        @endif
    @endforeach
</div>