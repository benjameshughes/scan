<?php

declare(strict_types=1);

use App\Actions\Scanner\CreateScanRecordAction;
use App\Actions\Scanner\ValidateScanDataAction;
use App\Livewire\Scanner\ScanForm;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $role = Role::firstOrCreate(['name' => 'admin']);
    Permission::firstOrCreate(['name' => 'view scanner']);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('validates quantity and action', function () {
    $product = Product::factory()->create(['barcode' => '5059031234567']);

    Livewire::test(ScanForm::class, [
        'barcode' => $product->barcode,
        'product' => $product,
    ])->set('form.quantity', 0)
        ->call('save')
        ->assertHasErrors(['form.quantity' => 'Quantity must be at least 1.']);
});

it('submits scan and dispatches event', function () {
    $product = Product::factory()->create(['barcode' => '5059031234567']);

    // Mock validation and creation actions
    $this->mock(ValidateScanDataAction::class, function ($mock) {
        $mock->shouldReceive('validateOrFail')->andReturnTrue();
    });
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

    Livewire::test(ScanForm::class, [
        'barcode' => $product->barcode,
        'product' => $product,
    ])->set('form.quantity', 2)
        ->set('form.scanAction', false)
        ->call('save')
        ->assertDispatched('scan-submitted');
});
