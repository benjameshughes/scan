<div class="flex flex-col gap-y-10">
    <div class="flex justify-between pt-6">
        <div class="flex-none">
            <x-primary-button wire:target="redispatch" wire:click="redispatch">
                <div wire:loading wire:target="redispatch">
                    <x-icons.reload :size="4" class="mr-2 animate-spin"/>
                </div>
                Redispatch
            </x-primary-button>
        </div>

        <!-- Notifications drawer -->
        <div x-data="{open: false}">
            <button x-on:click="open = ! open">
                @if($notifications->count() > 0)
                    <x-icons.bell-new :size="6" class="text-red-500 animate-bounce"/>
                @else
                    <x-icons.bell :size="6" class="text-gray-500"/>
                @endif
            </button>

            <div x-show="open" class="relative z-10">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

                <div class="fixed inset-0 overflow-hidden">
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">

                            <div class="pointer-events-auto relative w-screen max-w-md">

                                <div class="absolute left-0 top-0 -ml-8 flex pr-2 pt-4 sm:-ml-10 sm:pr-4">
                                    <button
                                            x-on:click="open = ! open"
                                            type="button"
                                            class="relative rounded-md text-gray-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                                        <span class="absolute -inset-2.5"></span>
                                        <span class="sr-only">Close panel</span>
                                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                             stroke="currentColor" aria-hidden="true" data-slot="icon">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M6 18 18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                                    <div class="px-4 sm:px-6">
                                        <h2 class="text-base font-semibold text-gray-900" id="slide-over-title">
                                            Notifications</h2>
                                    </div>
                                    <div class="relative mt-6 flex-1 px-4 sm:px-6">
                                        @forelse($notifications as $notification)
                                            <div class="p-4 space-x-4 text-center min-h-svh border-gray-100 dark:border-gray-700">
                                                <div class="text-gray-500 dark:text-gray-400">{{ $notification->data['message'] }}</div>
                                                <div class="text-gray-500 dark:text-gray-400">{{ $notification->read_at ? $notification->read_at->format('d/m/Y H:i:s') : 'Not Read' }}</div>
                                                <div class="text-gray-500 dark:text-gray-400">{{ $notification->created_at->format('d/m/Y H:i:s') }}</div>
                                                <x-primary-button type="button" wire:click="markAsRead('{{$notification->id}}')">Acknowledge</x-primary-button>
                                            </div>
                                        @empty
                                            <div class="p-4 text-center">
                                                <p class="text-gray-500 dark:text-gray-400">You have no notifications.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex grow gap-4">
        <x-widget :title="'Pending'" :stat="$scans->where('submitted', false)->count()"/>
        <x-widget :title="'Completed'" :stat="$scans->where('submitted', true)->count()"/>
        <x-widget :title="'Scans this Week'" :stat="$scans->whereBetween('created_at', [now()->subWeek(), now()])->count()"/>
    </div>
    <div>
        <x-list :items="$scans" :itemName="'Scan'" :itemDescription="'Scan not submitted'" :routeName="'scan.show'"/>
    </div>
</div>