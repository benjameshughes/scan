@php
    use Carbon\Carbon;

    // Move date formatting logic to a more readable format
    $scanDate = Carbon::parse($scan->created_at);
    $formattedDate = $scanDate->diffInDays(now()) < 3
        ? $scanDate->diffForHumans()
        : $scanDate->format('D F jS, Y, H:i:s');

    // Define reusable classes for better maintainability
    $dtClass = "text-sm font-medium text-gray-500 dark:text-gray-400";
    $ddClass = "mt-1 text-sm text-gray-900 dark:text-white";
    $borderClass = "border-b border-gray-200 dark:border-gray-700";
@endphp

<div class="max-sm:px-4">
    {{-- Breadcrumbs --}}
    <div class="mb-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item icon="home" href="{{route('dashboard')}}">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{route('scan.index')}}">Scans</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{$scan->id}}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    {{-- Header Section --}}
    <div>
        <h3 class="text-base/7 font-semibold text-gray-900 dark:text-white">
            {{ __('Scan :id', ['id' => $scan->id]) }}
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Scanned :date', ['date' => $formattedDate]) }}
        </p>
    </div>

    {{-- Details Section --}}
    <div class="mt-6 border-t border-gray-200 pt-5 dark:border-gray-700">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-1">
            {{-- Barcode Information --}}
            <div class="sm:col-span-1 pb-5 {{ $borderClass }}">
                <dt class="{{ $dtClass }}">{{ __('Barcode') }}</dt>
                <dd class="{{ $ddClass }}">{{ $scan->barcode }}</dd>
                <dd class="{{ $ddClass }}">
                    <span class="text-sm text-gray-500">
                        {{ $scan->product->sku ?? __('No SKU Found') }}
                    </span>
                </dd>
            </div>

            {{-- Quantity --}}
            <div class="sm:col-span-1 pb-5 {{ $borderClass }}">
                <dt class="{{ $dtClass }}">{{ __('Quantity') }}</dt>
                <dd class="{{ $ddClass }}">{{ $scan->quantity ?? '0' }}</dd>
            </div>

            {{-- Status with Sync Button --}}
            <div wire:poll.500 class="sm:col-span-1 pb-5 {{ $borderClass }}">
                <dt class="{{ $dtClass }}">{{ __('Status') }}</dt>

                <div class="flex justify-between items-center">
                    <dd class="{{ $ddClass }}">{{ $scan->sync_status }}</dd>
                    @unless($scan->submitted)
                        <dd class="{{ $ddClass }}">
                            <flux:button icon="refresh-ccw" wire:click="sync">
                                {{ __('Sync') }}
                            </flux:button>
                        </dd>
                    @endunless
                </div>
            </div>

            {{-- Submission Date --}}
            <div class="sm:col-span-1 pb-5 {{ $borderClass }}">
                <dt class="{{ $dtClass }}">{{ __('Submitted At') }}</dt>
                <dd class="{{ $ddClass }}">
                    {{ $scan->submitted_at ?? __('Not Submitted') }}
                </dd>
            </div>

            {{-- User Information --}}
            <div class="sm:col-span-1 pb-5 {{ $borderClass }}">
                <dt class="{{ $dtClass }}">{{ __('Scanned By') }}</dt>
                <dd class="{{ $ddClass }}">{{ $scan->user->name }}</dd>
            </div>

            {{--Actions--}}
            @can('delete')
                <div class="sm:col-span-1 pb-5 {{$borderClass}}">
                    <dt class="{{ $dtClass }}">
                        <flux:button variant="danger" wire:click="delete('{{$scan->id}}')">Delete</flux:button>
                    </dt>
                </div>
            @endcan
        </dl>
    </div>
</div>