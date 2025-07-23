<?php

use App\Actions\Stock\GetProductStockLocationsAction;
use App\Models\Product;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->product = Product::factory()->create([
        'sku' => 'TEST-STOCK-001',
    ]);
});

it('can fetch and format stock locations from Linnworks', function () {
    // Mock the Linnworks API response
    $mockLocations = [
        [
            'Location' => [
                'StockLocationId' => 'loc-001',
                'LocationName' => 'Main Bay',
            ],
            'StockLevel' => 50,
            'Available' => 45,
            'Allocated' => 5,
            'OnOrder' => 10,
            'MinimumLevel' => 5,
        ],
        [
            'Location' => [
                'StockLocationId' => 'loc-002',
                'LocationName' => 'Floor Stock',
            ],
            'StockLevel' => 100,
            'Available' => 95,
            'Allocated' => 5,
            'OnOrder' => 0,
            'MinimumLevel' => 10,
        ],
    ];

    $mockService = Mockery::mock(LinnworksApiService::class);
    $mockService->shouldReceive('getStockLocationsByProduct')
        ->with('TEST-STOCK-001')
        ->once()
        ->andReturn($mockLocations);

    $this->app->instance(LinnworksApiService::class, $mockService);

    $result = app(GetProductStockLocationsAction::class)->handle($this->product);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result[0])->toMatchArray([
            'id' => 'loc-001',
            'name' => 'Main Bay',
            'stock_level' => 50,
            'available' => 45,
            'allocated' => 5,
            'on_order' => 10,
            'minimum_level' => 5,
        ])
        ->and($result[1])->toMatchArray([
            'id' => 'loc-002',
            'name' => 'Floor Stock',
            'stock_level' => 100,
            'available' => 95,
            'allocated' => 5,
            'on_order' => 0,
            'minimum_level' => 10,
        ]);
});

it('returns empty array when Linnworks API fails', function () {
    $mockService = Mockery::mock(LinnworksApiService::class);
    $mockService->shouldReceive('getStockLocationsByProduct')
        ->with('TEST-STOCK-001')
        ->once()
        ->andThrow(new Exception('API Error'));

    $this->app->instance(LinnworksApiService::class, $mockService);

    Log::shouldReceive('channel')->with('inventory')->andReturnSelf();
    Log::shouldReceive('error')->once();

    $result = app(GetProductStockLocationsAction::class)->handle($this->product);

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('handles missing location data gracefully', function () {
    // Mock response with missing/null data
    $mockLocations = [
        [
            'Location' => [
                'StockLocationId' => null,
                'LocationName' => null,
            ],
            'StockLevel' => null,
            'Available' => null,
        ],
        [
            // Missing Location key entirely
            'StockLevel' => 25,
        ],
    ];

    $mockService = Mockery::mock(LinnworksApiService::class);
    $mockService->shouldReceive('getStockLocationsByProduct')
        ->with('TEST-STOCK-001')
        ->once()
        ->andReturn($mockLocations);

    $this->app->instance(LinnworksApiService::class, $mockService);

    $result = app(GetProductStockLocationsAction::class)->handle($this->product);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result[0])->toMatchArray([
            'id' => null,
            'name' => '',
            'stock_level' => 0,
            'available' => 0,
        ])
        ->and($result[1])->toMatchArray([
            'id' => null,
            'name' => '',
            'stock_level' => 25,
            'available' => 0,
        ]);
});