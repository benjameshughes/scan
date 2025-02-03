<?php

use App\Http\Controllers\LinnworksController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ScanController;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\hasNotifications;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
//Route::redirect('/', 'dashboard')->name('home');
Route::redirect('/', 'scanner')->name('home');
Route::get('scanner', [ScanController::class, 'scan'])->name('scan.scan');

/*
|--------------------------------------------------------------------------
| Authentication Required Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', hasNotifications::class])->group(function () {
    // Dashboard & Profile

    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    /*
    |--------------------------------------------------------------------------
    | Scanning Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('scan', ScanController::class);
    Route::prefix('scan')->name('scan.')->group(function () {
        Route::get('aggregated', [ScanController::class, 'aggregated'])->name('aggregated');
        Route::get('{scanId}/sync', [ScanController::class, 'sync'])->name('sync');
    });

    /*
    |--------------------------------------------------------------------------
    | Linnworks Integration Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('linnworks')->name('linnworks.')->group(function () {
        // API Routes
        Route::controller(LinnworksApiService::class)->group(function () {
            Route::get('stock/{sku}', 'getStockLevel')->name('stock');
        });

        // Profile & Resource Routes
        Route::view('profile', 'linnworks.profile')->name('profile');
        Route::resource('/', LinnworksController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Product Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->name('products.')->group(function () {
        Route::resource('/', ProductController::class);
        Route::view('import', 'imports.product')->name('import');
    });
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';