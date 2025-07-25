<?php

use App\Livewire\ProductScanner;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('view scanner');
    
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'settings' => [
            'scan_sound' => true,
            'dark_mode' => false,
            'auto_submit' => false,
        ]
    ]);
    $this->user->givePermissionTo('view scanner');
    
    $this->product = Product::factory()->create([
        'barcode' => '5059031234567',
        'name' => 'Test Product'
    ]);
});

describe('User Settings Model', function () {
    test('settings accessor returns correct defaults', function () {
        $user = User::factory()->create(['settings' => null]); // No settings
        
        expect($user->settings)->toBe([
            'dark_mode' => false,
            'auto_submit' => false,
            'scan_sound' => true,
        ]);
    });

    test('settings accessor preserves false values', function () {
        $user = User::factory()->create([
            'settings' => [
                'scan_sound' => false,
                'dark_mode' => true,
                'auto_submit' => true,
            ]
        ]);
        
        expect($user->settings)->toBe([
            'dark_mode' => true,
            'auto_submit' => true,
            'scan_sound' => false,
        ]);
    });

    test('settings can be updated via array assignment', function () {
        $user = User::factory()->create();
        
        $user->update([
            'settings' => [
                'dark_mode' => true,
                'auto_submit' => false,
                'scan_sound' => false,
            ]
        ]);
        
        $user->refresh();
        
        expect($user->settings)->toBe([
            'dark_mode' => true,
            'auto_submit' => false,
            'scan_sound' => false,
        ]);
    });
});

describe('API Integration', function () {
    test('api endpoint returns user settings', function () {
        $response = $this->actingAs($this->user)
            ->get('/api/user/settings');
        
        $response->assertOk()
            ->assertJson([
                'scan_sound' => true,
                'dark_mode' => false,
                'auto_submit' => false,
            ]);
    });

    test('api endpoint requires authentication', function () {
        $response = $this->get('/api/user/settings');
        
        $response->assertRedirect('/login');
    });
});

describe('ProductScanner Integration', function () {
    test('scanner initializes with user auto-submit setting', function () {
        // User with auto-submit enabled
        $this->user->update([
            'settings' => ['auto_submit' => true, 'dark_mode' => false, 'scan_sound' => true]
        ]);
        
        $component = Livewire::actingAs($this->user)
            ->test(ProductScanner::class);
            
        expect($component->get('autoSubmitEnabled'))->toBeTrue();
    });

    test('scanner initializes with user auto-submit disabled', function () {
        // User with auto-submit disabled (default)
        $component = Livewire::actingAs($this->user)
            ->test(ProductScanner::class);
            
        expect($component->get('autoSubmitEnabled'))->toBeFalse();
    });

    test('sound setting affects scanner behavior', function () {
        // Test sound enabled
        Livewire::actingAs($this->user)
            ->test(ProductScanner::class)
            ->call('onBarcodeDetected', '5059031234567')
            ->assertSet('playSuccessSound', true);

        // Test sound disabled
        $this->user->update([
            'settings' => ['scan_sound' => false, 'dark_mode' => false, 'auto_submit' => false]
        ]);

        Livewire::actingAs($this->user)
            ->test(ProductScanner::class)
            ->call('onBarcodeDetected', '5059031234567')
            ->assertSet('playSuccessSound', false);
    });

    test('auto-submit logs when enabled and product found', function () {
        // Enable auto-submit
        $this->user->update([
            'settings' => ['auto_submit' => true, 'dark_mode' => false, 'scan_sound' => true]
        ]);

        // Clear previous logs
        \Log::spy();

        Livewire::actingAs($this->user)
            ->test(ProductScanner::class)
            ->call('onBarcodeDetected', '5059031234567');

        \Log::shouldHaveReceived('info')
            ->with('Auto-submit triggered', \Mockery::type('array'))
            ->once();
    });

    test('auto-submit does not trigger when disabled', function () {
        // Auto-submit disabled (default)
        \Log::spy();

        Livewire::actingAs($this->user)
            ->test(ProductScanner::class)
            ->call('onBarcodeDetected', '5059031234567');

        \Log::shouldNotHaveReceived('info', ['Auto-submit triggered', \Mockery::any()]);
    });
});

describe('Settings Integration', function () {
    test('all settings work together correctly', function () {
        // Create user with all settings enabled
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'settings' => [
                'scan_sound' => true,
                'dark_mode' => true,
                'auto_submit' => true,
            ]
        ]);
        $user->givePermissionTo('view scanner');

        // Test API endpoint
        $response = $this->actingAs($user)
            ->get('/api/user/settings');
        $response->assertOk()
            ->assertJson([
                'scan_sound' => true,
                'dark_mode' => true,
                'auto_submit' => true,
            ]);

        // Test scanner component
        $component = Livewire::actingAs($user)
            ->test(ProductScanner::class);
            
        expect($component->get('autoSubmitEnabled'))->toBeTrue();

        // Test sound and auto-submit together
        \Log::spy();
        
        $component->call('onBarcodeDetected', '5059031234567')
            ->assertSet('playSuccessSound', true);

        \Log::shouldHaveReceived('info')
            ->with('Auto-submit triggered', \Mockery::type('array'))
            ->once();
    });

    test('settings persist across requests', function () {
        // Update settings
        $this->user->update([
            'settings' => [
                'scan_sound' => false,
                'dark_mode' => true,
                'auto_submit' => true,
            ]
        ]);

        // Verify persistence with fresh user instance
        $freshUser = User::find($this->user->id);
        
        expect($freshUser->settings)->toBe([
            'dark_mode' => true,
            'auto_submit' => true,
            'scan_sound' => false,
        ]);
    });
});