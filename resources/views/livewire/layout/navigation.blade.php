<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"/>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                        <livewire:notification-badge/>
                    </x-nav-link>
                    <x-nav-link :href="route('scan.create')" :active="request()->routeIs('scan.create')" wire:navigate>
                        {{ __('Scan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('scans.index')" :active="request()->routeIs('scans.index')" wire:navigate>
                        {{ __('Scan History') }}
                    </x-nav-link>
                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')"
                                wire:navigate>
                        {{ __('Products') }}
                    </x-nav-link>
                    @can('view users')
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')"
                                    wire:navigate>
                            {{ __('Users') }}
                        </x-nav-link>
                    @endcan
                    @can('manage products')
                        <x-nav-link :href="route('locations.dashboard')" :active="request()->routeIs('locations.*')"
                                    wire:navigate>
                            {{ __('Locations') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.pending-updates')" :active="request()->routeIs('admin.pending-updates')"
                                    wire:navigate>
                            {{ __('Sync Updates') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle"
                             aria-label="Toggle dark mode"/>
                <flux:dropdown position="bottom" align="end">
                    <flux:profile
                            initials="{{ auth()->user()->initials() }}"
                            color="auto"
                    />

                    <flux:navmenu>
                        <flux:menu.group heading="Signed in as">
                            <flux:navmenu.item href="{{route('profile')}}" icon="user" wire:navigate>{{__('My Profile')}}</flux:navmenu.item>
                            @can('import products')
                            <flux:navmenu.item href="{{route('products.import')}}" icon="import" wire:navigate>Import</flux:navmenu.item>
                            @endcan
                            @can('manage products')
                            <flux:navmenu.item href="{{route('locations.manage')}}" icon="map-pin" wire:navigate>Manage Locations</flux:navmenu.item>
                            <flux:navmenu.item href="{{route('admin.manual-sync')}}" icon="arrow-path" wire:navigate>Manual Sync</flux:navmenu.item>
                            @endcan
                        </flux:menu.group>
                        <flux:navmenu.item wire:click="logout" icon="trash" variant="danger">Logout</flux:navmenu.item>
                    </flux:navmenu>
                </flux:dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-zinc-100 dark:focus:bg-zinc-700 focus:text-gray-900 dark:focus:text-gray-100 transition-colors duration-200">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
                <livewire:notification-badge/>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('scan.create')" :active="request()->routeIs('scan.create')"
                                   wire:navigate>
                {{ __('Scanner') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('scans.index')" :active="request()->routeIs('scans.index')" wire:navigate>
                {{ __('Scan History') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')"
                                   wire:navigate>
                {{ __('Products') }}
            </x-responsive-nav-link>
            @can('view users')
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')"
                                       wire:navigate>
                    {{ __('Users') }}
                </x-responsive-nav-link>
            @endcan
            @can('manage products')
                <x-responsive-nav-link :href="route('locations.dashboard')" :active="request()->routeIs('locations.*')"
                                       wire:navigate>
                    {{ __('Locations') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.pending-updates')" :active="request()->routeIs('admin.pending-updates')"
                                       wire:navigate>
                    {{ __('Sync Updates') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-zinc-200 dark:border-zinc-700">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200"
                     x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                     x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
