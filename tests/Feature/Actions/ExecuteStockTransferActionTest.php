<?php

use App\Actions\Stock\ExecuteStockTransferAction;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create([
        'sku' => 'TEST-EXECUTE-001',
    ]);

    // Create required permission
    Permission::firstOrCreate(['name' => 'refill bays']);
    $this->user->givePermissionTo('refill bays');

    // Set config values
    Config::set('linnworks.default_location_id', 'main-001');
    Config::set('linnworks.floor_location_id', 'floor-001');
});

it('executes a successful refill operation with auto-selected source', function () {
    // Mock the API calls
    $mockLocations = [
        [
            'id' => 'main-001',
            'name' => 'Main Bay',
            'stock_level' => 25,
        ],
        [
            'id' => 'floor-001',
            'name' => 'Floor Stock',
            'stock_level' => 50,
        ],
    ];

    // Mock GetProductStockLocationsAction
    $this->mock(\App\Actions\Stock\GetProductStockLocationsAction::class)
        ->shouldReceive('run')
        ->with($this->product)
        ->once()
        ->andReturn($mockLocations);

    // Mock LinnworksApiService
    $mockService = Mockery::mock(LinnworksApiService::class);
    $mockService->shouldReceive('transferStockToDefaultLocation')
        ->with('TEST-EXECUTE-001', 'floor-001', 10)
        ->once()
        ->andReturn(['success' => true]);

    $this->app->instance(LinnworksApiService::class, $mockService);

    $result = app(ExecuteStockTransferAction::class)->handle(
        user: $this->user,
        product: $this->product,
        quantity: 10,
        operationType: 'refill',
        autoSelectSource: true
    );

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['message'])->toContain('Successfully transferred 10 units')
        ->and($result['stock_movement'])->toBeInstanceOf(StockMovement::class)
        ->and($result['quantity_transferred'])->toBe(10)
        ->and($result['auto_selected'])->toBeTrue();

    // Verify stock movement was created
    $movement = $result['stock_movement'];
    expect($movement->type)->toBe(StockMovement::TYPE_BAY_REFILL)
        ->and($movement->quantity)->toBe(10)
        ->and($movement->product_id)->toBe($this->product->id)
        ->and($movement->user_id)->toBe($this->user->id);
});

it('executes a manual transfer with specified locations', function () {
    Permission::firstOrCreate(['name' => 'create stock movements']);
    $this->user->givePermissionTo('create stock movements');

    $mockLocations = [
        [
            'id' => 'warehouse-001',
            'name' => 'Warehouse A',
            'stock_level' => 100,
        ],
        [
            'id' => 'shop-001',
            'name' => 'Shop Floor',
            'stock_level' => 20,
        ],
    ];

    $this->mock(\App\Actions\Stock\GetProductStockLocationsAction::class)
        ->shouldReceive('run')
        ->with($this->product)
        ->once()
        ->andReturn($mockLocations);

    // Mock the service for non-default location (should fail for now)
    $mockService = Mockery::mock(LinnworksApiService::class);
    $this->app->instance(LinnworksApiService::class, $mockService);

    expect(function () {
        app(ExecuteStockTransferAction::class)->handle(
            user: $this->user,
            product: $this->product,
            quantity: 15,
            operationType: 'transfer',
            fromLocationId: 'warehouse-001',
            toLocationId: 'shop-001',
            autoSelectSource: false
        );
    })->toThrow(ValidationException::class, 'Generic stock transfers between arbitrary locations not yet implemented');
});

it('throws validation exception when no stock locations found', function () {
    $this->mock(\App\Actions\Stock\GetProductStockLocationsAction::class)
        ->shouldReceive('run')
        ->with($this->product)
        ->once()
        ->andReturn([]);

    expect(function () {
        app(ExecuteStockTransferAction::class)->handle(
            user: $this->user,
            product: $this->product,
            quantity: 10,
            operationType: 'refill'
        );
    })->toThrow(ValidationException::class, 'No stock locations found for this product.');
});

it('throws validation exception when no suitable source location found', function () {
    // Mock locations where all stock is in the target location
    $mockLocations = [
        [
            'id' => 'main-001', // This is the target location
            'name' => 'Main Bay',
            'stock_level' => 25,
        ],
    ];

    $this->mock(\App\Actions\Stock\GetProductStockLocationsAction::class)
        ->shouldReceive('run')
        ->with($this->product)
        ->once()
        ->andReturn($mockLocations);

    expect(function () {
        app(ExecuteStockTransferAction::class)->handle(
            user: $this->user,
            product: $this->product,
            quantity: 10,
            operationType: 'refill',
            autoSelectSource: true
        );
    })->toThrow(ValidationException::class, 'No suitable source location found for refill operation.');
});

it('adjusts quantity when requested exceeds available stock', function () {
    $mockLocations = [
        [
            'id' => 'main-001',
            'name' => 'Main Bay',
            'stock_level' => 25,
        ],
        [
            'id' => 'floor-001',
            'name' => 'Floor Stock',
            'stock_level' => 5, // Less than requested quantity
        ],
    ];

    $this->mock(\App\Actions\Stock\GetProductStockLocationsAction::class)
        ->shouldReceive('run')
        ->with($this->product)
        ->once()
        ->andReturn($mockLocations);

    $mockService = Mockery::mock(LinnworksApiService::class);
    $mockService->shouldReceive('transferStockToDefaultLocation')
        ->with('TEST-EXECUTE-001', 'floor-001', 5) // Adjusted quantity
        ->once()
        ->andReturn(['success' => true]);

    $this->app->instance(LinnworksApiService::class, $mockService);

    $result = app(ExecuteStockTransferAction::class)->handle(
        user: $this->user,
        product: $this->product,
        quantity: 10, // Requested more than available
        operationType: 'refill',
        autoSelectSource: true
    );

    expect($result['quantity_transferred'])->toBe(5); // Adjusted to available stock
});

it('throws validation exception when Linnworks transfer fails', function () {
    $mockLocations = [
        [
            'id' => 'main-001',
            'name' => 'Main Bay',
            'stock_level' => 25,
        ],
        [
            'id' => 'floor-001',
            'name' => 'Floor Stock',
            'stock_level' => 50,
        ],
    ];

    $this->mock(\App\Actions\Stock\GetProductStockLocationsAction::class)
        ->shouldReceive('run')
        ->with($this->product)
        ->once()
        ->andReturn($mockLocations);

    $mockService = Mockery::mock(LinnworksApiService::class);
    $mockService->shouldReceive('transferStockToDefaultLocation')
        ->with('TEST-EXECUTE-001', 'floor-001', 10)
        ->once()
        ->andReturn(['success' => false, 'message' => 'API Error']);

    $this->app->instance(LinnworksApiService::class, $mockService);

    expect(function () {
        app(ExecuteStockTransferAction::class)->handle(
            user: $this->user,
            product: $this->product,
            quantity: 10,
            operationType: 'refill',
            autoSelectSource: true
        );
    })->toThrow(ValidationException::class, 'API Error');
});

it('uses database transaction for consistency', function () {
    $mockLocations = [
        [
            'id' => 'floor-001',
            'name' => 'Floor Stock',
            'stock_level' => 50,
        ],
    ];

    $this->mock(\App\Actions\Stock\GetProductStockLocationsAction::class)
        ->shouldReceive('run')
        ->with($this->product)
        ->once()
        ->andReturn($mockLocations);

    // Mock service to succeed
    $mockService = Mockery::mock(LinnworksApiService::class);
    $mockService->shouldReceive('transferStockToDefaultLocation')
        ->once()
        ->andReturn(['success' => true]);

    $this->app->instance(LinnworksApiService::class, $mockService);

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    ExecuteStockTransferAction::run(
        user: $this->user,
        product: $this->product,
        quantity: 10,
        operationType: 'refill',
        autoSelectSource: true
    );
});

it('throws permission exception for unauthorized user', function () {
    $unauthorizedUser = User::factory()->create();

    expect(function () {
        app(ExecuteStockTransferAction::class)->handle(
            user: $unauthorizedUser,
            product: $this->product,
            quantity: 10,
            operationType: 'refill'
        );
    })->toThrow(ValidationException::class);
});
