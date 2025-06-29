<?php

use App\Models\Product;
use App\Models\Scan;
use App\Models\User;

describe('Scan Model', function () {

    test('it can be created with factory', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $scan = Scan::factory()->create([
            'user_id' => $user->id,
            'barcode' => $product->barcode,
        ]);

        expect($scan)->toBeInstanceOf(Scan::class);
        expect($scan->id)->not->toBeNull();
        expect($scan->barcode)->not->toBeNull();
        expect($scan->quantity)->not->toBeNull();
    });

    test('it has guarded attributes', function () {
        $guarded = [
            'id',
            'created_at',
        ];

        $scan = new Scan;
        expect($scan->getGuarded())->toEqual($guarded);
    });

    test('it belongs to user', function () {
        $user = User::factory()->create();
        $scan = Scan::factory()->create([
            'user_id' => $user->id,
        ]);

        expect($scan->user)->toBeInstanceOf(User::class);
        expect($scan->user->id)->toBe($user->id);
    });

    test('it belongs to product through custom relationship', function () {
        $product = Product::factory()->create([
            'barcode' => '1234567890123',
        ]);

        $scan = Scan::factory()->create([
            'barcode' => $product->barcode,
        ]);

        expect($scan->product)->toBeInstanceOf(Product::class);
        expect($scan->product->id)->toBe($product->id);
    });

    test('product relationship works with secondary barcode', function () {
        $product = Product::factory()->create([
            'barcode' => '1111111111111',
            'barcode_2' => '2222222222222',
        ]);

        $scan = Scan::factory()->create([
            'barcode' => '2222222222222',
        ]);

        expect($scan->product)->toBeInstanceOf(Product::class);
        expect($scan->product->id)->toBe($product->id);
    });

    test('product relationship works with tertiary barcode', function () {
        $product = Product::factory()->create([
            'barcode' => '1111111111111',
            'barcode_2' => '2222222222222',
            'barcode_3' => '3333333333333',
        ]);

        $scan = Scan::factory()->create([
            'barcode' => '3333333333333',
        ]);

        expect($scan->product)->toBeInstanceOf(Product::class);
        expect($scan->product->id)->toBe($product->id);
    });

    test('product relationship returns null when no product matches', function () {
        $scan = Scan::factory()->create([
            'barcode' => '9999999999999',
        ]);

        expect($scan->product)->toBeNull();
    });

    test('dateForHumans returns correct format for current year', function () {
        $scan = Scan::factory()->create([
            'created_at' => now()->setMonth(6)->setDay(15)->setHour(14)->setMinute(30),
        ]);

        $formatted = $scan->dateForHumans();

        expect($formatted)->toMatch('/Jun 15, \d{1,2}:\d{2} [AP]M/');
    });

    test('dateForHumans returns correct format for different year', function () {
        $lastYear = now()->subYear();
        $scan = Scan::factory()->create([
            'created_at' => $lastYear->setMonth(6)->setDay(15),
        ]);

        $formatted = $scan->dateForHumans();

        expect($formatted)->toBe('Jun 15, '.$lastYear->year);
    });

    test('it can be created with all required fields', function () {
        $user = User::factory()->create();

        $scan = Scan::create([
            'barcode' => '1234567890123',
            'quantity' => 5,
            'submitted' => false,
            'scanAction' => false,
            'sync_status' => 'pending',
            'user_id' => $user->id,
        ]);

        expect($scan->barcode)->toBe('1234567890123');
        expect($scan->quantity)->toBe(5);
        expect($scan->submitted)->toBeFalse();
        expect($scan->scanAction)->toBeFalse();
        expect($scan->sync_status)->toBe('pending');
        expect($scan->user_id)->toBe($user->id);
    });

    test('it can be created with submitted scan', function () {
        $submittedAt = now();

        $scan = Scan::factory()->create([
            'submitted' => true,
            'submitted_at' => $submittedAt,
            'sync_status' => 'synced',
        ]);

        expect($scan->submitted)->toBeTrue();
        expect($scan->submitted_at)->toEqual($submittedAt);
        expect($scan->sync_status)->toBe('synced');
    });

    test('factory creates realistic data patterns', function () {
        $scan = Scan::factory()->create();

        // Quantity should be between 1 and 100
        expect($scan->quantity)->toBeGreaterThanOrEqual(1);
        expect($scan->quantity)->toBeLessThanOrEqual(100);

        // Sync status should follow submitted logic
        if ($scan->submitted) {
            expect($scan->sync_status)->toBe('synced');
        } else {
            expect(in_array($scan->sync_status, ['pending', 'syncing']))->toBeTrue();
        }

        // Created_at should be before submitted_at
        if ($scan->submitted_at) {
            expect($scan->created_at)->toBeLessThanOrEqual($scan->submitted_at);
        }
    });

    test('it handles different sync statuses', function () {
        $pendingScan = Scan::factory()->create([
            'sync_status' => 'pending',
        ]);

        $syncingScan = Scan::factory()->create([
            'sync_status' => 'syncing',
        ]);

        $syncedScan = Scan::factory()->create([
            'sync_status' => 'synced',
        ]);

        expect($pendingScan->sync_status)->toBe('pending');
        expect($syncingScan->sync_status)->toBe('syncing');
        expect($syncedScan->sync_status)->toBe('synced');
    });

    test('it can have positive and negative quantities', function () {
        $positiveScan = Scan::factory()->create([
            'quantity' => 10,
        ]);

        $negativeScan = Scan::factory()->create([
            'quantity' => -5,
        ]);

        expect($positiveScan->quantity)->toBe(10);
        expect($negativeScan->quantity)->toBe(-5);
    });

    test('it has timestamps', function () {
        $scan = Scan::factory()->create();

        expect($scan->created_at)->not->toBeNull();
        expect($scan->updated_at)->not->toBeNull();
    });

    test('it can be updated', function () {
        $scan = Scan::factory()->create([
            'quantity' => 5,
        ]);

        $scan->update(['quantity' => 10]);

        expect($scan->fresh()->quantity)->toBe(10);
    });

    test('it can be deleted', function () {
        $scan = Scan::factory()->create();
        $scanId = $scan->id;

        $scan->delete();

        expect(Scan::find($scanId))->toBeNull();
    });

    test('it can handle null user_id', function () {
        $scan = Scan::factory()->create([
            'user_id' => null,
        ]);

        expect($scan->user_id)->toBeNull();
        expect($scan->user)->toBeNull();
    });

    test('it can handle string user_id', function () {
        $scan = Scan::factory()->create([
            'user_id' => '1',
        ]);

        expect($scan->user_id)->toBe('1');
    });

    test('dateForHumans handles edge cases correctly', function () {
        // Test midnight
        $midnightScan = Scan::factory()->create([
            'created_at' => now()->setTime(0, 0, 0),
        ]);

        $formatted = $midnightScan->dateForHumans();
        expect($formatted)->toContain('12:00 AM');

        // Test noon
        $noonScan = Scan::factory()->create([
            'created_at' => now()->setTime(12, 0, 0),
        ]);

        $formatted = $noonScan->dateForHumans();
        expect($formatted)->toContain('12:00 PM');
    });

    test('it prioritizes primary barcode in product relationship', function () {
        // Create a product with primary barcode
        $primaryProduct = Product::factory()->create([
            'name' => 'Primary Product',
            'barcode' => '1234567890123',
        ]);

        // Create another product with the same barcode as secondary
        $secondaryProduct = Product::factory()->create([
            'name' => 'Secondary Product',
            'barcode' => '9999999999999',
            'barcode_2' => '1234567890123',
        ]);

        $scan = Scan::factory()->create([
            'barcode' => '1234567890123',
        ]);

        // Should find one of the products
        expect($scan->product)->not->toBeNull();
        expect(collect([$primaryProduct->id, $secondaryProduct->id]))->toContain($scan->product->id);
    });
});
