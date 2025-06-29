<?php

use App\Jobs\SyncBarcode;
use App\Livewire\ProductScanner;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use Livewire\Livewire;

describe('Complete Scanning Workflow', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001',
            'barcode' => '5059031234567',
        ]);
    });

    test('complete successful scan workflow from start to finish', function () {
        Queue::fake();
        $this->actingAs($this->user);

        // Step 1: User scans a valid barcode
        $component = Livewire::test(ProductScanner::class)
            ->set('barcode', $this->product->barcode)
            ->assertSet('barcodeScanned', true)
            ->assertSet('product.name', 'Test Product')
            ->assertSet('successMessage', 'Test Product')
            ->assertSet('showSuccessMessage', true);

        // Step 2: User adjusts quantity
        $component->call('incrementQuantity')
            ->assertSet('quantity', 2)
            ->call('incrementQuantity')
            ->assertSet('quantity', 3);

        // Step 3: User saves the scan
        $component->call('save')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Scan saved successfully! Ready for next item.')
            ->assertSet('isScanning', true)
            ->assertDispatched('resume-scanning');

        // Verify scan was created in database
        expect(Scan::count())->toBe(1);
        $scan = Scan::first();
        expect($scan->barcode)->toBe($this->product->barcode);
        expect($scan->quantity)->toBe(3);
        expect($scan->user_id)->toBe($this->user->id);
        expect($scan->submitted)->toBeFalse();
        expect($scan->sync_status)->toBe('pending');

        // Verify sync job was dispatched
        Queue::assertPushed(SyncBarcode::class, function ($job) use ($scan) {
            return $job->scan->id === $scan->id;
        });
    });

    test('workflow with invalid barcode shows validation error', function () {
        $this->actingAs($this->user);

        Livewire::test(ProductScanner::class)
            ->set('barcode', '1234567890123') // Invalid prefix
            ->call('save')
            ->assertHasErrors('barcode');
    });

    test('workflow with valid but unknown barcode shows not found message', function () {
        $this->actingAs($this->user);

        Livewire::test(ProductScanner::class)
            ->set('barcode', '5059039999999') // Valid prefix but no product
            ->assertSet('product', null)
            ->assertSet('successMessage', 'No Product Found With That Barcode')
            ->assertSet('showSuccessMessage', true);
    });

    test('user can reset and start new scan', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(ProductScanner::class)
            ->set('barcode', $this->product->barcode)
            ->set('quantity', 5)
            ->assertSet('barcodeScanned', true)
            ->assertSet('showSuccessMessage', true);

        // Reset the scan
        $component->call('resetScan')
            ->assertSet('barcode', null)
            ->assertSet('quantity', 1)
            ->assertSet('barcodeScanned', false)
            ->assertSet('showSuccessMessage', false)
            ->assertSet('product', null);

        // Start new scan
        $component->call('startNewScan')
            ->assertSet('isScanning', true)
            ->assertDispatched('resume-scanning');
    });

    test('multiple products with same barcode can be scanned', function () {
        $this->actingAs($this->user);

        // Create another product with same barcode (edge case)
        $product2 = Product::factory()->create([
            'name' => 'Duplicate Barcode Product',
            'barcode' => $this->product->barcode,
        ]);

        Livewire::test(ProductScanner::class)
            ->set('barcode', $this->product->barcode)
            ->assertSet('barcodeScanned', true)
            ->assertSet('showSuccessMessage', true)
            // Should find one of the products
            ->call('save')
            ->assertHasNoErrors();

        expect(Scan::count())->toBe(1);
    });

    test('user can scan multiple items in sequence', function () {
        Queue::fake();
        $this->actingAs($this->user);

        // Create second product
        $product2 = Product::factory()->create([
            'name' => 'Second Product',
            'barcode' => '5059035555555',
        ]);

        $component = Livewire::test(ProductScanner::class);

        // First scan
        $component->set('barcode', $this->product->barcode)
            ->set('quantity', 2)
            ->call('save')
            ->assertHasNoErrors();

        // Second scan (component auto-resets after save)
        $component->set('barcode', $product2->barcode)
            ->set('quantity', 3)
            ->call('save')
            ->assertHasNoErrors();

        // Verify both scans were created
        expect(Scan::count())->toBe(2);
        $scans = Scan::all();
        expect($scans->pluck('barcode')->toArray())->toContain($this->product->barcode);
        expect($scans->pluck('barcode')->toArray())->toContain($product2->barcode);

        // Verify both sync jobs were dispatched
        Queue::assertPushed(SyncBarcode::class, 2);
    });

    test('empty bay notification workflow', function () {
        Queue::fake();
        $this->actingAs($this->user);

        Livewire::test(ProductScanner::class)
            ->set('barcode', $this->product->barcode)
            ->call('emptyBayNotification')
            ->assertSet('showSuccessMessage', true)
            ->assertSet('successMessage', 'Empty bay notification sent');

        Queue::assertPushed(\App\Jobs\EmptyBayJob::class);
    });

    test('camera controls work correctly', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(ProductScanner::class);

        // Test camera toggle
        $component->call('toggleCamera')
            ->assertSet('isScanning', true)
            ->assertDispatched('camera-state-changed', true);

        $component->call('toggleCamera')
            ->assertSet('isScanning', false)
            ->assertSet('isTorchOn', false) // Torch should turn off
            ->assertDispatched('camera-state-changed', false);

        // Test torch toggle with support
        $component->set('torchSupported', true)
            ->call('toggleTorch')
            ->assertSet('isTorchOn', true)
            ->assertDispatched('torch-state-changed', true);

        // Test torch toggle without support
        $component->set('torchSupported', false)
            ->call('toggleTorch')
            ->assertSet('isTorchOn', false)
            ->assertSet('cameraError', 'Torch not supported on this device');
    });

    test('barcode detection events work correctly', function () {
        $this->actingAs($this->user);

        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', $this->product->barcode)
            ->assertSet('barcode', $this->product->barcode)
            ->assertSet('barcodeScanned', true)
            ->assertSet('isScanning', false)
            ->assertSet('product.name', 'Test Product')
            ->assertSet('successMessage', 'Test Product');
    });

    test('camera event handlers work correctly', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(ProductScanner::class);

        // Camera ready event
        $component->dispatch('onCameraReady')
            ->assertSet('loadingCamera', false)
            ->assertSet('isScanning', true)
            ->assertSet('cameraError', '');

        // Camera error event
        $component->dispatch('onCameraError', 'Camera access denied')
            ->assertSet('loadingCamera', false)
            ->assertSet('isScanning', false)
            ->assertSet('cameraError', 'Camera access denied');

        // Torch support detection
        $component->dispatch('onTorchSupportDetected', true)
            ->assertSet('torchSupported', true);

        $component->set('isTorchOn', true)
            ->dispatch('onTorchSupportDetected', false)
            ->assertSet('torchSupported', false)
            ->assertSet('isTorchOn', false);
    });

    test('quantity controls work correctly', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(ProductScanner::class);

        // Test increment
        $component->assertSet('quantity', 1)
            ->call('incrementQuantity')
            ->assertSet('quantity', 2)
            ->call('incrementQuantity')
            ->assertSet('quantity', 3);

        // Test decrement
        $component->call('decrementQuantity')
            ->assertSet('quantity', 2)
            ->call('decrementQuantity')
            ->assertSet('quantity', 1);

        // Test cannot go below 1
        $component->call('decrementQuantity')
            ->assertSet('quantity', 1);
    });

    test('error clearing works correctly', function () {
        $this->actingAs($this->user);

        Livewire::test(ProductScanner::class)
            ->set('cameraError', 'Some error message')
            ->call('clearError')
            ->assertSet('cameraError', '');
    });

    test('unauthenticated user defaults to user id 1', function () {
        // Don't authenticate user

        Livewire::test(ProductScanner::class)
            ->set('barcode', $this->product->barcode)
            ->set('quantity', 1)
            ->call('save')
            ->assertHasNoErrors();

        $scan = Scan::first();
        expect($scan->user_id)->toBe('1');
    });

    test('scan validation prevents invalid data', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(ProductScanner::class);

        // Test empty barcode
        $component->set('barcode', null)
            ->call('save')
            ->assertHasErrors('barcode');

        // Test invalid quantity
        $component->set('barcode', $this->product->barcode)
            ->set('quantity', 0)
            ->call('save')
            ->assertHasErrors('quantity');

        // Test negative quantity (should be allowed for stock reductions)
        $component->set('quantity', -1)
            ->call('save')
            ->assertHasNoErrors('quantity');
    });
});
