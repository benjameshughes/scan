@php
    use Carbon\Carbon
    ;if (\Carbon\Carbon::parse($scan->created_at)->diffInDays(now()) < 3) {
        $scanDate = \Carbon\Carbon::parse($scan->created_at)->diffForHumans(Carbon::now());
    } else {
        $scanDate = \Carbon\Carbon::parse($scan->created_at)->format('D F jS, Y, H:i:s');
    }
@endphp
<div>
    <div class="px-4 sm:px-0">
        <h3 class="text-base/7 font-semibold text-gray-900 dark:text-white">{{__('Scan ' . $this->scan->id)}}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{__('Scanned')}} {{$scanDate}}</p>
    </div>
    <div class="mt-6 border-t border-gray-200 pt-5 dark:border-gray-700">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-1">
            <div class="sm:col-span-1 pb-5 border-b border-gray-200 dark:border-gray-700">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{__('Barcode')}}
                </dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{$scan->barcode}}
                </dd>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    <span class="text-sm text-gray-500">{{$scan->product->sku ?? 'No SKU Found'}}</span>
                </dd>
            </div>
            <div class="sm:col-span-1 pb-5 border-b border-gray-200 dark:border-gray-700">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{__('Quantity')}}
                </dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{$scan->quantity ?? '0'}}
                </dd>
            </div>
            <div wire:poll.500 class="sm:col-span-1 pb-5 border-b border-gray-200 dark:border-gray-700">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{__('Status')}}
                </dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{$scan->sync_status}}
                </dd>
            </div>
            <div class="sm:col-span-1 pb-5 border-b border-gray-200 dark:border-gray-700">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{__('Submitted At')}}
                </dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{$scan->submitted_at ?? __('Not Submitted')}}
                </dd>
            </div>
            <div class="sm:col-span-1 pb-5 border-b border-gray-200 dark:border-gray-700">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{__('Scanned By')}}
                </dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{$scan->user->name}}
                </dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{__('Actions')}}
                </dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    <a wire:click="sync">
                        <button type="button" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-500">
                        {{__('Sync')}}
                        </button>
                    </a>
                    <a href="{{route('scan.edit', $scan)}}" class="ml-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-500">
                        {{__('Logs')}}
                    </a>
                </dd>
            </div>
        </dl>
    </div>
</div>