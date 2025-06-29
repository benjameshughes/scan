<?php

use App\Actions\CheckBarcodeExists;
use App\Actions\SyncBarcodeAction;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;

describe('SyncBarcodeAction', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'sku' => 'TEST-SKU-001',
            'barcode' => '1234567890123',
        ]);
    });

    test('it processes scan successfully with valid barcode', function () {
        $scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => 5,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->with($this->product->sku)
            ->once()
            ->andReturn(50);
        $linnworksService->shouldReceive('updateStockLevel')
            ->with($this->product->sku, 55)
            ->once()
            ->andReturn(true);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->with($scan->barcode)
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeTrue();
        expect($scan->fresh()->submitted)->toBeTrue();
        expect($scan->fresh()->sync_status)->toBe('synced');
        expect($scan->fresh()->submitted_at)->not->toBeNull();
    });

    test('it handles negative quantity correctly', function () {
        $scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => -3,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->with($this->product->sku)
            ->once()
            ->andReturn(10);
        $linnworksService->shouldReceive('updateStockLevel')
            ->with($this->product->sku, 7)
            ->once()
            ->andReturn(true);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeTrue();
    });

    test('it prevents stock from going below zero', function () {
        $scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => -15,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->with($this->product->sku)
            ->once()
            ->andReturn(10);
        $linnworksService->shouldReceive('updateStockLevel')
            ->with($this->product->sku, 0)
            ->once()
            ->andReturn(true);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeTrue();
    });

    test('it fails when scan is already submitted', function () {
        $scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => 5,
            'submitted' => true,
            'submitted_at' => now(),
            'user_id' => $this->user->id,
        ]);

        Log::shouldReceive('channel')
            ->with('barcode')
            ->andReturnSelf();
        Log::shouldReceive('warning')
            ->once();

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeFalse();
        expect($scan->fresh()->sync_status)->not->toBe('synced');
    });

    test('it fails when barcode does not exist in database', function () {
        $scan = Scan::factory()->create([
            'barcode' => '9999999999999',
            'quantity' => 5,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->with($scan->barcode)
            ->once()
            ->andReturn(false);

        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        Log::shouldReceive('channel')
            ->with('barcode')
            ->andReturnSelf();
        Log::shouldReceive('error')
            ->once();

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeFalse();
        expect($scan->fresh()->sync_status)->toBe('failed');
    });

    test('it fails when Linnworks stock level retrieval fails', function () {
        $scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => 5,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->with($this->product->sku)
            ->once()
            ->andReturn(null);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        Log::shouldReceive('channel')
            ->with('barcode')
            ->andReturnSelf();
        Log::shouldReceive('error')
            ->once();

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeFalse();
        expect($scan->fresh()->sync_status)->toBe('failed');
    });

    test('it fails when Linnworks stock update fails', function () {
        $scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => 5,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->with($this->product->sku)
            ->once()
            ->andReturn(50);
        $linnworksService->shouldReceive('updateStockLevel')
            ->with($this->product->sku, 55)
            ->once()
            ->andReturn(false);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        Log::shouldReceive('channel')
            ->with('barcode')
            ->andReturnSelf();
        Log::shouldReceive('error')
            ->once();

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeFalse();
        expect($scan->fresh()->sync_status)->toBe('failed');
    });

    test('it works with secondary barcode', function () {
        $product = Product::factory()->create([
            'sku' => 'SECONDARY-SKU',
            'barcode' => '1111111111111',
            'barcode_2' => '2222222222222',
        ]);

        $scan = Scan::factory()->create([
            'barcode' => '2222222222222',
            'quantity' => 3,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->with('SECONDARY-SKU')
            ->once()
            ->andReturn(20);
        $linnworksService->shouldReceive('updateStockLevel')
            ->with('SECONDARY-SKU', 23)
            ->once()
            ->andReturn(true);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeTrue();
    });

    test('it works with tertiary barcode', function () {
        $product = Product::factory()->create([
            'sku' => 'TERTIARY-SKU',
            'barcode' => '1111111111111',
            'barcode_2' => '2222222222222',
            'barcode_3' => '3333333333333',
        ]);

        $scan = Scan::factory()->create([
            'barcode' => '3333333333333',
            'quantity' => 7,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->with('TERTIARY-SKU')
            ->once()
            ->andReturn(30);
        $linnworksService->shouldReceive('updateStockLevel')
            ->with('TERTIARY-SKU', 37)
            ->once()
            ->andReturn(true);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeTrue();
    });

    test('it logs successful operations', function () {
        $scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => 5,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->once()
            ->andReturn(50);
        $linnworksService->shouldReceive('updateStockLevel')
            ->once()
            ->andReturn(true);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        Log::shouldReceive('channel')
            ->with('barcode')
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->once();

        $action = new SyncBarcodeAction($scan);
        $action->handle();
    });

    test('it handles exceptions gracefully', function () {
        $scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => 5,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        Log::shouldReceive('channel')
            ->with('barcode')
            ->andReturnSelf();
        Log::shouldReceive('error')
            ->once();

        $action = new SyncBarcodeAction($scan);
        $result = $action->handle();

        expect($result)->toBeFalse();
        expect($scan->fresh()->sync_status)->toBe('failed');
    });

    test('it updates scan timestamps correctly', function () {
        $scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => 5,
            'submitted' => false,
            'submitted_at' => null,
            'user_id' => $this->user->id,
        ]);

        $originalCreatedAt = $scan->created_at;

        $linnworksService = Mockery::mock(LinnworksApiService::class);
        $linnworksService->shouldReceive('getStockLevel')
            ->once()
            ->andReturn(50);
        $linnworksService->shouldReceive('updateStockLevel')
            ->once()
            ->andReturn(true);

        $checkBarcodeExists = Mockery::mock(CheckBarcodeExists::class);
        $checkBarcodeExists->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        app()->instance(LinnworksApiService::class, $linnworksService);
        app()->instance(CheckBarcodeExists::class, $checkBarcodeExists);

        $action = new SyncBarcodeAction($scan);
        $action->handle();

        $refreshedScan = $scan->fresh();
        expect($refreshedScan->submitted_at)->not->toBeNull();
        expect($refreshedScan->created_at)->toEqual($originalCreatedAt);
        expect($refreshedScan->submitted_at)->toBeGreaterThanOrEqual($originalCreatedAt);
    });
});
