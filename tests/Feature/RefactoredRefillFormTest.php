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
                ['Location' => ['StockLocationId' => 'loc-1', 'LocationName' => 'A'], 'StockLevel' => 10],
            ],
            'selectedLocationId' => 'loc-1',
        ]);
        $mock->shouldReceive('getSmartLocationSelectorData')->andReturn([]);
        $mock->shouldReceive('getMaxRefillStock')->andReturn(10);
        $mock->shouldReceive('clearRefillError')->andReturn([]);
    });

    Livewire::test(RefillForm::class, [
        'product' => $product,
        'isEmailRefill' => false,
    ])->assertSet('selectedLocationId', 'loc-1')
        ->assertCount('availableLocations', 1);
});

it('submits refill and dispatches event', function () {
    $product = Product::factory()->create(['sku' => 'SKU-2']);

    $this->mock(PrepareRefillFormAction::class, function ($mock) {
        $mock->shouldReceive('handle')->andReturn([
            'success' => true,
            'availableLocations' => [
                ['Location' => ['StockLocationId' => 'loc-1', 'LocationName' => 'A'], 'StockLevel' => 10],
            ],
            'selectedLocationId' => 'loc-1',
        ]);
        $mock->shouldReceive('validateRefillQuantity')->andReturn(['valid' => true]);
        $mock->shouldReceive('getSmartLocationSelectorData')->andReturn([]);
        $mock->shouldReceive('getMaxRefillStock')->andReturn(10);
        $mock->shouldReceive('clearRefillError')->andReturn([]);
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
        $mock->shouldReceive('prepareSuccessState')->andReturn([
            'isProcessingRefill' => false,
            'refillSuccess' => 'Transferred',
        ]);
    });

    Livewire::test(RefillForm::class, [
        'product' => $product,
    ])->set('selectedLocationId', 'loc-1')
        ->set('refillQuantity', 3)
        ->call('submitRefill')
        ->assertSet('refillSuccess', 'Transferred')
        ->assertDispatched('refill-submitted');
});
