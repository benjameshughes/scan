<?php

use App\Livewire\ProductScanner;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create admin role and permissions
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $viewScannerPermission = Permission::firstOrCreate(['name' => 'view scanner']);
    $refillBaysPermission = Permission::firstOrCreate(['name' => 'refill bays']);
    
    $adminRole->givePermissionTo([$viewScannerPermission, $refillBaysPermission]);
    
    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('admin');
    
    // Create regular user
    $this->regularUser = User::factory()->create();
    $this->regularUser->givePermissionTo('view scanner');
    
    // Create test product
    $this->product = Product::factory()->create([
        'name' => 'Test Refill Product',
        'sku' => 'TEST-REFILL-001',
        'barcode' => '5059031234567',
    ]);
    
    // Mock Linnworks service
    $this->mockLinnworksService = $this->mock(LinnworksApiService::class);
});

describe('ProductScanner Refill Functionality', function () {

    describe('Admin Permissions', function () {
        beforeEach(function () {
            $this->actingAs($this->adminUser);
        });

        test('admin can access scanner with proper permissions', function () {
            Livewire::test(ProductScanner::class)
                ->assertStatus(200)
                ->assertSet('isEmailRefill', false)
                ->assertSet('showRefillForm', false);
        });

        test('admin can show refill bay form', function () {
            // Mock Linnworks locations response
            $mockLocations = [
                [
                    'Location' => [
                        'StockLocationId' => 'loc-001',
                        'LocationName' => 'Warehouse A',
                        'LocationCode' => 'WH-A'
                    ],
                    'StockLevel' => 50,
                    'Quantity' => 50
                ],
                [
                    'Location' => [
                        'StockLocationId' => 'loc-002', 
                        'LocationName' => 'Warehouse B',
                        'LocationCode' => 'WH-B'
                    ],
                    'StockLevel' => 25,
                    'Quantity' => 25
                ]
            ];

            $this->mockLinnworksService
                ->shouldReceive('getStockLocationsByProduct')
                ->with('TEST-REFILL-001')
                ->once()
                ->andReturn($mockLocations);

            Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->call('showRefillBayForm')
                ->assertSet('showRefillForm', true)
                ->assertSet('isProcessingRefill', false)
                ->assertSet('refillError', '')
                ->assertCount('availableLocations', 2);
        });

        test('refill form shows correct max stock property', function () {
            $mockLocations = [
                [
                    'Location' => [
                        'StockLocationId' => 'loc-001',
                        'LocationName' => 'Warehouse A'
                    ],
                    'StockLevel' => 100
                ]
            ];

            $component = Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->set('availableLocations', $mockLocations)
                ->set('selectedLocationId', 'loc-001');

            expect($component->get('maxRefillStock'))->toBe(100);
        });

        test('refill quantity validation prevents exceeding max stock', function () {
            $mockLocations = [
                [
                    'Location' => [
                        'StockLocationId' => 'loc-001',
                        'LocationName' => 'Warehouse A'
                    ],
                    'StockLevel' => 10
                ]
            ];

            Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->set('availableLocations', $mockLocations)
                ->set('selectedLocationId', 'loc-001')
                ->set('refillQuantity', 15) // Try to exceed max
                ->assertSet('refillQuantity', 10) // Should be auto-corrected
                ->assertHasErrors('refillQuantity');
        });

        test('increment refill quantity respects max stock limit', function () {
            $mockLocations = [
                [
                    'Location' => [
                        'StockLocationId' => 'loc-001',
                        'LocationName' => 'Warehouse A'
                    ],
                    'StockLevel' => 5
                ]
            ];

            Livewire::test(ProductScanner::class)
                ->set('availableLocations', $mockLocations)
                ->set('selectedLocationId', 'loc-001')
                ->set('refillQuantity', 5)
                ->call('incrementRefillQuantity')
                ->assertSet('refillQuantity', 5); // Should not increase beyond max
        });

        test('decrement refill quantity respects minimum limit', function () {
            Livewire::test(ProductScanner::class)
                ->set('refillQuantity', 1)
                ->call('decrementRefillQuantity')
                ->assertSet('refillQuantity', 1); // Should not go below 1
        });

        test('location change resets quantity to safe value', function () {
            Livewire::test(ProductScanner::class)
                ->set('refillQuantity', 10)
                ->dispatch('locationChanged', 'new-location-id')
                ->assertSet('selectedLocationId', 'new-location-id')
                ->assertSet('refillQuantity', 1); // Should reset to 1
        });

        test('successful refill submission creates stock movement', function () {
            $mockLocations = [
                [
                    'Location' => [
                        'StockLocationId' => 'loc-001',
                        'LocationName' => 'Warehouse A',
                        'LocationCode' => 'WH-A'
                    ],
                    'StockLevel' => 50
                ]
            ];

            $this->mockLinnworksService
                ->shouldReceive('transferStockToDefaultLocation')
                ->with('TEST-REFILL-001', 'loc-001', 5)
                ->once()
                ->andReturn(['success' => true]);

            Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->set('availableLocations', $mockLocations)
                ->set('selectedLocationId', 'loc-001')
                ->set('refillQuantity', 5)
                ->call('submitRefill')
                ->assertSet('showSuccessMessage', true)
                ->assertSet('isScanning', true); // Should resume scanning after success

            // Verify stock movement was created
            expect(StockMovement::count())->toBe(1);
            $movement = StockMovement::first();
            expect($movement->product_id)->toBe($this->product->id);
            expect($movement->quantity)->toBe(5);
            expect($movement->type)->toBe(StockMovement::TYPE_BAY_REFILL);
        });

        test('refill submission validates against available stock', function () {
            $mockLocations = [
                [
                    'Location' => [
                        'StockLocationId' => 'loc-001',
                        'LocationName' => 'Warehouse A'
                    ],
                    'StockLevel' => 3 // Only 3 available
                ]
            ];

            // Test that real-time validation auto-corrects excessive quantities
            Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->set('availableLocations', $mockLocations)
                ->set('selectedLocationId', 'loc-001')
                ->set('refillQuantity', 5) // Try to set more than available
                ->assertSet('refillQuantity', 3) // Should be auto-corrected to max available
                ->assertHasErrors('refillQuantity'); // Should have validation error message
        });

        test('cancel refill resets form state', function () {
            Livewire::test(ProductScanner::class)
                ->set('showRefillForm', true)
                ->set('selectedLocationId', 'loc-001')
                ->set('refillQuantity', 5)
                ->set('refillError', 'Some error')
                ->call('cancelRefill')
                ->assertSet('showRefillForm', false)
                ->assertSet('selectedLocationId', '')
                ->assertSet('refillQuantity', 1)
                ->assertSet('refillError', '');
        });

        test('email refill workflow sets proper state', function () {
            // Simulate email refill request
            $this->mockLinnworksService
                ->shouldReceive('getStockLocationsByProduct')
                ->andReturn([]);

            $component = Livewire::test(ProductScanner::class)
                ->set('isEmailRefill', true)
                ->set('barcode', $this->product->barcode)
                ->set('product', $this->product);

            expect($component->get('isEmailRefill'))->toBeTrue();
        });
    });

    describe('Permission Restrictions', function () {
        beforeEach(function () {
            $this->actingAs($this->regularUser); // Regular user without refill permission
        });

        test('regular user cannot show refill bay form', function () {
            Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->call('showRefillBayForm')
                ->assertSet('refillError', 'You do not have permission to refill bays.')
                ->assertSet('showRefillForm', false);
        });

        test('regular user cannot submit refill', function () {
            Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->set('selectedLocationId', 'loc-001')
                ->set('refillQuantity', 5)
                ->call('submitRefill')
                ->assertHasErrors(); // Should fail validation due to permissions
        });
    });

    describe('Error Handling', function () {
        beforeEach(function () {
            $this->actingAs($this->adminUser);
        });

        test('handles no locations available error', function () {
            $this->mockLinnworksService
                ->shouldReceive('getStockLocationsByProduct')
                ->with('TEST-REFILL-001')
                ->once()
                ->andReturn([]); // Empty locations

            Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->call('showRefillBayForm')
                ->assertSet('refillError', 'No locations with stock found for this product.')
                ->assertSet('showRefillForm', false);
        });

        test('handles linnworks api errors gracefully', function () {
            $this->mockLinnworksService
                ->shouldReceive('getStockLocationsByProduct')
                ->with('TEST-REFILL-001')
                ->once()
                ->andThrow(new \Exception('API connection failed'));

            Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->call('showRefillBayForm')
                ->assertSet('refillError', 'Failed to load locations: API connection failed')
                ->assertSet('showRefillForm', false);
        });

        test('clear refill error resets error message', function () {
            Livewire::test(ProductScanner::class)
                ->set('refillError', 'Test error message')
                ->call('clearRefillError')
                ->assertSet('refillError', '');
        });

        test('handles missing product for refill', function () {
            Livewire::test(ProductScanner::class)
                ->set('product', null)
                ->call('showRefillBayForm')
                ->assertSet('refillError', 'No product selected for refill.')
                ->assertSet('showRefillForm', false);
        });
    });

    describe('Auto-Selection Logic', function () {
        beforeEach(function () {
            $this->actingAs($this->adminUser);
        });

        test('auto-selects single non-default location', function () {
            config(['linnworks.default_location_id' => 'default-loc']);

            $mockLocations = [
                [
                    'Location' => [
                        'StockLocationId' => 'default-loc',
                        'LocationName' => 'Default Location'
                    ],
                    'StockLevel' => 100
                ],
                [
                    'Location' => [
                        'StockLocationId' => 'only-option',
                        'LocationName' => 'Only Option'
                    ],
                    'StockLevel' => 50
                ]
            ];

            $this->mockLinnworksService
                ->shouldReceive('getStockLocationsByProduct')
                ->with('TEST-REFILL-001')
                ->once()
                ->andReturn($mockLocations);

            Livewire::test(ProductScanner::class)
                ->set('product', $this->product)
                ->call('showRefillBayForm')
                ->assertSet('selectedLocationId', 'only-option'); // Should auto-select non-default
        });
    });

    describe('Integration Tests', function () {
        beforeEach(function () {
            $this->actingAs($this->adminUser);
        });

        test('complete refill workflow from scan to completion', function () {
            $mockLocations = [
                [
                    'Location' => [
                        'StockLocationId' => 'loc-001',
                        'LocationName' => 'Test Location',
                        'LocationCode' => 'TEST-LOC'
                    ],
                    'StockLevel' => 25
                ]
            ];

            $this->mockLinnworksService
                ->shouldReceive('getStockLocationsByProduct')
                ->with('TEST-REFILL-001')
                ->once()
                ->andReturn($mockLocations);

            $this->mockLinnworksService
                ->shouldReceive('transferStockToDefaultLocation')
                ->with('TEST-REFILL-001', 'loc-001', 3)
                ->once()
                ->andReturn(['success' => true]);

            // Complete workflow
            $component = Livewire::test(ProductScanner::class)
                // 1. Scan product
                ->set('barcode', $this->product->barcode)
                ->set('product', $this->product)
                ->assertSet('barcodeScanned', true)
                
                // 2. Open refill form
                ->call('showRefillBayForm')
                ->assertSet('showRefillForm', true)
                
                // 3. Select location and quantity
                ->dispatch('locationChanged', 'loc-001')
                ->assertSet('selectedLocationId', 'loc-001')
                ->set('refillQuantity', 3)
                
                // 4. Submit refill
                ->call('submitRefill')
                ->assertSet('showSuccessMessage', true)
                ->assertSet('isScanning', true); // Should resume scanning

            // Verify final state
            expect(StockMovement::count())->toBe(1);
            expect($component->get('showRefillForm'))->toBeFalse();
            expect($component->get('selectedLocationId'))->toBe('');
        });
    });
});