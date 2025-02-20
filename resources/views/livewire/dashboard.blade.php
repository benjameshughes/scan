<div class="flex flex-col gap-4">
    <div class="flex">
        <div class="flex-none">
            <x-primary-button wire:target="redispatch" wire:click="redispatch">
                Redispatch
            </x-primary-button>
        </div>
    </div>
    <div>
        @forelse($notifications as $notification)
            <div class="flex p-4 space-x-4 text-center">
                <div class="text-gray-500 dark:text-gray-400">{{ $notification->data['message'] }}</div>
                <div class="text-gray-500 dark:text-gray-400">{{ $notification->read_at ? $notification->read_at->format('d/m/Y H:i:s') : 'Not Read' }}</div>
                <div class="text-gray-500 dark:text-gray-400">{{ $notification->created_at->format('d/m/Y H:i:s') }}</div>
                <x-primary-button type="button" wire:click="markAsRead('{{$notification->id}}')">
                    Mark as Read
                </x-primary-button>
            </div>
        @empty
            <div class="p-4 text-center">
                <p class="text-gray-500 dark:text-gray-400">You have no notifications.</p>
            </div>
        @endforelse
    </div>
    <div>
        @forelse($scans as $scan)
            <div class="flex space-x-4 p-4 text-center">
                <div class="text-gray-500 dark:text-gray-400">{{ $scan['barcode'] }}</div>
                <div class="text-gray-500 dark:text-gray-400">Scan not submitted</div>
                <a href="{{ route('scan.show', $scan->id) }}" navigate>
                    <x-primary-button>
                        View
                    </x-primary-button>
                </a>
            </div>
        @empty
            <div class="p-4 text-center">
                <p class="text-gray-500 dark:text-gray-400">You have no scans.</p>
            </div>
        @endforelse
    </div>
</div>