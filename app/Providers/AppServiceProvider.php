<?php

namespace App\Providers;

use App\Tables\TableComponent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
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

        /**
         * Filter query to where column is true.
         *
         * @param string $column
         * @return \Illuminate\Database\Eloquent\Builder
         */
        Builder::macro('whereTrue', function ($column) {
            return $this->where($column, true);
        });

        /**
         * Filter query to where column is false.
         *
         * @param string $column
         * @return \Illuminate\Database\Eloquent\Builder
         */
        Builder::macro('whereFalse', function ($column) {
            return $this->where($column, false);
        });

        /**
         * Collection filter macro
         */
        Collection::macro('whereFalse', function ($column) {
            return $this->where($column, false);
        });

//        // Barcode relationship macro
//        Relation::macro('orWhere', function ($column, $value) {
//            $this->query->orWhere($column, $value);
//            return $this;
//        });

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

        // Allow certain emails to view laravel pulse from an env array
        Gate::define('viewPulse', function ($user) {
            return $user->email == config('pulse.users');
        });

    }
}
