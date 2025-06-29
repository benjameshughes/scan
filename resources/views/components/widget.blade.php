@props(['title' => 'title', 'stat' => '10', 'icon'=>'hourglass'])
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 p-6 rounded-lg shadow-sm w-full']) }}>
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stat }}</p>
        </div>
        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
            <x-lucide-{{$icon}} class="w-6 h-6 text-blue-600 dark:text-blue-400" />
        </div>
    </div>
</div>