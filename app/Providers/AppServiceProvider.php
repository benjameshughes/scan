<?php

namespace App\Providers;

use App\Tables\TableComponent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Search Macro
        Builder::macro('search', function ($fields, $string) {
            if ($string) {
                foreach ((array) $fields as $field) {
                    $this->orWhere($field, 'like', '%' . $string . '%');
                }
            }
            return $this;
        });

        // Table Component
        Livewire::component('table', TableComponent::class);

        // HTTP Linnworks API Macro
        Http::macro('linnworks', function ($url, $data = []) {
            $response = Http::withBody(json_encode($data))->withHeaders([
                'Authorization' => Cache::get('linnworks.session_token'),
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post(config('linnworks.base_url') . $url);

            return $response->body();
        });

    }
}
