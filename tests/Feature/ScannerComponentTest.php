<?php

use App\Livewire\Scanner;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Scanner Component', function () {

    test('it renders successfully', function () {
        Livewire::test(Scanner::class)
            ->assertStatus(200);
    });

    test('it initializes with correct default state', function () {
        Livewire::test(Scanner::class)
            ->assertSet('result', [])
            ->assertSet('isScanning', false)
            ->assertSet('isTorchOn', false)
            ->assertSet('torchSupported', false)
            ->assertSet('loadingCamera', false)
            ->assertSet('cameraError', '')
            ->assertSet('barcode', '');
    });

    describe('Torch State Updates', function () {
        test('updateTorchStatus handles boolean casting correctly', function () {
            // Test with boolean true
            Livewire::test(Scanner::class)
                ->dispatch('torchStatus', true)
                ->assertSet('isTorchOn', true);

            // Test with boolean false
            Livewire::test(Scanner::class)
                ->dispatch('torchStatus', false)
                ->assertSet('isTorchOn', false);

            // Test with string "1"
            Livewire::test(Scanner::class)
                ->dispatch('torchStatus', '1')
                ->assertSet('isTorchOn', true);

            // Test with string "0"
            Livewire::test(Scanner::class)
                ->dispatch('torchStatus', '0')
                ->assertSet('isTorchOn', false);

            // Test with integer 1
            Livewire::test(Scanner::class)
                ->dispatch('torchStatus', 1)
                ->assertSet('isTorchOn', true);

            // Test with integer 0
            Livewire::test(Scanner::class)
                ->dispatch('torchStatus', 0)
                ->assertSet('isTorchOn', false);

            // Test with null
            Livewire::test(Scanner::class)
                ->dispatch('torchStatus', null)
                ->assertSet('isTorchOn', false);
        });

        test('torchStatusUpdated handles boolean casting for both enabled and supported', function () {
            // Test with boolean values
            Livewire::test(Scanner::class)
                ->dispatch('torchStatusUpdated', true, true)
                ->assertSet('isTorchOn', true)
                ->assertSet('torchSupported', true)
                ->assertSet('cameraError', '');

            // Test with string values
            Livewire::test(Scanner::class)
                ->dispatch('torchStatusUpdated', '1', '1')
                ->assertSet('isTorchOn', true)
                ->assertSet('torchSupported', true);

            // Test with mixed values
            Livewire::test(Scanner::class)
                ->dispatch('torchStatusUpdated', 1, false)
                ->assertSet('isTorchOn', true)
                ->assertSet('torchSupported', false)
                ->assertSet('cameraError', 'Torch not supported on this device');

            // Test with not supported
            Livewire::test(Scanner::class)
                ->dispatch('torchStatusUpdated', true, 0)
                ->assertSet('isTorchOn', true)
                ->assertSet('torchSupported', false)
                ->assertSet('cameraError', 'Torch not supported on this device');
        });
    });

    describe('Camera Controls', function () {
        test('camera toggle works correctly', function () {
            $component = Livewire::test(Scanner::class);

            // Toggle on
            $component->dispatch('camera', true)
                ->assertSet('isScanning', true);

            // Toggle off
            $component->dispatch('camera', false)
                ->assertSet('isScanning', false);

            // Toggle without parameter
            $component->dispatch('camera')
                ->assertSet('isScanning', true);

            $component->dispatch('camera')
                ->assertSet('isScanning', false);
        });

        test('updateLoadingCamera works correctly', function () {
            Livewire::test(Scanner::class)
                ->dispatch('loadingCamera', true)
                ->assertSet('loadingCamera', true)
                ->dispatch('loadingCamera', false)
                ->assertSet('loadingCamera', false);
        });
    });

    describe('Barcode Scanning', function () {
        test('updateBarcode processes scan result', function () {
            $result = ['text' => '1234567890123', 'format' => 'EAN_13'];

            Livewire::test(Scanner::class)
                ->dispatch('result', $result)
                ->assertSet('barcode', '1234567890123')
                ->assertDispatched('barcode', '1234567890123');
        });

        test('barcodeScanned event is handled', function () {
            Livewire::test(Scanner::class)
                ->dispatch('barcodeScanned')
                ->assertStatus(200); // Just verify it doesn't error
        });
    });

    describe('Error Handling', function () {
        test('clearError resets camera error', function () {
            Livewire::test(Scanner::class)
                ->set('cameraError', 'Some error')
                ->call('clearError')
                ->assertSet('cameraError', '');
        });
    });
});
