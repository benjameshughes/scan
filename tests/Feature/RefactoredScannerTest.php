<?php

use App\Livewire\Scanner\ManualEntry;
use App\Livewire\Scanner\ProductScanner;
use App\Livewire\Scanner\ScanForm;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create admin role and permissions
    $role = Role::firstOrCreate(['name' => 'admin']);
    $permission = Permission::firstOrCreate(['name' => 'view scanner']);
    $role->givePermissionTo($permission);

    $this->user = User::factory()->create([
        'settings' => [
            'auto_submit' => false, // Disable auto-submit for tests
            'scan_sound' => true,
            'vibration_pattern' => 'medium',
        ],
    ]);
    $this->user->assignRole('admin');
    $this->actingAs($this->user);

    $this->product = Product::factory()->create([
        'barcode' => '5059039999999',
        'name' => 'Test Scanner Product',
    ]);
});

describe('Camera Barcode Detection', function () {
    test('valid barcode finds product', function () {
        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', '5059039999999')
            ->assertSet('barcode', '5059039999999')
            ->assertSet('barcodeScanned', true)
            ->assertSet('isScanning', false)
            ->assertSet('product.name', 'Test Scanner Product')
            ->assertSet('cameraError', '');
    });

    test('valid prefix but product not found still sets barcode', function () {
        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', '5059031111111')
            ->assertSet('barcode', '5059031111111')
            ->assertSet('barcodeScanned', true)
            ->assertSet('product', null);
    });

    test('invalid barcode prefix is rejected with error', function () {
        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', '1234567890123')
            ->assertSet('barcode', null)
            ->assertSet('barcodeScanned', false)
            ->assertSet('product', null)
            ->assertSet('cameraError', 'The barcode must start with 505903.');
    });

    test('barcode with wrong length is rejected', function () {
        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', '505903123')
            ->assertSet('barcode', null)
            ->assertSet('barcodeScanned', false)
            ->assertSet('product', null)
            ->assertSet('cameraError', 'The barcode must be exactly 13 digits long.');
    });

    test('successful barcode detection dispatches success sound event', function () {
        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', '5059039999999')
            ->assertDispatched('play-success-sound');
    });

    test('successful barcode detection dispatches vibration event', function () {
        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', '5059039999999')
            ->assertDispatched('trigger-vibration');
    });

    test('barcode detection works with secondary barcode', function () {
        $product = Product::factory()->create([
            'barcode' => '1111111111111',
            'barcode_2' => '5059031234567',
            'name' => 'Secondary Barcode Product',
        ]);

        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', '5059031234567')
            ->assertSet('barcode', '5059031234567')
            ->assertSet('barcodeScanned', true)
            ->assertSet('product.name', 'Secondary Barcode Product');
    });

    test('barcode detection works with tertiary barcode', function () {
        $product = Product::factory()->create([
            'barcode' => '1111111111111',
            'barcode_2' => '2222222222222',
            'barcode_3' => '5059031234567',
            'name' => 'Tertiary Barcode Product',
        ]);

        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', '5059031234567')
            ->assertSet('barcode', '5059031234567')
            ->assertSet('barcodeScanned', true)
            ->assertSet('product.name', 'Tertiary Barcode Product');
    });
});

describe('Camera Lifecycle', function () {
    test('camera initializing state sets loading flag', function () {
        Livewire::test(ProductScanner::class)
            ->dispatch('onCameraInitializing')
            ->assertSet('loadingCamera', true)
            ->assertSet('isScanning', false)
            ->assertSet('cameraError', '');
    });

    test('camera ready state clears loading and starts scanning', function () {
        Livewire::test(ProductScanner::class)
            ->set('loadingCamera', true)
            ->dispatch('onCameraReady')
            ->assertSet('loadingCamera', false)
            ->assertSet('isScanning', true)
            ->assertSet('cameraError', '');
    });

    test('camera error handling stops scanning and shows error', function () {
        $errorMessage = 'Camera access denied';

        Livewire::test(ProductScanner::class)
            ->set('loadingCamera', true)
            ->set('isScanning', true)
            ->dispatch('onCameraError', $errorMessage)
            ->assertSet('loadingCamera', false)
            ->assertSet('isScanning', false)
            ->assertSet('cameraError', $errorMessage);
    });

    test('torch support detection enables torch functionality', function () {
        Livewire::test(ProductScanner::class)
            ->dispatch('onTorchSupportDetected', true)
            ->assertSet('torchSupported', true);
    });

    test('torch not supported disables torch functionality', function () {
        Livewire::test(ProductScanner::class)
            ->set('isTorchOn', true)
            ->dispatch('onTorchSupportDetected', false)
            ->assertSet('torchSupported', false)
            ->assertSet('isTorchOn', false);
    });

    test('torch state changes update component state', function () {
        Livewire::test(ProductScanner::class)
            ->dispatch('onTorchStateChanged', true)
            ->assertSet('isTorchOn', true);

        Livewire::test(ProductScanner::class)
            ->dispatch('onTorchStateChanged', false)
            ->assertSet('isTorchOn', false);
    });

    test('torch state changed handles various input types with boolean casting', function () {
        // Test with string "1"
        Livewire::test(ProductScanner::class)
            ->dispatch('onTorchStateChanged', '1')
            ->assertSet('isTorchOn', true);

        // Test with string "0"
        Livewire::test(ProductScanner::class)
            ->dispatch('onTorchStateChanged', '0')
            ->assertSet('isTorchOn', false);

        // Test with integer 1
        Livewire::test(ProductScanner::class)
            ->dispatch('onTorchStateChanged', 1)
            ->assertSet('isTorchOn', true);

        // Test with integer 0
        Livewire::test(ProductScanner::class)
            ->dispatch('onTorchStateChanged', 0)
            ->assertSet('isTorchOn', false);
    });

    test('error cleared event resets camera error', function () {
        Livewire::test(ProductScanner::class)
            ->set('cameraError', 'Some error')
            ->dispatch('error-cleared')
            ->assertSet('cameraError', '');
    });
});

describe('Manual Entry Component', function () {
    test('processes manually entered barcode for valid product', function () {
        $component = Livewire::test(ManualEntry::class)
            ->set('barcode', '5059039999999');

        // Verify the barcode-processed event was dispatched
        $component->assertDispatched('barcode-processed');

        // Check that play-success-sound and trigger-vibration were dispatched
        $component->assertDispatched('play-success-sound');
        $component->assertDispatched('trigger-vibration');
    });

    test('validates barcode format and rejects invalid prefix', function () {
        Livewire::test(ManualEntry::class)
            ->set('barcode', '1234567890123')
            ->assertHasErrors('barcode')
            ->assertDispatched('barcode-processed', [
                'barcode' => null,
                'barcodeScanned' => false,
                'product' => null,
            ]);
    });

    test('validates barcode length requirement', function () {
        Livewire::test(ManualEntry::class)
            ->set('barcode', '505903123')
            ->assertHasErrors('barcode')
            ->assertDispatched('barcode-processed', [
                'barcode' => null,
                'barcodeScanned' => false,
                'product' => null,
            ]);
    });

    test('manual entry triggers success sound for valid product', function () {
        Livewire::test(ManualEntry::class)
            ->set('barcode', '5059039999999')
            ->assertDispatched('play-success-sound');
    });

    test('manual entry triggers vibration for valid product', function () {
        Livewire::test(ManualEntry::class)
            ->set('barcode', '5059039999999')
            ->assertDispatched('trigger-vibration');
    });

    test('clearing barcode resets scan state', function () {
        Livewire::test(ManualEntry::class)
            ->set('barcode', '5059039999999')
            ->set('barcode', null)
            ->assertDispatched('barcode-processed', [
                'barcode' => null,
                'barcodeScanned' => false,
                'product' => null,
            ]);
    });
});

describe('Scan Submission', function () {
    test('creates scan record with valid data', function () {
        Queue::fake();

        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->set('form.quantity', 5)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('scan-submitted');

        expect(Scan::count())->toBe(1);
        $scan = Scan::first();
        expect((string) $scan->barcode)->toBe('5059039999999');
        expect($scan->quantity)->toBe(5);
        expect($scan->user_id)->toBe($this->user->id);
    });

    test('quantity validation requires minimum of 1', function () {
        Queue::fake();

        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->set('form.quantity', 0)
            ->call('save')
            ->assertHasErrors('form.quantity');

        expect(Scan::count())->toBe(0);
    });

    test('increment quantity increases by one', function () {
        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->set('form.quantity', 5)
            ->call('incrementQuantity')
            ->assertSet('form.quantity', 6);
    });

    test('decrement quantity decreases by one', function () {
        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->set('form.quantity', 5)
            ->call('decrementQuantity')
            ->assertSet('form.quantity', 4);
    });

    test('decrement quantity does not go below 1', function () {
        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->set('form.quantity', 1)
            ->call('decrementQuantity')
            ->assertSet('form.quantity', 1);
    });

    test('scan action toggle changes state', function () {
        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->assertSet('form.scanAction', false)
            ->set('form.scanAction', true)
            ->assertSet('form.scanAction', true);
    });

    test('show refill bay form dispatches event', function () {
        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->call('showRefillBayForm')
            ->assertDispatched('refill-form-requested');
    });

    test('empty bay notification dispatches event with barcode', function () {
        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->call('emptyBayNotification')
            ->assertDispatched('empty-bay-notification', [
                'barcode' => '5059039999999',
            ]);
    });
});

describe('State Reset After Submission', function () {
    test('scan submitted event resets orchestrator state', function () {
        Livewire::test(ProductScanner::class)
            ->set('barcode', '5059039999999')
            ->set('barcodeScanned', true)
            ->set('product', $this->product)
            ->dispatch('scan-submitted')
            ->assertSet('barcode', null)
            ->assertSet('barcodeScanned', false)
            ->assertSet('product', null)
            ->assertSet('loadingCamera', true) // Camera is preparing
            ->assertSet('isScanning', false); // Will be true after onCameraReady
    });

    test('scan submitted event dispatches camera state changed', function () {
        Livewire::test(ProductScanner::class)
            ->set('barcode', '5059039999999')
            ->set('barcodeScanned', true)
            ->dispatch('scan-submitted')
            ->assertDispatched('camera-state-changed', true);
    });

    test('new scan requested resets state and prepares camera', function () {
        Livewire::test(ProductScanner::class)
            ->set('barcode', '5059039999999')
            ->set('barcodeScanned', true)
            ->set('isScanning', false)
            ->dispatch('new-scan-requested')
            ->assertSet('barcode', null)
            ->assertSet('barcodeScanned', false)
            ->assertSet('loadingCamera', true) // Camera is preparing
            ->assertSet('isScanning', false) // Will be true after onCameraReady
            ->assertDispatched('camera-state-changed', true);
    });
});

describe('Camera Control Events', function () {
    test('camera toggle requested dispatches camera state change', function () {
        Livewire::test(ProductScanner::class)
            ->assertSet('isScanning', false)
            ->dispatch('camera-toggle-requested')
            ->assertDispatched('camera-state-changed', true);
    });

    test('torch toggle requested when supported changes torch state', function () {
        Livewire::test(ProductScanner::class)
            ->set('torchSupported', true)
            ->dispatch('torch-toggle-requested')
            ->assertSet('isTorchOn', true)
            ->assertDispatched('torch-state-changed', true);
    });

    test('torch toggle requested when not supported shows error', function () {
        Livewire::test(ProductScanner::class)
            ->set('torchSupported', false)
            ->dispatch('torch-toggle-requested')
            ->assertSet('isTorchOn', false)
            ->assertSet('cameraError', 'Torch not supported on this device');
    });
});

describe('Full Workflow', function () {
    test('complete cycle from camera detect to submit scan to reset', function () {
        Queue::fake();

        // Start with ProductScanner orchestrator
        $component = Livewire::test(ProductScanner::class)
            ->assertSet('isScanning', false)
            ->assertSet('barcode', null);

        // Simulate camera initialization
        $component
            ->dispatch('onCameraInitializing')
            ->assertSet('loadingCamera', true);

        // Camera ready
        $component
            ->dispatch('onCameraReady')
            ->assertSet('loadingCamera', false)
            ->assertSet('isScanning', true);

        // Barcode detected by camera
        $component
            ->dispatch('onBarcodeDetected', '5059039999999')
            ->assertSet('barcode', '5059039999999')
            ->assertSet('barcodeScanned', true)
            ->assertSet('product.name', 'Test Scanner Product')
            ->assertSet('isScanning', false);

        // Now test ScanForm submission
        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->set('form.quantity', 3)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('scan-submitted');

        // Verify scan was created
        expect(Scan::count())->toBe(1);
        $scan = Scan::first();
        expect((string) $scan->barcode)->toBe('5059039999999');
        expect($scan->quantity)->toBe(3);
        expect($scan->user_id)->toBe($this->user->id);

        // Verify orchestrator resets after submission
        $component
            ->dispatch('scan-submitted')
            ->assertSet('barcode', null)
            ->assertSet('barcodeScanned', false)
            ->assertSet('product', null)
            ->assertSet('loadingCamera', true) // Camera is preparing
            ->assertSet('isScanning', false); // Will be true after onCameraReady
    });

    test('complete cycle with manual entry instead of camera', function () {
        Queue::fake();

        // Start with manual entry
        Livewire::test(ManualEntry::class)
            ->set('barcode', '5059039999999')
            ->assertDispatched('barcode-processed');

        // Submit scan
        Livewire::test(ScanForm::class, [
            'barcode' => '5059039999999',
            'product' => $this->product,
        ])
            ->set('form.quantity', 2)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('scan-submitted');

        // Verify scan was created
        expect(Scan::count())->toBe(1);
        $scan = Scan::first();
        expect((string) $scan->barcode)->toBe('5059039999999');
        expect($scan->quantity)->toBe(2);
    });

    test('product not found workflow still sets barcode', function () {
        Queue::fake();

        // Valid barcode prefix but no product exists
        $validBarcode = '5059031111111';

        // Camera detects valid barcode with no product
        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', $validBarcode)
            ->assertSet('barcode', $validBarcode)
            ->assertSet('barcodeScanned', true)
            ->assertSet('product', null);

        // Valid barcodes can proceed to submission even without a product
    });

    test('invalid barcode stops workflow with error', function () {
        Queue::fake();

        $invalidBarcode = '1234567890123';

        Livewire::test(ProductScanner::class)
            ->dispatch('onBarcodeDetected', $invalidBarcode)
            ->assertSet('barcode', null)
            ->assertSet('barcodeScanned', false)
            ->assertSet('product', null)
            ->assertSet('cameraError', 'The barcode must start with 505903.');

        // Cannot proceed to scan submission without valid barcode
        expect(Scan::count())->toBe(0);
    });
});

describe('Permission and Authentication', function () {
    test('unauthenticated user cannot access scanner', function () {
        auth()->logout();

        Livewire::test(ProductScanner::class)
            ->assertStatus(401);
    });

    test('user without scanner permission cannot access scanner', function () {
        $userWithoutPermission = User::factory()->create();
        $this->actingAs($userWithoutPermission);

        Livewire::test(ProductScanner::class)
            ->assertStatus(403);
    });

    test('user with scanner permission can access scanner', function () {
        Livewire::test(ProductScanner::class)
            ->assertStatus(200);
    });
});
