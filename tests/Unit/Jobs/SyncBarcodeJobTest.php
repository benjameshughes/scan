<?php

use App\Actions\SyncBarcodeAction;
use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

describe('SyncBarcode Job', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'barcode' => '1234567890123',
        ]);
        $this->scan = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => 5,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);
    });

    test('it can be instantiated with scan', function () {
        $job = new SyncBarcode($this->scan);

        expect($job->scan)->toBeInstanceOf(Scan::class);
        expect($job->scan->id)->toBe($this->scan->id);
    });

    test('it implements ShouldQueue interface', function () {
        $job = new SyncBarcode($this->scan);

        expect($job)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });

    test('it uses correct traits', function () {
        $reflection = new ReflectionClass(SyncBarcode::class);
        $traits = $reflection->getTraitNames();

        expect($traits)->toContain('Illuminate\Foundation\Bus\Dispatchable');
        expect($traits)->toContain('Illuminate\Queue\InteractsWithQueue');
        expect($traits)->toContain('Illuminate\Bus\Queueable');
        expect($traits)->toContain('Illuminate\Queue\SerializesModels');
        expect($traits)->toContain('Illuminate\Bus\Batchable');
    });

    test('it executes SyncBarcodeAction when handled', function () {
        $syncBarcodeAction = Mockery::mock(SyncBarcodeAction::class);
        $syncBarcodeAction->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        // Mock the constructor to return our mock
        $originalScan = $this->scan;
        $mockAction = Mockery::mock('overload:'.SyncBarcodeAction::class);
        $mockAction->shouldReceive('__construct')
            ->with(Mockery::on(function ($scan) use ($originalScan) {
                return $scan->id === $originalScan->id;
            }))
            ->andReturnSelf();
        $mockAction->shouldReceive('handle')
            ->once()
            ->andReturn(true);

        $job = new SyncBarcode($this->scan);
        $job->handle();

        // If we get here without exception, the job executed successfully
        expect(true)->toBeTrue();
    });

    test('it serializes scan model correctly', function () {
        $job = new SyncBarcode($this->scan);

        // Test that job can be serialized (important for queue storage)
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        expect($unserialized->scan)->toBeInstanceOf(Scan::class);
        expect($unserialized->scan->id)->toBe($this->scan->id);
    });

    test('it can be dispatched', function () {
        Queue::fake();

        SyncBarcode::dispatch($this->scan);

        Queue::assertPushed(SyncBarcode::class, function ($job) {
            return $job->scan->id === $this->scan->id;
        });
    });

    test('it can be dispatched with delay', function () {
        Queue::fake();

        SyncBarcode::dispatch($this->scan)->delay(now()->addMinutes(5));

        Queue::assertPushed(SyncBarcode::class);
    });

    test('it can be added to batch', function () {
        $this->scan2 = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'quantity' => 3,
            'submitted' => false,
            'user_id' => $this->user->id,
        ]);

        Bus::fake();

        $batch = Bus::batch([
            new SyncBarcode($this->scan),
            new SyncBarcode($this->scan2),
        ])->dispatch();

        Bus::assertBatched(function ($batch) {
            return $batch->jobs->count() === 2;
        });
    });

    test('it handles action failure gracefully', function () {
        // Mock the action to throw an exception
        $mockAction = Mockery::mock('overload:'.SyncBarcodeAction::class);
        $mockAction->shouldReceive('__construct')
            ->andReturnSelf();
        $mockAction->shouldReceive('handle')
            ->once()
            ->andThrow(new \Exception('Sync failed'));

        $job = new SyncBarcode($this->scan);

        // The job should allow the exception to bubble up
        // (This is by design - let Laravel's queue system handle retries)
        expect(fn () => $job->handle())->toThrow(\Exception::class);
    });

    test('it preserves scan relationship data', function () {
        // Create scan with user relationship
        $scanWithUser = Scan::factory()->create([
            'barcode' => $this->product->barcode,
            'user_id' => $this->user->id,
        ]);

        $job = new SyncBarcode($scanWithUser);

        // Verify the scan still has its relationships
        expect($job->scan->user)->not->toBeNull();
        expect($job->scan->user->id)->toBe($this->user->id);
    });

    test('it can be queued on specific queue', function () {
        Queue::fake();

        SyncBarcode::dispatch($this->scan)->onQueue('high-priority');

        Queue::assertPushedOn('high-priority', SyncBarcode::class);
    });

    test('it can be queued on specific connection', function () {
        Queue::fake();

        SyncBarcode::dispatch($this->scan)->onConnection('redis');

        Queue::assertPushedOn('redis', SyncBarcode::class);
    });

    test('it handles scan model deletion before job execution', function () {
        $scanId = $this->scan->id;
        $job = new SyncBarcode($this->scan);

        // Delete the scan before job execution
        $this->scan->delete();

        // Create a mock that expects the action to still be called
        // (since the scan is serialized in the job)
        $mockAction = Mockery::mock('overload:'.SyncBarcodeAction::class);
        $mockAction->shouldReceive('__construct')
            ->once()
            ->andReturnSelf();
        $mockAction->shouldReceive('handle')
            ->once();

        // Job should still execute with the serialized scan data
        $job->handle();

        // Verify scan was actually deleted
        expect(Scan::find($scanId))->toBeNull();
    });

    test('it can handle multiple dispatches', function () {
        Queue::fake();

        // Dispatch multiple jobs
        SyncBarcode::dispatch($this->scan);
        SyncBarcode::dispatch($this->scan);
        SyncBarcode::dispatch($this->scan);

        Queue::assertPushed(SyncBarcode::class, 3);
    });
});
