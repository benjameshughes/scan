<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\LinnworksApiService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Horizon snapshot command
Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Schedule Linnworks API auth call every 15 minutes
Schedule::command('linnworks:refresh-token')->everyFifteenMinutes();
