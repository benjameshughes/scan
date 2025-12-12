<?php

use App\Actions\Scanner\LookupProductByBarcodeAction;
use App\Models\Product;

beforeEach(function () {
    $this->action = app(LookupProductByBarcodeAction::class);
});

it('returns product when barcode matches primary barcode field', function () {
    $product = Product::factory()->create([
        'barcode' => 505903123456,
        'barcode_2' => null,
        'barcode_3' => null,
    ]);

    $result = $this->action->handle('505903123456');

    expect($result)->toBeInstanceOf(Product::class)
        ->and($result->id)->toBe($product->id)
        ->and($result->barcode)->toBe(505903123456);
});

it('returns product when barcode matches barcode_2 field', function () {
    $product = Product::factory()->create([
        'barcode' => 505903111111,
        'barcode_2' => 505903222222,
        'barcode_3' => null,
    ]);

    $result = $this->action->handle('505903222222');

    expect($result)->toBeInstanceOf(Product::class)
        ->and($result->id)->toBe($product->id)
        ->and($result->barcode_2)->toBe(505903222222);
});

it('returns product when barcode matches barcode_3 field', function () {
    $product = Product::factory()->create([
        'barcode' => 505903111111,
        'barcode_2' => 505903222222,
        'barcode_3' => 505903333333,
    ]);

    $result = $this->action->handle('505903333333');

    expect($result)->toBeInstanceOf(Product::class)
        ->and($result->id)->toBe($product->id)
        ->and($result->barcode_3)->toBe(505903333333);
});

it('returns null when barcode does not match any product', function () {
    Product::factory()->create([
        'barcode' => '505903111111',
    ]);

    $result = $this->action->handle('505903999999');

    expect($result)->toBeNull();
});

it('returns null when barcode is empty string', function () {
    Product::factory()->create([
        'barcode' => '505903111111',
    ]);

    $result = $this->action->handle('');

    expect($result)->toBeNull();
});

it('returns first matching product when multiple products have same barcode', function () {
    $product1 = Product::factory()->create([
        'barcode' => '505903123456',
    ]);

    $product2 = Product::factory()->create([
        'barcode' => '505903123456',
    ]);

    $result = $this->action->handle('505903123456');

    expect($result)->toBeInstanceOf(Product::class)
        ->and($result->id)->toBe($product1->id);
});

it('exists returns true when product is found', function () {
    Product::factory()->create([
        'barcode' => '505903123456',
    ]);

    $exists = $this->action->exists('505903123456');

    expect($exists)->toBeTrue();
});

it('exists returns false when product is not found', function () {
    Product::factory()->create([
        'barcode' => '505903111111',
    ]);

    $exists = $this->action->exists('505903999999');

    expect($exists)->toBeFalse();
});

it('getProductId returns product ID when found', function () {
    $product = Product::factory()->create([
        'barcode' => '505903123456',
    ]);

    $productId = $this->action->getProductId('505903123456');

    expect($productId)->toBe($product->id);
});

it('getProductId returns null when product not found', function () {
    Product::factory()->create([
        'barcode' => '505903111111',
    ]);

    $productId = $this->action->getProductId('505903999999');

    expect($productId)->toBeNull();
});

it('handleBatch returns array of products for matching barcodes', function () {
    $product1 = Product::factory()->create([
        'barcode' => '505903111111',
    ]);

    $product2 = Product::factory()->create([
        'barcode' => '505903222222',
    ]);

    $product3 = Product::factory()->create([
        'barcode' => '505903333333',
    ]);

    $results = $this->action->handleBatch([
        '505903111111',
        '505903222222',
        '505903999999', // Not found
    ]);

    expect($results)->toBeArray()
        ->toHaveCount(2)
        ->and($results['505903111111']->id)->toBe($product1->id)
        ->and($results['505903222222']->id)->toBe($product2->id)
        ->and($results)->not->toHaveKey('505903999999');
});

it('handleBatch returns empty array when no barcodes match', function () {
    Product::factory()->create([
        'barcode' => '505903111111',
    ]);

    $results = $this->action->handleBatch([
        '505903999999',
        '505903888888',
    ]);

    expect($results)->toBeArray()
        ->toBeEmpty();
});

it('handleBatch returns empty array when given empty array', function () {
    $results = $this->action->handleBatch([]);

    expect($results)->toBeArray()
        ->toBeEmpty();
});
