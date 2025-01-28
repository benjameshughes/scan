<?php

namespace App\Providers;

use App\Tables\TableComponent;
use Illuminate\Database\Eloquent\Builder;
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

    }
}
