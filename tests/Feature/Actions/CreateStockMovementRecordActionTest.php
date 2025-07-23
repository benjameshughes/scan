<?php

use App\Actions\Stock\CreateStockMovementRecordAction;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Test User']);
    $this->product = Product::factory()->create([
        'sku' => 'TEST-MOVEMENT-001',
        'name' => 'Test Product',
    ]);
});

it('creates a stock movement record with all data', function () {
    $movement = app(CreateStockMovementRecordAction::class)->handle(
        product: $this->product,
        user: $this->user,
        quantity: 25,
        type: StockMovement::TYPE_MANUAL_TRANSFER,
        fromLocationId: 'loc-001',
        fromLocationCode: 'FLOOR',
        toLocationId: 'loc-002',
        toLocationCode: 'MAIN',
        notes: 'Test transfer',
        metadata: ['test_key' => 'test_value']
    );

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->product_id)->toBe($this->product->id)
        ->and($movement->user_id)->toBe($this->user->id)
        ->and($movement->quantity)->toBe(25)
        ->and($movement->type)->toBe(StockMovement::TYPE_MANUAL_TRANSFER)
        ->and($movement->from_location_id)->toBe('loc-001')
        ->and($movement->from_location_code)->toBe('FLOOR')
        ->and($movement->to_location_id)->toBe('loc-002')
        ->and($movement->to_location_code)->toBe('MAIN')
        ->and($movement->notes)->toBe('Test transfer')
        ->and($movement->moved_at)->not()->toBeNull();

    // Check metadata includes both provided and default values
    expect($movement->metadata)->toHaveKey('test_key', 'test_value')
        ->and($movement->metadata)->toHaveKey('created_via', 'action_system')
        ->and($movement->metadata)->toHaveKey('user_name', 'Test User')
        ->and($movement->metadata)->toHaveKey('product_sku', 'TEST-MOVEMENT-001')
        ->and($movement->metadata)->toHaveKey('product_name', 'Test Product');
});

it('creates a stock movement record with minimal data', function () {
    $movement = app(CreateStockMovementRecordAction::class)->handle(
        product: $this->product,
        user: $this->user,
        quantity: 10,
        type: StockMovement::TYPE_BAY_REFILL
    );

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->product_id)->toBe($this->product->id)
        ->and($movement->user_id)->toBe($this->user->id)
        ->and($movement->quantity)->toBe(10)
        ->and($movement->type)->toBe(StockMovement::TYPE_BAY_REFILL)
        ->and($movement->from_location_id)->toBeNull()
        ->and($movement->to_location_id)->toBeNull()
        ->and($movement->notes)->toBeNull();
});

it('creates bay refill record with helper method', function () {
    $movement = app(CreateStockMovementRecordAction::class)->createBayRefillRecord(
        product: $this->product,
        user: $this->user,
        quantity: 15,
        fromLocationCode: 'FLOOR',
        toLocationCode: 'MAIN',
        fromLocationId: 'floor-001',
        toLocationId: 'main-001',
        additionalMetadata: ['auto_selected' => true]
    );

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->type)->toBe(StockMovement::TYPE_BAY_REFILL)
        ->and($movement->quantity)->toBe(15)
        ->and($movement->from_location_code)->toBe('FLOOR')
        ->and($movement->to_location_code)->toBe('MAIN')
        ->and($movement->from_location_id)->toBe('floor-001')
        ->and($movement->to_location_id)->toBe('main-001')
        ->and($movement->notes)->toBe('Bay refill from FLOOR to MAIN');

    // Check specific metadata for bay refill
    expect($movement->metadata)->toHaveKey('refill_operation', true)
        ->and($movement->metadata)->toHaveKey('auto_selected', true);
});

it('creates manual transfer record with helper method', function () {
    $movement = app(CreateStockMovementRecordAction::class)->createManualTransferRecord(
        product: $this->product,
        user: $this->user,
        quantity: 20,
        fromLocationCode: 'WAREHOUSE',
        toLocationCode: 'SHOP',
        fromLocationId: 'wh-001',
        toLocationId: 'shop-001',
        notes: 'Manual restocking',
        additionalMetadata: ['priority' => 'high']
    );

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->type)->toBe(StockMovement::TYPE_MANUAL_TRANSFER)
        ->and($movement->quantity)->toBe(20)
        ->and($movement->from_location_code)->toBe('WAREHOUSE')
        ->and($movement->to_location_code)->toBe('SHOP')
        ->and($movement->notes)->toBe('Manual restocking');

    // Check specific metadata for manual transfer
    expect($movement->metadata)->toHaveKey('manual_operation', true)
        ->and($movement->metadata)->toHaveKey('priority', 'high');
});

it('uses default notes for manual transfer when none provided', function () {
    $movement = app(CreateStockMovementRecordAction::class)->createManualTransferRecord(
        product: $this->product,
        user: $this->user,
        quantity: 20,
        fromLocationCode: 'WAREHOUSE',
        toLocationCode: 'SHOP'
    );

    expect($movement->notes)->toBe('Manual transfer from WAREHOUSE to SHOP');
});

it('persists movement to database', function () {
    $movement = app(CreateStockMovementRecordAction::class)->handle(
        product: $this->product,
        user: $this->user,
        quantity: 25,
        type: StockMovement::TYPE_MANUAL_TRANSFER
    );

    // Verify it's in the database
    $this->assertDatabaseHas('stock_movements', [
        'id' => $movement->id,
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'quantity' => 25,
        'type' => StockMovement::TYPE_MANUAL_TRANSFER,
    ]);
});

it('throws exception when database save fails', function () {
    // Create a product with invalid data to force database error
    $invalidProduct = new Product();
    $invalidProduct->id = 999999; // Non-existent ID

    expect(function () {
        app(CreateStockMovementRecordAction::class)->handle(
            product: $invalidProduct,
            user: $this->user,
            quantity: 25,
            type: StockMovement::TYPE_MANUAL_TRANSFER
        );
    })->toThrow(Exception::class);
});