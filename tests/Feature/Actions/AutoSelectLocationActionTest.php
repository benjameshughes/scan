<?php

use App\Actions\Stock\AutoSelectLocationAction;

beforeEach(function () {
    $this->mockLocations = [
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
        [
            'id' => 'warehouse-001',
            'name' => 'Warehouse A',
            'stock_level' => 100,
        ],
    ];
});

it('returns null when no locations are available', function () {
    $result = app(AutoSelectLocationAction::class)->handle(
        [],
        'main-001',
        'floor-001',
        1
    );

    expect($result)->toBeNull();
});

it('auto-selects preferred location when available with sufficient stock', function () {
    $result = app(AutoSelectLocationAction::class)->handle(
        $this->mockLocations,
        'main-001',
        'floor-001', // Preferred location
        10
    );

    expect($result)->toBeArray()
        ->and($result['id'])->toBe('floor-001')
        ->and($result['name'])->toBe('Floor Stock')
        ->and($result['stock_level'])->toBe(50);
});

it('skips preferred location when it has insufficient stock', function () {
    $locationsWithLowFloorStock = [
        [
            'id' => 'main-001',
            'name' => 'Main Bay',
            'stock_level' => 25,
        ],
        [
            'id' => 'floor-001',
            'name' => 'Floor Stock',
            'stock_level' => 2, // Insufficient for requirement of 10
        ],
        [
            'id' => 'warehouse-001',
            'name' => 'Warehouse A',
            'stock_level' => 100,
        ],
    ];

    $result = app(AutoSelectLocationAction::class)->handle(
        $locationsWithLowFloorStock,
        'main-001',
        'floor-001',
        10 // Minimum required
    );

    // Should skip floor and select warehouse (highest stock)
    expect($result)->toBeArray()
        ->and($result['id'])->toBe('warehouse-001')
        ->and($result['name'])->toBe('Warehouse A');
});

it('auto-selects single available location when only one option exists', function () {
    $singleLocation = [
        [
            'id' => 'warehouse-001',
            'name' => 'Warehouse A',
            'stock_level' => 100,
        ],
    ];

    $result = app(AutoSelectLocationAction::class)->handle(
        $singleLocation,
        'main-001',
        'floor-001',
        10
    );

    expect($result)->toBeArray()
        ->and($result['id'])->toBe('warehouse-001')
        ->and($result['name'])->toBe('Warehouse A');
});

it('selects location with highest stock when multiple options exist', function () {
    $result = app(AutoSelectLocationAction::class)->handle(
        $this->mockLocations,
        'main-001',
        null, // No preferred location
        10
    );

    // Should select warehouse (highest stock: 100)
    expect($result)->toBeArray()
        ->and($result['id'])->toBe('warehouse-001')
        ->and($result['stock_level'])->toBe(100);
});

it('excludes target location from selection', function () {
    $result = app(AutoSelectLocationAction::class)->handle(
        $this->mockLocations,
        'warehouse-001', // Target location (should be excluded)
        null,
        10
    );

    // Should select floor (next highest stock: 50)
    expect($result)->toBeArray()
        ->and($result['id'])->toBe('floor-001')
        ->and($result['stock_level'])->toBe(50);
});

it('excludes locations with no stock', function () {
    $locationsWithZeroStock = [
        [
            'id' => 'main-001',
            'name' => 'Main Bay',
            'stock_level' => 0, // No stock
        ],
        [
            'id' => 'floor-001',
            'name' => 'Floor Stock',
            'stock_level' => 50,
        ],
    ];

    $result = app(AutoSelectLocationAction::class)->handle(
        $locationsWithZeroStock,
        'target-001',
        'main-001', // Preferred but has no stock
        10
    );

    // Should select floor since main has no stock
    expect($result)->toBeArray()
        ->and($result['id'])->toBe('floor-001');
});

it('returns null when no valid source locations exist', function () {
    $locationsWithNoValidSources = [
        [
            'id' => 'main-001',
            'name' => 'Main Bay',
            'stock_level' => 0, // No stock
        ],
    ];

    $result = app(AutoSelectLocationAction::class)->handle(
        $locationsWithNoValidSources,
        'main-001', // Same as only available location
        null,
        10
    );

    expect($result)->toBeNull();
});

it('calculates correct max transfer quantity', function () {
    $location = [
        'id' => 'test-001',
        'name' => 'Test Location',
        'stock_level' => 30,
    ];

    $action = new AutoSelectLocationAction;

    // Requested quantity is less than available
    $maxQuantity = $action->getMaxTransferQuantity($location, 20);
    expect($maxQuantity)->toBe(20);

    // Requested quantity is more than available
    $maxQuantity = $action->getMaxTransferQuantity($location, 50);
    expect($maxQuantity)->toBe(30);

    // Requested quantity equals available
    $maxQuantity = $action->getMaxTransferQuantity($location, 30);
    expect($maxQuantity)->toBe(30);
});

it('handles missing stock level in max quantity calculation', function () {
    $locationWithNoStock = [
        'id' => 'test-001',
        'name' => 'Test Location',
        // Missing stock_level
    ];

    $action = new AutoSelectLocationAction;
    $maxQuantity = $action->getMaxTransferQuantity($locationWithNoStock, 10);

    expect($maxQuantity)->toBe(0);
});
