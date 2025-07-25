<?php

use App\Jobs\ProcessStockMovement;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create([
        'sku' => 'TEST-JOB-001',
    ]);
});

it('can be dispatched with a stock movement', function () {
    Queue::fake();

    $stockMovement = StockMovement::create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'from_location_id' => 'floor-001',
        'from_location_code' => 'FLOOR',
        'to_location_id' => 'main-001',
        'to_location_code' => 'MAIN',
        'quantity' => 10,
        'type' => StockMovement::TYPE_BAY_REFILL,
        'moved_at' => now(),
        'sync_status' => 'pending',
    ]);

    ProcessStockMovement::dispatch($stockMovement);

    Queue::assertPushed(ProcessStockMovement::class, function ($job) use ($stockMovement) {
        return $job->stockMovement->id === $stockMovement->id;
    });
});

it('processes stock movement successfully', function () {
    // Mock LinnworksApiService
    $mockService = Mockery::mock(LinnworksApiService::class);
    $mockService->shouldReceive('transferStockToDefaultLocation')
        ->with('TEST-JOB-001', 'floor-001', 10)
        ->once()
        ->andReturn(['success' => true, 'message' => 'Transfer completed']);

    $this->app->instance(LinnworksApiService::class, $mockService);

    $stockMovement = StockMovement::create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'from_location_id' => 'floor-001',
        'from_location_code' => 'FLOOR',
        'to_location_id' => config('linnworks.default_location_id', 'main-001'),
        'to_location_code' => 'MAIN',
        'quantity' => 10,
        'type' => StockMovement::TYPE_BAY_REFILL,
        'moved_at' => now(),
        'sync_status' => 'pending',
        'sync_attempts' => 0,
    ]);

    $job = new ProcessStockMovement($stockMovement);
    $job->handle();

    // Refresh from database
    $stockMovement->refresh();

    expect($stockMovement->sync_status)->toBe('synced')
        ->and($stockMovement->processed_at)->not()->toBeNull()
        ->and($stockMovement->sync_attempts)->toBe(1)
        ->and($stockMovement->sync_error_message)->toBeNull();
});

it('handles processing failures correctly', function () {
    // Mock LinnworksApiService to throw exception
    $mockService = Mockery::mock(LinnworksApiService::class);
    $mockService->shouldReceive('transferStockToDefaultLocation')
        ->with('TEST-JOB-001', 'floor-001', 10)
        ->once()
        ->andThrow(new Exception('API Error'));

    $this->app->instance(LinnworksApiService::class, $mockService);

    $stockMovement = StockMovement::create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'from_location_id' => 'floor-001',
        'from_location_code' => 'FLOOR',
        'to_location_id' => config('linnworks.default_location_id', 'main-001'),
        'to_location_code' => 'MAIN',
        'quantity' => 10,
        'type' => StockMovement::TYPE_BAY_REFILL,
        'moved_at' => now(),
        'sync_status' => 'pending',
        'sync_attempts' => 0,
    ]);

    $job = new ProcessStockMovement($stockMovement);

    expect(function () use ($job) {
        $job->handle();
    })->toThrow(Exception::class, 'API Error');

    // Refresh from database
    $stockMovement->refresh();

    expect($stockMovement->sync_status)->toBe('failed')
        ->and($stockMovement->sync_error_message)->toContain('API Error')
        ->and($stockMovement->sync_error_type)->toBe('general_error')
        ->and($stockMovement->sync_attempts)->toBe(1);
});

it('skips already processed movements', function () {
    $stockMovement = StockMovement::create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'from_location_id' => 'floor-001',
        'from_location_code' => 'FLOOR',
        'to_location_id' => 'main-001',
        'to_location_code' => 'MAIN',
        'quantity' => 10,
        'type' => StockMovement::TYPE_BAY_REFILL,
        'moved_at' => now(),
        'sync_status' => 'synced', // Already processed
        'processed_at' => now(),
    ]);

    $job = new ProcessStockMovement($stockMovement);
    $job->handle();

    // Should not change the status
    $stockMovement->refresh();
    expect($stockMovement->sync_status)->toBe('synced');
});

it('has correct job tags', function () {
    $stockMovement = StockMovement::create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'from_location_id' => 'floor-001',
        'from_location_code' => 'FLOOR',
        'to_location_id' => 'main-001',
        'to_location_code' => 'MAIN',
        'quantity' => 10,
        'type' => StockMovement::TYPE_BAY_REFILL,
        'moved_at' => now(),
        'sync_status' => 'pending',
    ]);

    $job = new ProcessStockMovement($stockMovement);
    $tags = $job->tags();

    expect($tags)->toContain('stock-movement')
        ->and($tags)->toContain('movement:'.$stockMovement->id)
        ->and($tags)->toContain('type:'.StockMovement::TYPE_BAY_REFILL)
        ->and($tags)->toContain('product:TEST-JOB-001');
});
