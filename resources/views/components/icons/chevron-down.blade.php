@props(['size' => '6', 'stroke' => 'currentColor', 'strokeWidth' => '1.5'])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="{{ $strokeWidth }}" stroke="{{ $stroke }}" {{$attributes->merge(['class' => 'w-'.$size.' h-'.$size])}}>
    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
</svg>
