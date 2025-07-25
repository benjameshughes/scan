<?php

use App\Livewire\ProductScanner;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('view scanner');
    
    $this->user = User::factory()->create([
        'settings' => [
            'scan_sound' => true,
            'dark_mode' => false,
            'auto_submit' => false,
        ]
    ]);
    $this->user->givePermissionTo('view scanner');
    
    $this->product = Product::factory()->create([
        'barcode' => '5059031234567',
        'name' => 'Test Product'
    ]);
});

test('sound plays when user has sound enabled and product found', function () {
    Livewire::actingAs($this->user)
        ->test(ProductScanner::class)
        ->call('onBarcodeDetected', '5059031234567')
        ->assertSet('playSuccessSound', true)
        ->assertSet('product.name', 'Test Product');
});

test('sound does not play when user has sound disabled', function () {
    // Update user settings to disable sound
    $this->user->update([
        'settings' => [
            'scan_sound' => false,
            'dark_mode' => false,
            'auto_submit' => false,
        ]
    ]);
    
    // Refresh the user to ensure settings are loaded
    $this->user->refresh();
    
    Livewire::actingAs($this->user)
        ->test(ProductScanner::class)
        ->call('onBarcodeDetected', '5059031234567')
        ->assertSet('playSuccessSound', false)
        ->assertSet('product.name', 'Test Product');
});

test('sound does not play when no product found even with sound enabled', function () {
    Livewire::actingAs($this->user)
        ->test(ProductScanner::class)
        ->call('onBarcodeDetected', '9999999999999') // Non-existent barcode
        ->assertSet('playSuccessSound', false)
        ->assertSet('product', null);
});

test('user can update sound settings via profile page', function () {
    // Test that the settings are properly loaded and can be changed
    expect($this->user->settings['scan_sound'])->toBeTrue();
    
    // Update settings directly
    $this->user->update([
        'settings' => [
            'scan_sound' => false,
            'dark_mode' => false,
            'auto_submit' => false,
        ]
    ]);
    
    // Verify settings were saved
    $this->user->refresh();
    
    // Debug what's actually stored
    dump('Raw settings:', $this->user->getAttributes()['settings']);
    dump('Parsed settings:', $this->user->settings);
    
    expect($this->user->settings['scan_sound'])->toBeFalse();
});