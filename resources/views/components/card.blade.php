@props([
    'title' => null,
    'subtitle' => null,
    'headerActions' => null,
    'padding' => 'default', // 'none', 'tight', 'default', 'comfortable', 'loose'
    'shadow' => 'default', // 'none', 'subtle', 'default', 'medium', 'large'
    'rounded' => 'medium', // 'small', 'medium', 'large'
])

@php
    $paddingClasses = [
        'none' => '',
        'tight' => 'p-3',
        'default' => 'p-6',
        'comfortable' => 'p-8',
        'loose' => 'p-10'
    ];
    
    $shadowClasses = [
        'none' => '',
        'subtle' => 'shadow-sm',
        'default' => 'shadow',
        'medium' => 'shadow-md',
        'large' => 'shadow-lg'
    ];
    
    $roundedClasses = [
        'small' => 'rounded-md',
        'medium' => 'rounded-lg',
        'large' => 'rounded-xl'
    ];
    
    $cardClasses = implode(' ', [
        'bg-white dark:bg-zinc-800',
        $shadowClasses[$shadow],
        $roundedClasses[$rounded],
        'border border-zinc-200 dark:border-zinc-700'
    ]);
    
    $contentPadding = $paddingClasses[$padding];
@endphp

<div {{ $attributes->merge(['class' => $cardClasses]) }}>
    @if($title || $subtitle || $headerActions)
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    @if($title)
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $title }}</h3>
                    @endif
                    @if($subtitle)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
                    @endif
                </div>
                @if($headerActions)
                    <div class="flex items-center gap-2 ml-4">
                        {{ $headerActions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="{{ $contentPadding }}">
        {{ $slot }}
    </div>
</div>