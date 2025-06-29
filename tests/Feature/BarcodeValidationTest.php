<?php

namespace Tests\Feature;

use App\Livewire\ProductScanner;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Barcode Validation', function () {

    test('no validation errors when barcode is null or empty', function () {
        Queue::fake();

        $component = Livewire::test(ProductScanner::class)
            ->set('barcode', null);

        // Should not have any validation errors for null barcode
        $component->assertHasNoErrors();

        // Should reset UI state when barcode is cleared
        $component
            ->assertSet('barcodeScanned', false)
            ->assertSet('product', null)
            ->assertSet('showSuccessMessage', false)
            ->assertSet('successMessage', '');
    });

    test('validation errors only appear when trying to save with empty barcode', function () {
        Queue::fake();

        $component = Livewire::test(ProductScanner::class)
            ->set('barcode', null)
            ->set('quantity', 5);

        // No errors while barcode is null
        $component->assertHasNoErrors();

        // But trying to save should trigger validation error
        $component->call('save')
            ->assertHasErrors(['barcode' => 'required']);
    });

    test('invalid barcode shows validation error on input', function () {
        Queue::fake();

        $component = Livewire::test(ProductScanner::class)
            ->set('barcode', 1234567890123); // Invalid prefix

        // Should have validation error for invalid barcode
        $component->assertHasErrors('barcode');

        // UI state should reflect validation failure
        $component
            ->assertSet('product', null)
            ->assertSet('showSuccessMessage', false)
            ->assertSet('successMessage', '');
    });

    test('valid barcode processes successfully', function () {
        Queue::fake();

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'barcode' => '5059031234567',
        ]);

        $component = Livewire::test(ProductScanner::class)
            ->set('barcode', 5059031234567);

        // Should not have validation errors
        $component->assertHasNoErrors();

        // Should set product and success state
        $component
            ->assertSet('barcodeScanned', true)
            ->assertSet('product.name', 'Test Product')
            ->assertSet('showSuccessMessage', true)
            ->assertSet('successMessage', 'Test Product');
    });

    test('clearing barcode after valid input resets state', function () {
        Queue::fake();

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'barcode' => '5059031234567',
        ]);

        $component = Livewire::test(ProductScanner::class)
            ->set('barcode', 5059031234567); // Set valid barcode first

        // Verify initial state
        $component
            ->assertSet('barcodeScanned', true)
            ->assertSet('showSuccessMessage', true);

        // Now clear the barcode
        $component->set('barcode', null);

        // Should reset all scan-related state
        $component
            ->assertHasNoErrors()
            ->assertSet('barcodeScanned', false)
            ->assertSet('product', null)
            ->assertSet('showSuccessMessage', false)
            ->assertSet('successMessage', '');
    });

    test('can save with valid barcode after clearing and re-entering', function () {
        Queue::fake();

        $product = Product::factory()->create([
            'barcode' => '5059031234567',
        ]);

        $component = Livewire::test(ProductScanner::class);

        // Enter invalid barcode first
        $component->set('barcode', 1234567890123);
        $component->assertHasErrors('barcode');

        // Clear the barcode (should reset validation)
        $component->set('barcode', null);
        $component->assertHasNoErrors();

        // Enter valid barcode
        $component->set('barcode', 5059031234567);
        $component->assertHasNoErrors();

        // Should be able to save successfully
        $component
            ->set('quantity', 3)
            ->call('save')
            ->assertHasNoErrors();
    });

    test('validation message shows for invalid prefix', function () {
        Queue::fake();

        $component = Livewire::test(ProductScanner::class)
            ->set('barcode', 1234567890123); // Invalid prefix (should start with 505903)

        $component->assertHasErrors(['barcode']);

        // The error should be about the prefix validation
        $errors = $component->errors();
        expect($errors->first('barcode'))->toContain('must start with 505903');
    });
});
