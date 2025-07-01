<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Volt\Component;

new class extends Component
{
    public string $token = '';

    /**
     * Get the key from the cache
     */
    public function mount()
    {
        $this->token = Cache::get(config('linnworks.cache.session_token_key')) ?? 'No token';
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        $linnworks = new \App\Services\LinnworksApiService();

        $linnworks->refreshToken();

        $this->token = Cache::get(config('linnworks.cache.session_token_key'));
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Linnworks API Key') }}
        </h2>
    </header>

    <section>
        <flux:input copyable readonly variant="filled" value="{{$token}}"/>
    </section>

    <flux:button wire:click="refresh" variant="primary">Refresh</flux:button>
</section>
