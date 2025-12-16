<?php

use App\Http\Controllers\LinnworksController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ScanController;
use App\Http\Middleware\IsInviteValid;
use App\Livewire\Invites\Accept;
use App\Models\Product;
use App\Models\User;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::redirect('/', 'dashboard')->name('home');
Route::get('/invite/{token}', Accept::class)->name('invite.accept')->middleware(IsInviteValid::class);

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
    // Scanner
    Route::get('scanner', [ScanController::class, 'scan'])->name('scan.scan')->middleware('permission:view scanner');

    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // API Routes for frontend integration
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('user/settings', function () {
            return response()->json(auth()->user()->settings);
        })->name('user.settings');
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
        Route::get('{product}', function (Product $product) {
            return view('products.show', compact('product'));
        })->name('show');
        Route::get('{product}/edit', function (Product $product) {
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
        Route::get('{user}', function (User $user) {
            return view('users.show', compact('user'));
        })->name('show');
        Route::get('{user}/edit', function (User $user) {
            return view('users.edit', compact('user'));
        })->name('edit')->middleware('permission:edit users');
    });

    /*
    |--------------------------------------------------------------------------
    | Location Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('locations')->name('locations.')->group(function () {
        // Location dashboard and management - require view locations permission
        Route::middleware('permission:view locations')->group(function () {
            Route::get('/', function () {
                return view('locations.index');
            })->name('index');
            Route::get('dashboard', function () {
                return view('locations.dashboard');
            })->name('dashboard');
        });

        // Location management - require manage locations permission
        Route::middleware('permission:manage locations')->group(function () {
            Route::get('manage', function () {
                return view('locations.manage');
            })->name('manage');
        });

        // Stock movement routes - require stock movement permissions
        Route::middleware('permission:view stock movements')->group(function () {
            Route::get('movements', function () {
                return view('locations.movements');
            })->name('movements');
        });

        Route::middleware('permission:create stock movements')->group(function () {
            Route::get('movements/create', function () {
                // Debug logging to help diagnose the issue
                \Log::info('Create route accessed', [
                    'user' => auth()->user()->email ?? 'not authenticated',
                    'can_create' => auth()->check() ? auth()->user()->can('create stock movements') : false,
                ]);

                return view('locations.movements.create');
            })->name('movements.create');
        });

        Route::middleware('permission:edit stock movements')->group(function () {
            Route::get('movements/{movement}/edit', function (App\Models\StockMovement $movement) {
                return view('locations.movements.edit', compact('movement'));
            })->name('movements.edit');
        });

        Route::middleware('permission:view stock movements')->group(function () {
            Route::get('movements/{movement}', function (App\Models\StockMovement $movement) {
                return view('locations.movements.show', compact('movement'));
            })->name('movements.show');
        });

        // Debug route to test location endpoints
        Route::get('debug-endpoints', function () {
            $linnworksService = app(\App\Services\LinnworksApiService::class);
            $results = $linnworksService->debugLocationEndpoints();

            return response()->json($results, 200, [], JSON_PRETTY_PRINT);
        })->name('debug');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('permission:manage products')->group(function () {
        Route::get('sync', function () {
            return view('admin.manual-sync');
        })->name('manual-sync');

        Route::get('pending-updates', function () {
            return view('admin.pending-updates');
        })->name('pending-updates');
    });

});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
