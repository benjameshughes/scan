@props(['title' => 'title', 'stat' => '10', 'icon'=>'hourglass'])
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 p-4
    space-x-4 text-center mr-4 rounded-md shadow-sm w-full']) }}>
    <div class="flex justify-between text-left">
        <div>
            <div class="text-gray-500 dark:text-gray-400">{{ $title }}</div>
            <div class="text-gray-500 dark:text-gray-400">{{ $stat }}</div>
        </div>
        <div>
            <x-lucide-{{$icon}} />
        </div>
    </div>
</div>