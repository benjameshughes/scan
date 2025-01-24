<div class="flex gap-4">
    @forelse($notifications as $notification)
        <div class="flex-col p-4 text-center">
            <div class="text-gray-500 dark:text-gray-400">{{ $notification->data['message'] }}</div>
            <div class="text-gray-500 dark:text-gray-400">{{ $notification->read_at ? $notification->read_at->format('d/m/Y H:i:s') : 'Not Read' }}</div>
            <div class="text-gray-500 dark:text-gray-400">{{ $notification->created_at->format('d/m/Y H:i:s') }}</div>
        <button>
            <x-primary-button wire:click="markAsRead({{$notification->id}})">
                Mark as Read
            </x-primary-button>
        </button>
        </div>
    @empty
        <div class="p-4 text-center">
            <p class="text-gray-500 dark:text-gray-400">You have no notifications.</p>
        </div>
    @endforelse
</div>