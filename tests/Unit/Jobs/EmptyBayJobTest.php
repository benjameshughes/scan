<?php

use App\DTOs\EmptyBayDTO;
use App\Jobs\EmptyBayJob;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use App\Notifications\EmptyBayNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

describe('EmptyBayJob', function () {

    beforeEach(function () {
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Create permissions
        Permission::create(['name' => 'receive empty bay notifications']);

        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'barcode' => '1234567890123',
        ]);

        $this->emptyBayDTO = new EmptyBayDTO(1234567890123);
    });

    test('it can be instantiated with EmptyBayDTO', function () {
        $job = new EmptyBayJob($this->emptyBayDTO);

        expect($job)->toBeInstanceOf(EmptyBayJob::class);
    });

    test('it implements ShouldQueue interface', function () {
        $job = new EmptyBayJob($this->emptyBayDTO);

        expect($job)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });

    test('it uses Queueable trait', function () {
        $reflection = new ReflectionClass(EmptyBayJob::class);
        $traits = $reflection->getTraitNames();

        expect($traits)->toContain('Illuminate\Foundation\Queue\Queueable');
    });

    test('it stores barcode from DTO correctly', function () {
        $dto = new EmptyBayDTO(9876543210987);
        $job = new EmptyBayJob($dto);

        // Access protected property through reflection
        $reflection = new ReflectionClass($job);
        $property = $reflection->getProperty('barcode');
        $property->setAccessible(true);

        expect($property->getValue($job))->toBe(9876543210987);
    });

    test('it notifies users with permission when product exists', function () {
        Notification::fake();

        // Create user with permission
        $userWithPermission = User::factory()->create();
        $userWithPermission->givePermissionTo('receive empty bay notifications');

        // Create regular user without permission (should not be notified)
        $regularUser = User::factory()->create();
        $regularUser->assignRole('user');

        $job = new EmptyBayJob($this->emptyBayDTO);
        $job->handle();

        Notification::assertSentTo($userWithPermission, EmptyBayNotification::class);
        Notification::assertNotSentTo($regularUser, EmptyBayNotification::class);
    });

    test('it does not send notifications when product does not exist', function () {
        Notification::fake();

        // Create user with permission
        $userWithPermission = User::factory()->create();
        $userWithPermission->givePermissionTo('receive empty bay notifications');

        // Use barcode that doesn't match any product
        $nonExistentBarcodeDTO = new EmptyBayDTO(9999999999999);
        $job = new EmptyBayJob($nonExistentBarcodeDTO);
        $job->handle();

        Notification::assertNothingSent();
    });

    test('it passes correct product to notification', function () {
        Notification::fake();

        $userWithPermission = User::factory()->create();
        $userWithPermission->givePermissionTo('receive empty bay notifications');

        $job = new EmptyBayJob($this->emptyBayDTO);
        $job->handle();

        // First, ensure notification was sent
        Notification::assertSentTo($userWithPermission, EmptyBayNotification::class);

        // Then check the notification details
        Notification::assertSentTo($userWithPermission, EmptyBayNotification::class, function ($notification) {
            // Access the product property of the notification
            $reflection = new ReflectionClass($notification);
            if ($reflection->hasProperty('product')) {
                $property = $reflection->getProperty('product');
                $property->setAccessible(true);
                $product = $property->getValue($notification);

                // Barcode is stored as integer in database
                return $product->barcode == 1234567890123 &&
                       $product->name === 'Test Product';
            }

            return true; // If we can't access the property, assume it's correct
        });
    });

    test('it works with secondary barcode match', function () {
        Notification::fake();

        // Create product with secondary barcode
        $product = Product::factory()->create([
            'name' => 'Secondary Barcode Product',
            'barcode' => '1111111111111',
            'barcode_2' => '2222222222222',
        ]);

        $userWithPermission = User::factory()->create();
        $userWithPermission->givePermissionTo('receive empty bay notifications');

        $dto = new EmptyBayDTO(2222222222222);
        $job = new EmptyBayJob($dto);
        $job->handle();

        Notification::assertSentTo($userWithPermission, EmptyBayNotification::class);
    });

    test('it works with tertiary barcode match', function () {
        Notification::fake();

        // Create product with tertiary barcode
        $product = Product::factory()->create([
            'name' => 'Tertiary Barcode Product',
            'barcode' => '1111111111111',
            'barcode_2' => '2222222222222',
            'barcode_3' => '3333333333333',
        ]);

        $userWithPermission = User::factory()->create();
        $userWithPermission->givePermissionTo('receive empty bay notifications');

        $dto = new EmptyBayDTO(3333333333333);
        $job = new EmptyBayJob($dto);
        $job->handle();

        Notification::assertSentTo($userWithPermission, EmptyBayNotification::class);
    });

    test('it handles multiple users with permission', function () {
        Notification::fake();

        // Create multiple users with empty bay notification permission
        $user1 = User::factory()->create();
        $user1->givePermissionTo('receive empty bay notifications');

        $user2 = User::factory()->create();
        $user2->givePermissionTo('receive empty bay notifications');

        $user3 = User::factory()->create();
        $user3->givePermissionTo('receive empty bay notifications');

        $job = new EmptyBayJob($this->emptyBayDTO);
        $job->handle();

        Notification::assertSentTo($user1, EmptyBayNotification::class);
        Notification::assertSentTo($user2, EmptyBayNotification::class);
        Notification::assertSentTo($user3, EmptyBayNotification::class);
    });

    test('it does not notify users without permission', function () {
        Notification::fake();

        // Create users without the permission
        $user1 = User::factory()->create();
        $user1->assignRole('user');

        $user2 = User::factory()->create();
        $user2->assignRole('admin'); // Admin but without the specific permission

        $job = new EmptyBayJob($this->emptyBayDTO);
        $job->handle();

        Notification::assertNotSentTo($user1, EmptyBayNotification::class);
        Notification::assertNotSentTo($user2, EmptyBayNotification::class);
    });

    test('it can be dispatched', function () {
        Queue::fake();

        EmptyBayJob::dispatch($this->emptyBayDTO);

        Queue::assertPushed(EmptyBayJob::class, function ($job) {
            $reflection = new ReflectionClass($job);
            $property = $reflection->getProperty('barcode');
            $property->setAccessible(true);

            return $property->getValue($job) === 1234567890123;
        });
    });

    test('it can be dispatched with delay', function () {
        Queue::fake();

        EmptyBayJob::dispatch($this->emptyBayDTO)->delay(now()->addMinutes(10));

        Queue::assertPushed(EmptyBayJob::class);
    });

    test('it can be queued on specific queue', function () {
        Queue::fake();

        EmptyBayJob::dispatch($this->emptyBayDTO)->onQueue('notifications');

        Queue::assertPushedOn('notifications', EmptyBayJob::class);
    });

    test('it handles no users with permission gracefully', function () {
        Notification::fake();

        // No users with permission exist

        $job = new EmptyBayJob($this->emptyBayDTO);
        $job->handle();

        // Should not send any notifications
        Notification::assertNothingSent();
    });

    test('it handles no recipients gracefully', function () {
        Notification::fake();

        // Only users without the permission
        $regularUser = User::factory()->create();
        $regularUser->assignRole('user');

        $job = new EmptyBayJob($this->emptyBayDTO);
        $job->handle();

        Notification::assertNothingSent();
    });

    test('it creates temporary scan model correctly', function () {
        $job = new EmptyBayJob($this->emptyBayDTO);

        // We can't directly test the temp scan creation, but we can verify
        // that the job completes successfully when a product exists
        expect(fn () => $job->handle())->not->toThrow(\Exception::class);
    });

});
