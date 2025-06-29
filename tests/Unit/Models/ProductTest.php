<?php

use App\Models\Product;
use App\Models\Scan;

describe('Product Model', function () {

    test('it can be created with factory', function () {
        $product = Product::factory()->create();

        expect($product)->toBeInstanceOf(Product::class);
        expect($product->id)->not->toBeNull();
        expect($product->sku)->not->toBeNull();
        expect($product->name)->not->toBeNull();
        expect($product->barcode)->not->toBeNull();
    });

    test('it has fillable attributes', function () {
        $fillable = [
            'sku',
            'name',
            'barcode',
            'barcode_2',
            'barcode_3',
            'quantity',
        ];

        $product = new Product;
        expect($product->getFillable())->toEqual($fillable);
    });

    test('it has guarded attributes', function () {
        $guarded = [
            'id',
            'created_at',
        ];

        $product = new Product;
        expect($product->getGuarded())->toEqual($guarded);
    });

    test('it can be created with all fillable attributes', function () {
        $data = [
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'barcode' => '1234567890123',
            'barcode_2' => '1234567890124',
            'barcode_3' => '1234567890125',
            'quantity' => 100,
        ];

        $product = Product::create($data);

        expect($product->sku)->toBe('TEST-001');
        expect($product->name)->toBe('Test Product');
        expect($product->barcode)->toBe('1234567890123');
        expect($product->barcode_2)->toBe('1234567890124');
        expect($product->barcode_3)->toBe('1234567890125');
        expect($product->quantity)->toBe(100);
    });

    test('it can have null secondary and tertiary barcodes', function () {
        $product = Product::factory()->create([
            'barcode_2' => null,
            'barcode_3' => null,
        ]);

        expect($product->barcode_2)->toBeNull();
        expect($product->barcode_3)->toBeNull();
    });

    test('it can have null name', function () {
        $product = Product::factory()->create([
            'name' => null,
        ]);

        expect($product->name)->toBeNull();
    });

    test('it has scans relationship', function () {
        $product = Product::factory()->create([
            'barcode' => '1234567890123',
        ]);

        // Create scans with this product's barcode
        Scan::factory()->count(3)->create([
            'barcode' => $product->barcode,
        ]);

        $scans = $product->scans()->get();

        expect($scans)->toHaveCount(3);
        expect($scans->first())->toBeInstanceOf(Scan::class);
    });

    test('scans relationship matches all barcode fields', function () {
        $product = Product::factory()->create([
            'barcode' => '1234567890123',
            'barcode_2' => '2234567890123',
            'barcode_3' => '3234567890123',
        ]);

        // Test that scans can be found by any barcode
        $user = \App\Models\User::factory()->create();

        // Create scans for each barcode type
        $scan1 = Scan::factory()->create([
            'barcode' => $product->barcode,
            'user_id' => $user->id,
        ]);

        $scan2 = Scan::factory()->create([
            'barcode' => $product->barcode_2,
            'user_id' => $user->id,
        ]);

        $scan3 = Scan::factory()->create([
            'barcode' => $product->barcode_3,
            'user_id' => $user->id,
        ]);

        // Create a scan that shouldn't match
        $otherScan = Scan::factory()->create([
            'barcode' => '9999999999999',
            'user_id' => $user->id,
        ]);

        // Get the scans through the relationship
        $productScans = $product->scans()->get();

        expect($productScans)->toHaveCount(3);
        expect($productScans->pluck('id')->sort()->values()->toArray())
            ->toBe([$scan1->id, $scan2->id, $scan3->id]);

        // Verify the other scan is not included
        expect($productScans->pluck('id'))->not->toContain($otherScan->id);
    });

    test('it can be updated', function () {
        $product = Product::factory()->create([
            'name' => 'Original Name',
        ]);

        $product->update(['name' => 'Updated Name']);

        expect($product->fresh()->name)->toBe('Updated Name');
    });

    test('it can be deleted', function () {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        expect(Product::find($productId))->toBeNull();
    });

    test('it has timestamps', function () {
        $product = Product::factory()->create();

        expect($product->created_at)->not->toBeNull();
        expect($product->updated_at)->not->toBeNull();
    });

    test('it updates timestamps on modification', function () {
        $product = Product::factory()->create();
        $originalUpdatedAt = $product->updated_at;

        sleep(1); // Ensure time difference
        $product->update(['name' => 'New Name']);

        expect($product->updated_at)->not->toEqual($originalUpdatedAt);
    });

    test('factory generates valid SKU format', function () {
        $product = Product::factory()->create();

        // Based on factory definition: numberBetween(001,999) . '-' . numberBetween(001,999)
        expect($product->sku)->toMatch('/^\d{1,3}-\d{1,3}$/');
    });

    test('factory generates EAN13 barcode', function () {
        $product = Product::factory()->create();

        // EAN13 should be 13 digits
        expect(strlen($product->barcode))->toBe(13);
        expect($product->barcode)->toMatch('/^\d{13}$/');
    });

    test('it can find products by any barcode field', function () {
        $product = Product::factory()->create([
            'barcode' => '1111111111111',
            'barcode_2' => '2222222222222',
            'barcode_3' => '3333333333333',
        ]);

        $foundByPrimary = Product::where('barcode', '1111111111111')->first();
        $foundBySecondary = Product::where('barcode_2', '2222222222222')->first();
        $foundByTertiary = Product::where('barcode_3', '3333333333333')->first();

        expect($foundByPrimary->id)->toBe($product->id);
        expect($foundBySecondary->id)->toBe($product->id);
        expect($foundByTertiary->id)->toBe($product->id);
    });

    test('it allows duplicate barcodes across different products', function () {
        $product1 = Product::factory()->create([
            'barcode' => '1234567890123',
        ]);

        $product2 = Product::factory()->create([
            'barcode' => '1234567890123',
        ]);

        expect($product1->id)->not->toBe($product2->id);
        expect($product1->barcode)->toBe($product2->barcode);
    });

    test('it allows same barcode in different barcode fields of same product', function () {
        $product = Product::factory()->create([
            'barcode' => '1234567890123',
            'barcode_2' => '1234567890123',
            'barcode_3' => '1234567890123',
        ]);

        expect($product->barcode)->toBe('1234567890123');
        expect($product->barcode_2)->toBe('1234567890123');
        expect($product->barcode_3)->toBe('1234567890123');
    });
});
