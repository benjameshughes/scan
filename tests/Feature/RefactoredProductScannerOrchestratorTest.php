<?php

declare(strict_types=1);

use App\Actions\Scanner\ProcessBarcodeAction;
use App\DTOs\Scanner\BarcodeResult;
use App\DTOs\Scanner\CameraState;
use App\Livewire\Scanner\ProductScanner as Orchestrator;
use App\Models\Product;
use App\Models\User;
use App\Services\Scanner\CameraManagerService;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Permissions
    $role = Role::firstOrCreate(['name' => 'admin']);
    $viewScanner = Permission::firstOrCreate(['name' => 'view scanner']);
    $role->givePermissionTo([$viewScanner]);

    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

it('handles camera lifecycle events via service', function () {
    // Mock camera manager including initial state used during component mount
    $this->mock(CameraManagerService::class, function ($mock) {
        $mock->shouldReceive('getInitialState')->andReturn(new CameraState(false, false, false, false));
        $mock->shouldReceive('handleInitializing')->andReturn(new CameraState(false, true, false, false));
        $mock->shouldReceive('handleReady')->andReturn(new CameraState(true, false, true, false));
        $mock->shouldReceive('handleError')->andReturn(new CameraState(false, false, false, false, 'No camera'));
        $mock->shouldReceive('updateTorchSupport')->andReturn(new CameraState(true, false, true, false));
        $mock->shouldReceive('updateTorchState')->andReturn(new CameraState(true, false, true, true));
    });

    Livewire::test(Orchestrator::class)
        ->dispatch('onCameraInitializing')
        ->assertSet('loadingCamera', true)
        ->assertSet('isScanning', false)
        ->dispatch('onCameraReady')
        ->assertSet('loadingCamera', false)
        ->assertSet('isScanning', true)
        ->dispatch('onTorchSupportDetected', true)
        ->assertSet('torchSupported', true)
        ->dispatch('onTorchStateChanged', true)
        ->assertSet('isTorchOn', true)
        ->dispatch('onCameraError', 'No camera')
        ->assertSet('cameraError', 'No camera');
});

it('processes barcode detection and updates state', function () {
    $product = Product::factory()->create([
        'barcode' => '5059031234567',
        'name' => 'Refactor Test Product',
    ]);

    $this->mock(ProcessBarcodeAction::class, function ($mock) use ($product) {
        $mock->shouldReceive('handleCameraDetection')
            ->andReturn(new BarcodeResult(barcode: $product->barcode, isValid: true, product: $product));
    });

    Livewire::test(Orchestrator::class)
        ->dispatch('onBarcodeDetected', $product->barcode)
        ->assertSet('barcode', $product->barcode)
        ->assertSet('barcodeScanned', true)
        ->assertSet('product.id', $product->id)
        ->assertSet('isScanning', false);
});
