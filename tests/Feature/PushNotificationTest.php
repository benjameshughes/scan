<?php

use App\Models\Product;
use App\Models\User;
use App\Notifications\EmptyBayNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions if they don't exist
    Permission::findOrCreate('refill bays');

    $this->user = User::factory()->create();
    $this->user->givePermissionTo('refill bays');

    $this->product = Product::factory()->create([
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'barcode' => '123456789',
    ]);
});

it('includes all channels when user has required permission', function () {
    $notification = new EmptyBayNotification($this->product);
    $channels = $notification->via($this->user);

    expect($channels)->toContain('broadcast');
    expect($channels)->toContain('database');
    expect($channels)->toContain('mail');
});

it('excludes all channels when user lacks required permission', function () {
    $userWithoutPermission = User::factory()->create();
    // Don't give the 'refill bays' permission to this user

    $notification = new EmptyBayNotification($this->product);
    $channels = $notification->via($userWithoutPermission);

    expect($channels)->toBeEmpty();
});

it('creates proper broadcast message format', function () {
    $notification = new EmptyBayNotification($this->product);
    $broadcastMessage = $notification->toBroadcast($this->user);

    expect($broadcastMessage)->toBeInstanceOf(BroadcastMessage::class);

    $data = $broadcastMessage->data;
    expect($data)->toHaveKeys([
        'type',
        'title',
        'message',
        'product_id',
        'product_sku',
        'product_name',
        'barcode',
        'action_url',
        'severity',
        'timestamp',
        'icon',
    ]);

    expect($data['type'])->toBe('empty_bay');
    expect($data['severity'])->toBe('high');
    expect($data['product_sku'])->toBe('TEST-001');
    expect($data['icon'])->toBe('📦');
});

it('sends notification with all channels when user has permission', function () {
    Notification::fake();

    $this->user->notify(new EmptyBayNotification($this->product));

    Notification::assertSentTo(
        $this->user,
        EmptyBayNotification::class,
        function ($notification, $channels) {
            return in_array('broadcast', $channels) &&
                   in_array('database', $channels) &&
                   in_array('mail', $channels);
        }
    );
});

it('user has custom broadcast channel name', function () {
    expect($this->user->receivesBroadcastNotificationsOn())
        ->toBe('users.'.$this->user->id);
});

it('user settings no longer contain notification preferences', function () {
    $newUser = User::factory()->create();

    expect($newUser->settings)->not->toHaveKey('notification_push');
    expect($newUser->settings)->not->toHaveKey('notification_emails');
    expect($newUser->settings)->not->toHaveKey('notification_database');
});

it('handles user with no permission gracefully', function () {
    $userWithoutPermission = User::factory()->create();
    $notification = new EmptyBayNotification($this->product);

    $channels = $notification->via($userWithoutPermission);

    expect($channels)->toBeEmpty();
});
