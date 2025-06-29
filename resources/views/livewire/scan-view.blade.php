@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    // Format date with a more readable approach
    $scanDate = Carbon::parse($scan->created_at);
    $formattedDate = $scanDate->diffInDays(now()) < 3
        ? $scanDate->diffForHumans()
        : $scanDate->format('D F jS, Y, H:i:s');

    // Define reusable CSS classes
    $classes = [
        'dt' => "text-sm font-medium text-gray-500 dark:text-gray-400",
        'dd' => "mt-1 text-sm text-gray-900 dark:text-white",
        'border' => "border-b border-gray-200 dark:border-gray-700",
        'section' => "sm:col-span-1 pb-5",
    ];

    // Helper function to create detail sections
    $detailSection = function($label, $content, $extraContent = null) use ($classes) {
        return [
            'label' => $label,
            'content' => $content,
            'extraContent' => $extraContent,
            'class' => "{$classes['section']} {$classes['border']}"
        ];
    };

    // Define all detail sections
    $details = [
        $detailSection(
            __('Barcode'),
            $scan->barcode,
            view('components.scan.sku-with-copy', ['sku' => $scan->product->sku ?? __('No SKU Found')])
        ),
        $detailSection(__('Quantity'), $scan->quantity ?? '0'),
        $detailSection(
            __('Status'),
            $scan->sync_status,
            !$scan->submitted ? view('components.scan.sync-button') : null
        ),
        $detailSection(__('Submitted At'), $scan->submitted_at ?? __('Not Submitted')),
        $detailSection(__('Scanned By'), $scan->user->name),
    ];

    // Product SKU for stock history
    $productSku = $scan->product->sku ?? '026-001';
@endphp

<div class="max-sm:px-4">
    {{-- Breadcrumbs --}}
    <div class="mb-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item icon="home" href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('scan.index') }}">Scans</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $scan->id }}</flux:breadcrumbs.item>
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
            {{-- Render all detail sections --}}
            @foreach($details as $detail)
                <div class="{{ $detail['class'] }}">
                    <dt class="{{ $classes['dt'] }}">{{ $detail['label'] }}</dt>

                    @if($detail['extraContent'])
                        <div class="flex justify-between items-center">
                            <dd class="{{ $classes['dd'] }}">{{ $detail['content'] }}</dd>
                            <dd class="{{ $classes['dd'] }}">
                                {!! $detail['extraContent'] !!}
                            </dd>
                        </div>
                    @else
                        <dd class="{{ $classes['dd'] }}">{{ $detail['content'] }}</dd>
                    @endif
                </div>
            @endforeach

            {{-- Actions Section --}}
            @can('delete', $scan)
                <div class="{{ $classes['section'] }} {{ $classes['border'] }}">
                    <dt class="{{ $classes['dt'] }}">
                        <flux:button variant="danger" wire:click="delete('{{ $scan->id }}')">
                            {{ __('Delete') }}
                        </flux:button>
                    </dt>
                </div>
            @endcan

            {{-- Stock History Modal Trigger --}}
            <div class="{{ $classes['section'] }} {{ $classes['border'] }}">
                <flux:modal.trigger name="getStockItemHistory">
                    <flux:button variant="primary">{{ __('Get Stock History') }}</flux:button>
                </flux:modal.trigger>
            </div>

            {{-- Stock History Modal --}}
            <flux:modal name="getStockItemHistory" x-on:open="$wire.getStockItemHistory('{{ $productSku }}')">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">
                        {{ __('Stock History for') }}: {{ $scan->product->name ?? $productSku }}
                    </h3>

                    {{-- Loading State --}}
                    @if($isLoadingHistory)
                        <div class="py-4">
                            <div class="flex justify-center">
                                <svg class="animate-spin h-6 w-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <p class="text-center mt-2 text-sm text-gray-500">{{ __('Loading stock history...') }}</p>
                        </div>
                    @endif

                    {{-- Error Message --}}
                    @if($errorMessage)
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ $errorMessage }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Stock History Content --}}
                    @if(!$isLoadingHistory && !$errorMessage && $stockHistory)
                        @if(empty($stockHistory))
                            <p class="text-gray-500">{{ __('No history found for this item.') }}</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Change') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Previous') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('New') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Source') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                    @foreach($stockHistory as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($item['ChangeDate'] ?? $item->changeDate ?? '')->format('M d, Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item['ChangeType'] ?? $item->changeType ?? '' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item['PreviousLevel'] ?? $item->previousLevel ?? 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item['NewLevel'] ?? $item->newLevel ?? 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item['ChangeSource'] ?? $item->changeSource ?? '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
            </flux:modal>

    </div>
