<?php

use App\Actions\Stock\ValidateStockTransferAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create([
        'sku' => 'TEST-VALIDATE-001',
    ]);
});

it('validates successful stock transfer', function () {
    // Create permission and assign to user
    Permission::firstOrCreate(['name' => 'refill bays']);
    $this->user->givePermissionTo('refill bays');

    $result = app(ValidateStockTransferAction::class)->handle(
        $this->user,
        $this->product,
        5,
        'loc-001',
        10,
        'refill'
    );

    expect($result)->toBeArray()
        ->and($result['valid'])->toBeTrue()
        ->and($result['user'])->toBe($this->user)
        ->and($result['product'])->toBe($this->product)
        ->and($result['quantity'])->toBe(5)
        ->and($result['from_location_id'])->toBe('loc-001')
        ->and($result['available_stock'])->toBe(10);
});

it('throws validation exception for insufficient permissions', function () {
    expect(function () {
        app(ValidateStockTransferAction::class)->handle(
            $this->user,
            $this->product,
            5,
            'loc-001',
            10,
            'refill'
        );
    })->toThrow(ValidationException::class);
});

it('validates different operation types require correct permissions', function () {
    Permission::firstOrCreate(['name' => 'create stock movements']);
    $this->user->givePermissionTo('create stock movements');

    // Should work for transfer operation
    $result = app(ValidateStockTransferAction::class)->handle(
        $this->user,
        $this->product,
        5,
        'loc-001',
        10,
        'transfer'
    );

    expect($result['valid'])->toBeTrue();

    // Should fail for refill operation (needs different permission)
    expect(function () {
        app(ValidateStockTransferAction::class)->handle(
            $this->user,
            $this->product,
            5,
            'loc-001',
            10,
            'refill'
        );
    })->toThrow(ValidationException::class);
});

it('throws validation exception for quantity less than 1', function () {
    Permission::firstOrCreate(['name' => 'refill bays']);
    $this->user->givePermissionTo('refill bays');

    expect(function () {
        app(ValidateStockTransferAction::class)->handle(
            $this->user,
            $this->product,
            0, // Invalid quantity
            'loc-001',
            10,
            'refill'
        );
    })->toThrow(ValidationException::class);
});

it('throws validation exception when quantity exceeds available stock', function () {
    Permission::firstOrCreate(['name' => 'refill bays']);
    $this->user->givePermissionTo('refill bays');

    expect(function () {
        app(ValidateStockTransferAction::class)->handle(
            $this->user,
            $this->product,
            15, // More than available stock
            'loc-001',
            10, // Available stock
            'refill'
        );
    })->toThrow(ValidationException::class);
});

it('validates successfully when no available stock limit is provided', function () {
    Permission::firstOrCreate(['name' => 'refill bays']);
    $this->user->givePermissionTo('refill bays');

    $result = app(ValidateStockTransferAction::class)->handle(
        $this->user,
        $this->product,
        15,
        'loc-001',
        null, // No stock limit
        'refill'
    );

    expect($result['valid'])->toBeTrue();
});

it('throws validation exception for product without SKU', function () {
    Permission::firstOrCreate(['name' => 'refill bays']);
    $this->user->givePermissionTo('refill bays');
    
    $invalidProduct = Product::factory()->create(['sku' => '']);

    expect(function () {
        app(ValidateStockTransferAction::class)->handle(
            $this->user,
            $invalidProduct,
            5,
            'loc-001',
            10,
            'refill'
        );
    })->toThrow(ValidationException::class);
});

it('validates successfully with null location ID', function () {
    Permission::firstOrCreate(['name' => 'refill bays']);
    $this->user->givePermissionTo('refill bays');

    $result = app(ValidateStockTransferAction::class)->handle(
        $this->user,
        $this->product,
        5,
        null, // Null location ID
        10,
        'refill'
    );

    expect($result['valid'])->toBeTrue();
});