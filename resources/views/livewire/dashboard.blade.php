<div class="flex flex-col gap-y-10">
    <div class="flex justify-between pt-6">
        <div class="flex-none">
            <flux:button class="cursor-pointer" variant="primary" wire:click="redispatch">Re-sync</flux:button>
            Retried: {{$retryCount}}
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
                                    <div class="flex justify-between px-4 sm:px-6">
                                        <h2 class="text-base font-semibold text-gray-900" id="slide-over-title">
                                            Notifications</h2>
                                        <flux:button wire:click="readAll" size="sm" variant="danger">Read All</flux:button>
                                    </div>

                                    <div class="relative mt-6 flex-1 px-4 sm:px-6">
                                        <ul role="list">
                                            @forelse($notifications as $notification)
                                                <li wire:key="{{$notification->id}}">
                                                    <div class="flex min-w-0 gap-x-4 border-b mb-4 pb-4 items-center">
                                                        <x-lucide-alert-circle class="w-4 h-4 text-red-500"/>
                                                        <div class="min-w-0 flex-auto">
                                                            <p class="text-gray-500">{{ $notification->data['message'] }}</p>
                                                            <p class="text-xs text-gray-500">{{ $notification->data['scan_id'] }}</p>
                                                            <p class="text-xs text-gray-500">{{$notification->data['barcode']}}</p>
                                                            <p class="text-xs text-gray-500">Date: {{ $notification->created_at->format('d/m/y')}}</p>
                                                        </div>
                                                        <flux:button variant="primary" wire:click="markAsRead('{{$notification->id}}')">Acknowledge</flux:button>
                                                    </div>
                                                </li>
                                            @empty
                                                <span>You have no notifications</span>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <livewire:dashboard.widgets :scan="$scans" />
    <livewire:dashboard.failed-scan-list :scans="$scans"/>
</div>