<?php

use App\Jobs\EmptyBayJob;
use App\Jobs\SyncBarcode;
use App\Livewire\ProductScanner;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    // Create admin role and permissions
    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    $viewScannerPermission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view scanner']);
    $refillBaysPermission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'refill bays']);

    $adminRole->givePermissionTo([$viewScannerPermission, $refillBaysPermission]);

    // Create admin user with proper permissions
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

describe('ProductScanner Component', function () {

    test('it renders successfully', function () {
        Livewire::test(ProductScanner::class)
            ->assertStatus(200);
    });

    test('it initializes with correct default state', function () {
        Livewire::test(ProductScanner::class)
            ->assertSet('isScanning', false)
            ->assertSet('isTorchOn', false)
            ->assertSet('torchSupported', false)
            ->assertSet('loadingCamera', false)
            ->assertSet('cameraError', '')
            ->assertSet('barcode', null)
            ->assertSet('quantity', 1)
            ->assertSet('barcodeScanned', false)
            ->assertSet('showSuccessMessage', false)
            ->assertSet('successMessage', '')
            ->assertSet('scanAction', false)
            ->assertSet('product', null);
    });

    describe('Camera Controls', function () {
        test('toggle camera switches scanning state', function () {
            Livewire::test(ProductScanner::class)
                ->call('toggleCamera')
                ->assertSet('isScanning', true)
                ->assertDispatched('camera-state-changed', true)
                ->call('toggleCamera')
                ->assertSet('isScanning', false)
                ->assertDispatched('camera-state-changed', false);
        });

        test('toggle camera turns off torch when camera stops', function () {
            Livewire::test(ProductScanner::class)
                ->set('isScanning', true)  // Start with camera on
                ->set('isTorchOn', true)
                ->call('toggleCamera')  // This will turn camera off
                ->assertSet('isTorchOn', false);
        });

        test('toggle torch works when supported', function () {
            Livewire::test(ProductScanner::class)
                ->set('torchSupported', true)
                ->call('toggleTorch')
                ->assertSet('isTorchOn', true)
                ->assertDispatched('torch-state-changed', true);
        });

        test('toggle torch shows error when not supported', function () {
            Livewire::test(ProductScanner::class)
                ->set('torchSupported', false)
                ->call('toggleTorch')
                ->assertSet('isTorchOn', false)
                ->assertSet('cameraError', 'Torch not supported on this device');
        });
    });

    describe('Barcode Validation', function () {
        test('valid barcode with correct prefix passes validation', function () {
            $validBarcode = 5059031234567;

            Livewire::test(ProductScanner::class)
                ->set('barcode', $validBarcode)
                ->assertHasNoErrors('barcode');
        });

        test('invalid barcode with wrong prefix fails validation', function () {
            $invalidBarcode = 1234567890123;

            Livewire::test(ProductScanner::class)
                ->set('barcode', $invalidBarcode)
                ->call('save')
                ->assertHasErrors('barcode');
        });

        test('empty barcode fails validation on save', function () {
            Livewire::test(ProductScanner::class)
                ->set('barcode', null)
                ->call('save')
                ->assertHasErrors('barcode');
        });
    });

    describe('Product Matching', function () {
        test('barcode that matches existing product shows product name', function () {
            $product = Product::factory()->create([
                'name' => 'Test Product',
                'barcode' => '5059031234567',
            ]);

            Livewire::test(ProductScanner::class)
                ->set('barcode', $product->barcode)
                ->assertSet('product.name', 'Test Product')
                ->assertSet('barcodeScanned', true)
                ->assertSet('showSuccessMessage', false); // Success message shows different behavior now
        });

        test('barcode that matches product with null name shows product without success message', function () {
            $product = Product::factory()->create([
                'name' => null,
                'barcode' => '5059031234567',
            ]);

            Livewire::test(ProductScanner::class)
                ->set('barcode', $product->barcode)
                ->assertSet('product.id', $product->id)
                ->assertSet('barcodeScanned', true)
                ->assertSet('showSuccessMessage', false); // Product found = no success message
        });

        test('valid barcode with no matching product shows not found message', function () {
            $validBarcodeNotInDb = '5059039999999';

            Livewire::test(ProductScanner::class)
                ->set('barcode', $validBarcodeNotInDb)
                ->assertSet('product', null)
                ->assertSet('successMessage', 'No Product Found With That Barcode - You can still submit the scan')
                ->assertSet('showSuccessMessage', true);
        });

        test('product matching works with secondary barcodes', function () {
            $product = Product::factory()->create([
                'name' => 'Test Product 2',
                'barcode' => '1111111111111',
                'barcode_2' => '5059031234567',
                'barcode_3' => null,
            ]);

            Livewire::test(ProductScanner::class)
                ->set('barcode', '5059031234567')
                ->assertSet('product.name', 'Test Product 2')
                ->assertSet('barcodeScanned', true)
                ->assertSet('showSuccessMessage', false); // Product found = no success message
        });

        test('product matching works with tertiary barcodes', function () {
            $product = Product::factory()->create([
                'name' => 'Test Product 3',
                'barcode' => '1111111111111',
                'barcode_2' => '2222222222222',
                'barcode_3' => '5059031234567',
            ]);

            Livewire::test(ProductScanner::class)
                ->set('barcode', '5059031234567')
                ->assertSet('product.name', 'Test Product 3')
                ->assertSet('barcodeScanned', true)
                ->assertSet('showSuccessMessage', false); // Product found = no success message
        });
    });

    describe('Quantity Management', function () {
        test('increment quantity increases by one', function () {
            Livewire::test(ProductScanner::class)
                ->set('quantity', 5)
                ->call('incrementQuantity')
                ->assertSet('quantity', 6);
        });

        test('decrement quantity decreases by one', function () {
            Livewire::test(ProductScanner::class)
                ->set('quantity', 5)
                ->call('decrementQuantity')
                ->assertSet('quantity', 4);
        });

        test('decrement quantity does not go below 1', function () {
            Livewire::test(ProductScanner::class)
                ->set('quantity', 1)
                ->call('decrementQuantity')
                ->assertSet('quantity', 1);
        });

        test('quantity validation requires minimum of 1', function () {
            Queue::fake();

            $product = Product::factory()->create([
                'barcode' => '5059031234567',
            ]);

            // The validation should fail for quantity of 0
            Livewire::test(ProductScanner::class)
                ->set('barcode', 5059031234567)
                ->set('quantity', 0)
                ->call('save')
                ->assertHasErrors(['quantity' => 'min']);

            // No job should be dispatched because validation failed
            Queue::assertNothingPushed();
        });
    });

    describe('Scan Saving', function () {
        test('valid scan saves successfully', function () {
            Queue::fake();

            $product = Product::factory()->create([
                'barcode' => '5059031234567',
            ]);

            $barcodeValue = 5059031234567;

            Livewire::test(ProductScanner::class)
                ->set('barcode', $barcodeValue)
                ->set('quantity', 5)
                ->call('save')
                ->assertHasNoErrors();

            expect(Scan::count())->toBe(1);
            $scan = Scan::first();
            expect($scan->barcode)->toBe($barcodeValue);
            expect($scan->quantity)->toBe(5);
            expect($scan->user_id)->toBe($this->user->id);
            expect($scan->submitted)->toBe(0);
            expect($scan->sync_status)->toBe('pending');

            // Assert the job was dispatched
            Queue::assertPushed(SyncBarcode::class, function ($job) use ($scan) {
                return $job->scan->id === $scan->id;
            });
        });

        test('save resets form and resumes scanning', function () {
            Queue::fake();

            $product = Product::factory()->create([
                'barcode' => '5059031234567',
            ]);

            Livewire::test(ProductScanner::class)
                ->set('barcode', $product->barcode)
                ->set('quantity', 5)
                ->call('save')
                ->assertSet('barcode', null)
                ->assertSet('quantity', 1)
                ->assertSet('barcodeScanned', false)
                ->assertSet('product', null)
                ->assertSet('isScanning', true)
                ->assertSet('successMessage', 'Scan saved successfully! Ready for next item.')
                ->assertDispatched('camera-state-changed', true);

            Queue::assertPushed(SyncBarcode::class);
        });

        test('save creates scan with authenticated user', function () {
            Queue::fake();

            $product = Product::factory()->create([
                'barcode' => '5059031234567',
            ]);

            Livewire::test(ProductScanner::class)
                ->set('barcode', $product->barcode)
                ->set('quantity', 1)
                ->call('save');

            $scan = Scan::first();
            expect($scan->user_id)->toBe($this->user->id);

            Queue::assertPushed(SyncBarcode::class);
        });
    });

    describe('Form Reset Functions', function () {
        test('reset scan clears all scan data', function () {
            $product = Product::factory()->create([
                'barcode' => '5059031234567',
            ]);

            Livewire::test(ProductScanner::class)
                ->set('barcode', $product->barcode)
                ->set('quantity', 5)
                ->set('barcodeScanned', true)
                ->set('showSuccessMessage', true)
                ->set('successMessage', 'Test message')
                ->set('product', $product)
                ->call('resetScan')
                ->assertSet('barcode', null)
                ->assertSet('quantity', 1)
                ->assertSet('barcodeScanned', false)
                ->assertSet('showSuccessMessage', false)
                ->assertSet('successMessage', '')
                ->assertSet('product', null)
                ->assertSet('cameraError', '');
        });

        test('start new scan resets and resumes scanning', function () {
            Livewire::test(ProductScanner::class)
                ->set('barcode', '5059031234567')
                ->set('isScanning', false)
                ->call('startNewScan')
                ->assertSet('barcode', null)
                ->assertSet('isScanning', true)
                ->assertDispatched('camera-state-changed', true);
        });
    });

    describe('Empty Bay Notification', function () {
        test('empty bay notification updates success message', function () {
            Queue::fake();

            Livewire::test(ProductScanner::class)
                ->set('barcode', '5059031234567')
                ->call('emptyBayNotification')
                ->assertSet('showSuccessMessage', true)
                ->assertSet('successMessage', 'Empty bay notification sent');

            Queue::assertPushed(EmptyBayJob::class);
        });
    });

    describe('Event Handlers', function () {
        test('camera ready event updates state', function () {
            Livewire::test(ProductScanner::class)
                ->set('loadingCamera', true)
                ->dispatch('onCameraReady')
                ->assertSet('loadingCamera', false)
                ->assertSet('isScanning', true)
                ->assertSet('cameraError', '');
        });

        test('camera error event updates state', function () {
            $errorMessage = 'Camera access denied';

            Livewire::test(ProductScanner::class)
                ->set('loadingCamera', true)
                ->set('isScanning', true)
                ->dispatch('onCameraError', $errorMessage)
                ->assertSet('loadingCamera', false)
                ->assertSet('isScanning', false)
                ->assertSet('cameraError', $errorMessage);
        });

        test('torch support detected event updates state', function () {
            Livewire::test(ProductScanner::class)
                ->dispatch('onTorchSupportDetected', true)
                ->assertSet('torchSupported', true);
        });

        test('torch support not detected turns off torch', function () {
            Livewire::test(ProductScanner::class)
                ->set('isTorchOn', true)
                ->dispatch('onTorchSupportDetected', false)
                ->assertSet('torchSupported', false)
                ->assertSet('isTorchOn', false);
        });

        test('torch state changed event updates state', function () {
            Livewire::test(ProductScanner::class)
                ->dispatch('onTorchStateChanged', true)
                ->assertSet('isTorchOn', true);
        });

        test('torch state changed event handles various input types with boolean casting', function () {
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

            // Test with null
            Livewire::test(ProductScanner::class)
                ->dispatch('onTorchStateChanged', null)
                ->assertSet('isTorchOn', false);
        });

        test('barcode detected event processes barcode', function () {
            $product = Product::factory()->create([
                'name' => 'Detected Product',
                'barcode' => '5059031234567',
            ]);

            Livewire::test(ProductScanner::class)
                ->dispatch('onBarcodeDetected', $product->barcode)
                ->assertSet('barcode', $product->barcode)
                ->assertSet('barcodeScanned', true)
                ->assertSet('isScanning', false)
                ->assertSet('cameraError', '')
                ->assertSet('product.name', 'Detected Product')
                ->assertSet('showSuccessMessage', false); // Product found = no success message
        });
    });

    describe('Error Handling', function () {
        test('clear error resets camera error', function () {
            Livewire::test(ProductScanner::class)
                ->set('cameraError', 'Some error')
                ->call('clearError')
                ->assertSet('cameraError', '');
        });
    });
});
