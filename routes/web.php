<?php

use App\Http\Controllers\ScanController;
use App\Livewire\ScanView;
use App\Models\Scan;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Breeze profile
Route::view('profile', 'profile')
    ->middleware(['auth', 'verified'])
    ->name('profile');

Route::resource('scan', ScanController::class)
    ->middleware(['auth', 'verified']);

// Sync route
Route::get('scan/{scan}/sync', ScanController::class . '@sync')->name('scan.sync');

require __DIR__.'/auth.php';
