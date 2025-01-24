<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white">
            {{ __('Linnworks Profile') }}
        </h2>
    </x-slot>

    @if($token)
        {{ $token }}
    @else
        <a href="{{ route('auth') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-500">
            {{ __('Authorize Linnworks') }}
        </a>
    @endif
</x-app-layout>