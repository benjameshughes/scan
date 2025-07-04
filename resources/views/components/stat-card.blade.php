@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'trend' => null, // 'up', 'down', 'neutral'
    'color' => 'default', // 'default', 'green', 'red', 'amber', 'blue'
])

@php
    $colorClasses = [
        'default' => [
            'bg' => 'bg-zinc-50 dark:bg-zinc-900',
            'iconBg' => 'bg-zinc-100 dark:bg-zinc-800',
            'iconColor' => 'text-zinc-600 dark:text-zinc-400',
            'value' => 'text-gray-900 dark:text-gray-100'
        ],
        'green' => [
            'bg' => 'bg-green-50 dark:bg-green-900/20',
            'iconBg' => 'bg-green-100 dark:bg-green-900',
            'iconColor' => 'text-green-600 dark:text-green-400',
            'value' => 'text-green-900 dark:text-green-100'
        ],
        'red' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'iconBg' => 'bg-red-100 dark:bg-red-900',
            'iconColor' => 'text-red-600 dark:text-red-400',
            'value' => 'text-red-900 dark:text-red-100'
        ],
        'amber' => [
            'bg' => 'bg-amber-50 dark:bg-amber-900/20',
            'iconBg' => 'bg-amber-100 dark:bg-amber-900',
            'iconColor' => 'text-amber-600 dark:text-amber-400',
            'value' => 'text-amber-900 dark:text-amber-100'
        ],
        'blue' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'iconBg' => 'bg-blue-100 dark:bg-blue-900',
            'iconColor' => 'text-blue-600 dark:text-blue-400',
            'value' => 'text-blue-900 dark:text-blue-100'
        ]
    ];
    
    $colors = $colorClasses[$color];
@endphp

<div {{ $attributes->merge(['class' => "bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700 {$colors['bg']}"]) }}>
    <div class="p-6">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $title }}</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <p class="text-2xl font-bold {{ $colors['value'] }}">{{ $value }}</p>
                    @if($trend)
                        <span class="flex items-center text-xs font-medium {{ $trend === 'up' ? 'text-green-600 dark:text-green-400' : ($trend === 'down' ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400') }}">
                            @if($trend === 'up')
                                <flux:icon.arrow-trending-up class="size-3 mr-1" />
                            @elseif($trend === 'down')
                                <flux:icon.arrow-trending-down class="size-3 mr-1" />
                            @else
                                <flux:icon.minus class="size-3 mr-1" />
                            @endif
                            {{ $trend === 'up' ? 'Trending up' : ($trend === 'down' ? 'Trending down' : 'Stable') }}
                        </span>
                    @endif
                </div>
                @if($subtitle)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
            @if($icon)
                <div class="w-10 h-10 {{ $colors['iconBg'] }} rounded-lg flex items-center justify-center flex-shrink-0">
                    <flux:icon name="{{ $icon }}" class="size-5 {{ $colors['iconColor'] }}" />
                </div>
            @endif
        </div>
    </div>
</div>