<?php

namespace Tests\Feature;

use App\Actions\SyncBarcodeAction;
use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Queue;
use Mockery;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Linnworks Stock Update', function () {

    test('it decreases stock level when action is decrease or null', function () {
        // Create a product
        $product = Product::factory()->create([
            'sku' => 'TEST-001',
            'barcode' => '5059031234567',
        ]);

        // Create a scan with decrease action
        $scan = Scan::factory()->create([
            'barcode' => $product->barcode,
            'quantity' => 5,
            'action' => 'decrease',
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        // Mock the LinnworksApiService
        $linnworksMock = Mockery::mock(LinnworksApiService::class);
        $linnworksMock->shouldReceive('getStockLevel')
            ->once()
            ->with('TEST-001')
            ->andReturn(100);

        $linnworksMock->shouldReceive('updateStockLevel')
            ->once()
            ->with('TEST-001', 95) // 100 - 5
            ->andReturn(['Success' => true]);

        // Create the action and inject the mock
        $action = new SyncBarcodeAction($scan);
        $action->linnworks = $linnworksMock;
        $action->handle();

        // Verify scan was marked as successful
        expect($scan->fresh()->submitted)->toBe(1); // Database stores as integer
        expect($scan->fresh()->sync_status)->toBe('synced');
    });

    test('it increases stock level when action is increase', function () {
        // Create a product
        $product = Product::factory()->create([
            'sku' => 'TEST-002',
            'barcode' => '5059031234568',
        ]);

        // Create a scan with increase action
        $scan = Scan::factory()->create([
            'barcode' => $product->barcode,
            'quantity' => 10,
            'action' => 'increase',
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        // Mock the LinnworksApiService
        $linnworksMock = Mockery::mock(LinnworksApiService::class);
        $linnworksMock->shouldReceive('getStockLevel')
            ->once()
            ->with('TEST-002')
            ->andReturn(50);

        $linnworksMock->shouldReceive('updateStockLevel')
            ->once()
            ->with('TEST-002', 60) // 50 + 10
            ->andReturn(['Success' => true]);

        // Create the action and inject the mock
        $action = new SyncBarcodeAction($scan);
        $action->linnworks = $linnworksMock;
        $action->handle();

        // Verify scan was marked as successful
        expect($scan->fresh()->submitted)->toBe(1); // Database stores as integer
        expect($scan->fresh()->sync_status)->toBe('synced');
    });

    test('it handles stock level that would go below zero', function () {
        // Create a product
        $product = Product::factory()->create([
            'sku' => 'TEST-003',
            'barcode' => '5059031234569',
        ]);

        // Create a scan that would decrease stock below zero
        $scan = Scan::factory()->create([
            'barcode' => $product->barcode,
            'quantity' => 5, // More than available stock
            'action' => 'decrease',
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        // Mock the LinnworksApiService
        $linnworksMock = Mockery::mock(LinnworksApiService::class);
        $linnworksMock->shouldReceive('getStockLevel')
            ->once()
            ->with('TEST-003')
            ->andReturn(3); // Current stock is only 3

        $linnworksMock->shouldReceive('updateStockLevel')
            ->once()
            ->with('TEST-003', 0) // Should be 0, not negative
            ->andReturn(['Success' => true]);

        // Create the action and inject the mock
        $action = new SyncBarcodeAction($scan);
        $action->linnworks = $linnworksMock;
        $action->handle();

        // Verify scan was marked as successful
        expect($scan->fresh()->submitted)->toBe(1); // Database stores as integer
        expect($scan->fresh()->sync_status)->toBe('synced');
    });

    test('it handles missing product SKU', function () {
        // Create a scan with a barcode that doesn't exist
        $scan = Scan::factory()->create([
            'barcode' => '9999999999999',
            'quantity' => 5,
            'action' => 'decrease',
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        // Execute the sync action and expect exception
        $action = new SyncBarcodeAction($scan);

        expect(fn () => $action->handle())
            ->toThrow(\App\Exceptions\NoSkuFoundException::class);

        // Verify scan was marked as failed
        expect($scan->fresh()->submitted)->toBe(0); // Database stores as integer
        expect($scan->fresh()->sync_status)->toBe('failed');
    });

    test('SyncBarcode job dispatches with correct scan', function () {
        Queue::fake();

        $product = Product::factory()->create([
            'sku' => 'TEST-004',
            'barcode' => '5059031234570',
        ]);

        $scan = Scan::factory()->create([
            'barcode' => $product->barcode,
            'quantity' => 5,
            'action' => 'decrease',
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        // Dispatch the job
        SyncBarcode::dispatch($scan);

        // Assert job was pushed
        Queue::assertPushed(SyncBarcode::class, function ($job) use ($scan) {
            return $job->scan->id === $scan->id;
        });
    });

    test('it uses LinnworksApiService to get and update stock', function () {
        $product = Product::factory()->create([
            'sku' => 'TEST-005',
            'barcode' => '5059031234571',
        ]);

        // Create a partial mock of LinnworksApiService
        $linnworksMock = Mockery::mock(LinnworksApiService::class)->makePartial();

        // Set expectations
        $linnworksMock->shouldReceive('getStockLevel')
            ->once()
            ->with('TEST-005')
            ->andReturn(75);

        $linnworksMock->shouldReceive('updateStockLevel')
            ->once()
            ->with('TEST-005', 70) // 75 - 5
            ->andReturn(['Success' => true]);

        // Bind the mock to the container
        app()->instance(LinnworksApiService::class, $linnworksMock);

        $scan = Scan::factory()->create([
            'barcode' => $product->barcode,
            'quantity' => 5,
            'action' => 'decrease',
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        // Create action with mocked service
        $action = new SyncBarcodeAction($scan);
        $action->linnworks = $linnworksMock;
        $action->handle();

        // Mockery will verify the expectations were met
    });
});
