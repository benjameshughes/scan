<?php

declare(strict_types=1);

use App\Actions\Scanner\PrepareRefillFormAction;
use App\Actions\Scanner\ProcessRefillSubmissionAction;
use App\Livewire\Scanner\RefillForm;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $role = Role::firstOrCreate(['name' => 'admin']);
    Permission::firstOrCreate(['name' => 'view scanner']);
    Permission::firstOrCreate(['name' => 'refill bays']);
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

it('prepares locations on mount', function () {
    $product = Product::factory()->create(['sku' => 'SKU-1']);

    $this->mock(PrepareRefillFormAction::class, function ($mock) {
        $mock->shouldReceive('handle')->andReturn([
            'success' => true,
            'availableLocations' => [
                ['Location' => ['StockLocationId' => 'loc-1', 'LocationName' => 'A'], 'StockLevel' => 10, 'Quantity' => 10],
            ],
            'allLocations' => [
                ['StockLocationId' => 'loc-1', 'LocationName' => 'A'],
                ['StockLocationId' => 'loc-2', 'LocationName' => 'B'],
            ],
            'selectedLocationId' => 'loc-1',
            'toLocationId' => '',
        ]);
        $mock->shouldReceive('getSmartLocationSelectorData')->andReturn([]);
        $mock->shouldReceive('getMaxRefillStock')->andReturn(10);
        $mock->shouldReceive('clearRefillError')->andReturn([]);
        $mock->shouldReceive('filterLocationsBySearch')->andReturn([
            ['StockLocationId' => 'loc-1', 'LocationName' => 'A', 'Quantity' => 10],
        ]);
        $mock->shouldReceive('filterAllLocationsBySearch')->andReturn([
            ['StockLocationId' => 'loc-1', 'LocationName' => 'A'],
            ['StockLocationId' => 'loc-2', 'LocationName' => 'B'],
        ]);
    });

    Livewire::test(RefillForm::class, [
        'product' => $product,
    ])->assertSet('selectedLocationId', 'loc-1')
        ->assertCount('availableLocations', 1);
});

it('initializes with default location pre-selected', function () {
    $product = Product::factory()->create(['sku' => 'SKU-DEFAULT']);
    $defaultLocationId = config('linnworks.default_location_id');

    $this->mock(PrepareRefillFormAction::class, function ($mock) use ($defaultLocationId) {
        $mock->shouldReceive('handle')->andReturn([
            'success' => true,
            'availableLocations' => [
                ['Location' => ['StockLocationId' => 'loc-1', 'LocationName' => 'A'], 'StockLevel' => 10, 'Quantity' => 10],
            ],
            'allLocations' => [
                ['StockLocationId' => $defaultLocationId, 'LocationName' => 'Default'],
                ['StockLocationId' => 'loc-1', 'LocationName' => 'A'],
            ],
            'selectedLocationId' => 'loc-1',
            'toLocationId' => $defaultLocationId,
        ]);
        $mock->shouldReceive('getSmartLocationSelectorData')->andReturn([]);
        $mock->shouldReceive('getMaxRefillStock')->andReturn(10);
        $mock->shouldReceive('filterLocationsBySearch')->andReturn([
            ['StockLocationId' => 'loc-1', 'LocationName' => 'A', 'Quantity' => 10],
        ]);
        $mock->shouldReceive('filterAllLocationsBySearch')->andReturn([
            ['StockLocationId' => $defaultLocationId, 'LocationName' => 'Default'],
            ['StockLocationId' => 'loc-1', 'LocationName' => 'A'],
        ]);
    });

    Livewire::test(RefillForm::class, [
        'product' => $product,
    ])
        ->assertSet('toLocationId', $defaultLocationId)
        ->assertSee('Default');
});

it('submits refill and dispatches event', function () {
    $product = Product::factory()->create(['sku' => 'SKU-2']);

    $this->mock(PrepareRefillFormAction::class, function ($mock) {
        $mock->shouldReceive('handle')->andReturn([
            'success' => true,
            'availableLocations' => [
                ['Location' => ['StockLocationId' => 'loc-1', 'LocationName' => 'A'], 'StockLevel' => 10, 'Quantity' => 10],
            ],
            'allLocations' => [
                ['StockLocationId' => 'loc-1', 'LocationName' => 'A'],
                ['StockLocationId' => 'loc-2', 'LocationName' => 'B'],
            ],
            'selectedLocationId' => 'loc-1',
            'toLocationId' => '',
        ]);
        $mock->shouldReceive('validateRefillQuantity')->andReturn(['valid' => true]);
        $mock->shouldReceive('getSmartLocationSelectorData')->andReturn([]);
        $mock->shouldReceive('getMaxRefillStock')->andReturn(10);
        $mock->shouldReceive('clearRefillError')->andReturn([]);
        $mock->shouldReceive('filterLocationsBySearch')->andReturn([
            ['StockLocationId' => 'loc-1', 'LocationName' => 'A', 'Quantity' => 10],
        ]);
        $mock->shouldReceive('filterAllLocationsBySearch')->andReturn([
            ['StockLocationId' => 'loc-1', 'LocationName' => 'A'],
            ['StockLocationId' => 'loc-2', 'LocationName' => 'B'],
        ]);
    });

    $this->mock(ProcessRefillSubmissionAction::class, function ($mock) {
        $mock->shouldReceive('setProcessingState')->andReturn([
            'isProcessingRefill' => true,
            'refillError' => '',
        ]);
        $mock->shouldReceive('handle')->andReturn([
            'success' => true,
            'message' => 'Transferred',
        ]);
    });

    Livewire::test(RefillForm::class, [
        'product' => $product,
    ])->set('selectedLocationId', 'loc-1')
        ->set('refillQuantity', 3)
        ->call('submitRefill')
        ->assertDispatched('refill-submitted')
        ->assertDispatched('refill-cancelled'); // Auto-close on success
});
