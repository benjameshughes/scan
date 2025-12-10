<?php

declare(strict_types=1);

use App\Actions\Scanner\AutoSubmitScanAction;
use App\Actions\Scanner\CreateScanRecordAction;
use App\Actions\Scanner\ValidateScanDataAction;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $role = Role::firstOrCreate(['name' => 'admin']);
    Permission::firstOrCreate(['name' => 'view scanner']);
    $this->user = User::factory()->create([
        'settings' => [
            'auto_submit' => true,
            'scan_sound' => true,
            'vibration_pattern' => 'medium',
        ],
    ]);
    $this->actingAs($this->user);
});

it('creates scan with default values during auto-submit', function () {
    $product = Product::factory()->create(['barcode' => '5059031234567']);

    // Mock Log facade
    Log::shouldReceive('info')->andReturn(null);
    Log::shouldReceive('error')->andReturn(null);

    // Mock ValidateScanDataAction
    $this->mock(ValidateScanDataAction::class, function ($mock) {
        $mock->shouldReceive('validateOrFail')->andReturnTrue();
    });

    // Mock CreateScanRecordAction
    $this->mock(CreateScanRecordAction::class, function ($mock) {
        $mock->shouldReceive('handle')->andReturnUsing(function ($scanData) {
            return Scan::create([
                'barcode' => $scanData->barcode,
                'quantity' => $scanData->quantity,
                'action' => $scanData->action,
                'user_id' => $scanData->userId,
                'submitted' => false,
                'sync_status' => 'pending',
            ]);
        });
    });

    $autoSubmitAction = app(AutoSubmitScanAction::class);
    $result = $autoSubmitAction->handle($product, $product->barcode, $this->user->id);

    expect($result['success'])->toBeTrue();
    expect($result['scan_id'])->toBeInt();

    $scan = Scan::find($result['scan_id']);
    expect($scan)->not->toBeNull();
    expect($scan->quantity)->toBe(1);
    expect($scan->action)->toBe('decrease');
    expect($scan->barcode)->toEqual($product->barcode); // Use toEqual for flexible comparison
    expect($scan->user_id)->toBe($this->user->id);
});

it('checks if auto-submit should be triggered correctly', function () {
    $product = Product::factory()->create(['barcode' => '5059031234567']);
    $autoSubmitAction = app(AutoSubmitScanAction::class);

    // Should trigger with product and enabled setting
    expect($autoSubmitAction->shouldAutoSubmit($product, true, true))->toBeTrue();

    // Should not trigger without product
    expect($autoSubmitAction->shouldAutoSubmit(null, true, true))->toBeFalse();

    // Should not trigger when disabled
    expect($autoSubmitAction->shouldAutoSubmit($product, false, true))->toBeFalse();

    // Should not trigger for manual entry (isCameraDetection = false)
    expect($autoSubmitAction->shouldAutoSubmit($product, true, false))->toBeFalse();
});

it('handles auto-submit errors gracefully', function () {
    $product = Product::factory()->create(['barcode' => '5059031234567']);

    // Mock Log to prevent errors
    Log::shouldReceive('info')->andReturn(null);
    Log::shouldReceive('error')->andReturn(null);

    // Mock validation to fail
    $this->mock(ValidateScanDataAction::class, function ($mock) {
        $mock->shouldReceive('validateOrFail')->andThrow(new Exception('Validation failed'));
    });

    $autoSubmitAction = app(AutoSubmitScanAction::class);
    $result = $autoSubmitAction->handle($product, $product->barcode, $this->user->id);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Validation failed');
});

it('logs auto-submit attempts', function () {
    $product = Product::factory()->create(['barcode' => '5059031234567']);

    // Mock Log facade
    Log::shouldReceive('info')
        ->twice() // Once for trigger, once for success
        ->with(Mockery::type('string'), Mockery::type('array'));

    // Ignore any error logs (shouldn't be called in success case but just in case)
    Log::shouldReceive('error')->andReturn(null);

    $autoSubmitAction = app(AutoSubmitScanAction::class);
    $autoSubmitAction->handle($product, $product->barcode, $this->user->id);
});

it('includes ProcessBarcodeAction handleCameraDetection alias', function () {
    $product = Product::factory()->create(['barcode' => '5059031234567']);

    // Mock GetProductFromScannedBarcode
    $this->mock(\App\Actions\GetProductFromScannedBarcode::class, function ($mock) use ($product) {
        $mock->shouldReceive('handle')->andReturn($product);
    });

    $processBarcodeAction = app(\App\Actions\Scanner\ProcessBarcodeAction::class);
    $result = $processBarcodeAction->handleCameraDetection($product->barcode);

    expect($result->isValid)->toBeTrue();
    expect($result->product)->not->toBeNull();
    expect($result->product->sku)->toBe($product->sku);
});

it('user setting determines auto-submit enabled state', function () {
    // User with auto-submit enabled
    $userEnabled = User::factory()->create([
        'settings' => ['auto_submit' => true],
    ]);
    expect($userEnabled->settings['auto_submit'])->toBeTrue();

    // User with auto-submit disabled
    $userDisabled = User::factory()->create([
        'settings' => ['auto_submit' => false],
    ]);
    expect($userDisabled->settings['auto_submit'])->toBeFalse();

    // User with no setting should default to false
    $userDefault = User::factory()->create([
        'settings' => [],
    ]);
    expect($userDefault->settings['auto_submit'])->toBeFalse();
});

it('auto-submit creates decrease action by default', function () {
    $product = Product::factory()->create(['barcode' => '5059031234567']);

    // Mock Log to prevent errors
    Log::shouldReceive('info')->andReturn(null);
    Log::shouldReceive('error')->andReturn(null);

    // Mock ValidateScanDataAction
    $this->mock(ValidateScanDataAction::class, function ($mock) {
        $mock->shouldReceive('validateOrFail')->andReturnTrue();
    });

    // Mock CreateScanRecordAction
    $this->mock(CreateScanRecordAction::class, function ($mock) {
        $mock->shouldReceive('handle')->andReturnUsing(function ($scanData) {
            return Scan::create([
                'barcode' => $scanData->barcode,
                'quantity' => $scanData->quantity,
                'action' => $scanData->action,
                'user_id' => $scanData->userId,
                'submitted' => false,
                'sync_status' => 'pending',
            ]);
        });
    });

    $autoSubmitAction = app(AutoSubmitScanAction::class);
    $result = $autoSubmitAction->handle($product, $product->barcode, $this->user->id);

    expect($result['success'])->toBeTrue();

    $scan = Scan::find($result['scan_id']);
    expect($scan->action)->toBe('decrease');
    expect($scan->quantity)->toBe(1);
});
