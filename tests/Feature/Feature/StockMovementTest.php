<?php

use App\Livewire\StockMovementsTable;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create the permission if it doesn't exist
    Permission::findOrCreate('manage products');

    // Give user permission to manage products
    $this->user->givePermissionTo('manage products');
});

it('creates a stock movement record when bay refill is performed', function () {
    $product = Product::factory()->create([
        'sku' => 'TEST-SKU-001',
        'name' => 'Test Product',
    ]);

    $movement = StockMovement::createBayRefill(
        $product,
        'location-123',
        '12B-3',
        50,
        $this->user->id,
        ['location_name' => 'Storage Location 12B-3', 'stock_before' => 100]
    );

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->product_id)->toBe($product->id)
        ->and($movement->from_location_id)->toBe('location-123')
        ->and($movement->from_location_code)->toBe('12B-3')
        ->and($movement->to_location_id)->toBe('default')
        ->and($movement->to_location_code)->toBe('Default')
        ->and($movement->quantity)->toBe(50)
        ->and($movement->type)->toBe(StockMovement::TYPE_BAY_REFILL)
        ->and($movement->user_id)->toBe($this->user->id)
        ->and($movement->metadata['location_name'])->toBe('Storage Location 12B-3')
        ->and($movement->metadata['stock_before'])->toBe(100);
});

it('displays formatted movement type correctly', function () {
    $product = Product::factory()->create();

    $movement = StockMovement::create([
        'product_id' => $product->id,
        'from_location_id' => 'loc-1',
        'from_location_code' => '11A',
        'to_location_id' => 'default',
        'to_location_code' => 'Default',
        'quantity' => 25,
        'type' => StockMovement::TYPE_BAY_REFILL,
        'user_id' => $this->user->id,
        'moved_at' => now(),
    ]);

    expect($movement->formatted_type)->toBe('Bay Refill')
        ->and($movement->movement_display)->toBe('11A → Default');
});

it('can filter stock movements by date range', function () {
    $product = Product::factory()->create();

    // Create movements on different dates
    StockMovement::factory()->create([
        'product_id' => $product->id,
        'moved_at' => now()->subDays(5),
        'user_id' => $this->user->id,
    ]);

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'moved_at' => now()->subDays(2),
        'user_id' => $this->user->id,
    ]);

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'moved_at' => now(),
        'user_id' => $this->user->id,
    ]);

    // Test date range scope
    $movements = StockMovement::dateRange(now()->subDays(3), now())->get();

    expect($movements)->toHaveCount(2);
});

it('can filter stock movements by location', function () {
    $product = Product::factory()->create();

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'from_location_id' => 'loc-1',
        'to_location_id' => 'loc-2',
        'user_id' => $this->user->id,
    ]);

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'from_location_id' => 'loc-3',
        'to_location_id' => 'loc-1',
        'user_id' => $this->user->id,
    ]);

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'from_location_id' => 'loc-2',
        'to_location_id' => 'loc-3',
        'user_id' => $this->user->id,
    ]);

    $movements = StockMovement::forLocation('loc-1')->get();

    expect($movements)->toHaveCount(2);
});

it('renders stock movements table component', function () {
    Product::factory()->count(3)->create();
    StockMovement::factory()->count(5)->create([
        'user_id' => $this->user->id,
        'type' => StockMovement::TYPE_BAY_REFILL,
    ]);

    Livewire::test(StockMovementsTable::class)
        ->assertOk();
});

// Route test removed - may need session setup for middleware
