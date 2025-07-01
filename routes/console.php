<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Horizon snapshot command
Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Schedule Linnworks API auth call every 15 minutes
Schedule::command('linnworks:refresh-token')->everyFifteenMinutes()->name('validate_linnworks_token')
    ->withoutOverlapping();

// Daily sync of all products from Linnworks
Schedule::command('linnworks:daily-sync')
    ->dailyAt('02:00')
    ->name('linnworks_daily_sync')
    ->withoutOverlapping(60) // 1 hour timeout
    ->runInBackground()
    ->onFailure(function () {
        \Log::error('Daily Linnworks sync failed');
    });
