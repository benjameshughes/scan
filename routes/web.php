<?php

use App\Http\Controllers\LinnworksController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ScanController;
use App\Http\Middleware\IsInviteValid;
use App\Models\User;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
// Route::redirect('/', 'dashboard')->name('home');
Route::redirect('/', 'scanner')->name('home');
Route::get('scanner', [ScanController::class, 'scan'])->name('scan.scan');
Route::get('/invite/{token}', function ($token) {
    return view('admin.users.invite.accept', ['token' => $token]);
})->name('invite.accept')->middleware(IsInviteValid::class);

// Redirect registration attempts to login with message
Route::get('/register', function () {
    return redirect()->route('login')->with('status', 'Registration is by invitation only. Please contact an administrator.');
})->name('register');

/*
|--------------------------------------------------------------------------
| Authentication Required Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard & Profile

    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    /*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::prefix('users')->name('users.')->middleware('permission:view users')->group(function () {
            Route::get('/', function () {
                return view('admin.users.index');
            })->name('index');

            Route::get('/{user}/edit', function (User $user) {
                return view('admin.users.edit', compact('user'));
            })->name('edit')->middleware('permission:edit users');

            Route::get('create', function () {
                return view('admin.users.add');
            })->name('add')->middleware('permission:create users');
        });

        Route::prefix('invites')->name('invites.')->middleware('permission:create invites')->group(function () {
            Route::get('/', function () {
                return view('admin.invites.index');
            })->name('index');
            Route::get('create', function () {
                return view('admin.users.invite.create');
            })->name('create');
            Route::get('bulk', function () {
                return view('admin.invites.bulk');
            })->name('bulk');
            // Accept route needs to be public, it's up top
        });

        // Keep the old route for backward compatibility
        Route::get('invite/create', function () {
            return view('admin.users.invite.create');
        })->name('invite.create')->middleware('permission:create invites');

    });
});

/*
|--------------------------------------------------------------------------
| Scanning Routes
|--------------------------------------------------------------------------
*/
// Legacy scan resource routes
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
        Route::get('stock-items', 'getStockItems')->name('stock-items');
        Route::get('count', 'getInventoryCount')->name('count');
    });

    // Profile & Resource Routes
    Route::view('profile', 'linnworks.profile')->name('profile');
    Route::get('inventory', LinnworksController::class.'@index')->name('inventory');
    Route::get('inventory/sync', LinnworksController::class.'@fetchInventory')->name('fetchInventory');
    Route::resource('/', LinnworksController::class);
});

/*
|--------------------------------------------------------------------------
| Product Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('products')->name('products.')->middleware('permission:view products')->group(function () {
    Route::get('/', function () {
        return view('products.index');
    })->name('index');
    Route::get('create', function () {
        return view('products.create');
    })->name('create')->middleware('permission:create products');
    Route::get('{product}', function ($product) {
        return view('products.show', compact('product'));
    })->name('show');
    Route::get('{product}/edit', function ($product) {
        return view('products.edit', compact('product'));
    })->name('edit')->middleware('permission:edit products');
    Route::view('import', 'imports.product')->name('import')->middleware('permission:import products');

    // Resource routes for API operations
    Route::resource('api', ProductController::class)->except(['index', 'show', 'create', 'edit']);
});

/*
|--------------------------------------------------------------------------
| Scan Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('scans')->name('scans.')->middleware('permission:view scans')->group(function () {
    Route::get('/', function () {
        return view('scans.index');
    })->name('index');
    Route::get('create', function () {
        return view('scans.create');
    })->name('create')->middleware('permission:create scans');
    Route::get('{scan}', function (App\Models\Scan $scan) {
        return view('scans.show', compact('scan'));
    })->name('show');
});

/*
|--------------------------------------------------------------------------
| Users Management Routes (Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('users')->name('users.')->middleware('permission:view users')->group(function () {
    Route::get('/', function () {
        return view('users.index');
    })->name('index');
    Route::get('create', function () {
        return view('users.create');
    })->name('create')->middleware('permission:create users');
    Route::get('{user}', function ($user) {
        return view('users.show', compact('user'));
    })->name('show');
    Route::get('{user}/edit', function (User $user) {
        return view('users.edit', compact('user'));
    })->name('edit')->middleware('permission:edit users');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
