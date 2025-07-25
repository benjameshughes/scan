<?php

use App\Livewire\Dashboard;
use App\Models\Product;
use App\Models\Scan;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\RefillSyncFailedNotification;
use App\Notifications\ScanSyncFailedNotification;
use Livewire\Livewire;

it('can display different notification types without errors', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    // Create a scan for scan-related notifications
    $scan = Scan::factory()->create([
        'user_id' => $user->id,
        'barcode' => 'TEST-123',
        'quantity' => 1,
    ]);

    // Create a stock movement for refill notifications
    $stockMovement = StockMovement::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'type' => 'refill',
    ]);

    // Create different types of notifications
    $user->notify(new ScanSyncFailedNotification($scan, 'Test scan error', 'test_error'));
    $user->notify(new RefillSyncFailedNotification($stockMovement, 'Test refill error', 'test_error'));

    // Test that dashboard can render all notification types
    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('Test scan error')
        ->assertSee('Test refill error');
});

it('handles missing data keys gracefully', function () {
    $user = User::factory()->create();

    // Create a notification with minimal data (simulating old/broken notifications)
    $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\ScanSyncFailedNotification',
        'data' => [
            'type' => 'scan_sync_failed',
            'message' => 'Test notification without scan_id',
            // Intentionally missing scan_id and barcode
        ],
        'read_at' => null,
    ]);

    // Should not throw errors
    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('Test notification without scan_id');
});

it('can mark notifications as read', function () {
    $user = User::factory()->create();
    $scan = Scan::factory()->create(['user_id' => $user->id]);

    $user->notify(new ScanSyncFailedNotification($scan, 'Test sync error', 'sync_error'));

    expect($user->unreadNotifications()->count())->toBe(1);

    $notification = $user->unreadNotifications()->first();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->call('markAsRead', $notification->id)
        ->assertDispatched('notification.markAsRead');

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

it('displays notification-specific details correctly', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['sku' => 'TEST-SKU-123']);

    $scan = Scan::factory()->create([
        'user_id' => $user->id,
        'barcode' => 'SCAN-BARCODE-456',
    ]);

    $stockMovement = StockMovement::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    // Test scan notification details
    $user->notify(new ScanSyncFailedNotification($scan, 'Scan failed', 'no_sku_found'));

    // Test refill notification details
    $user->notify(new RefillSyncFailedNotification($stockMovement, 'Refill failed', 'api_error'));

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('SCAN-BARCODE-456') // Scan barcode should be visible
        ->assertSee('TEST-SKU-123') // Product SKU should be visible
        ->assertSee('Scan ID:') // Scan ID label should be visible
        ->assertSee('Movement ID:'); // Movement ID label should be visible
});
