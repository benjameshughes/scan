<?php

use App\Services\Linnworks\LinnworksAuthenticator;
use App\Services\Linnworks\LinnworksHttpClient;
use App\Services\Linnworks\LinnworksInventoryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

describe('Linnworks Refactored Architecture', function () {

    beforeEach(function () {
        Config::set('linnworks', [
            'app_id' => 'test-app-id',
            'app_secret' => 'test-app-secret',
            'app_token' => 'test-app-token',
            'auth_url' => 'https://auth.linnworks.net/',
            'base_url' => 'https://api.linnworks.net/',
            'cache' => [
                'session_token_key' => 'test_linnworks_token'
            ],
            'pagination' => [
                'inventory_page_size' => 100,
                'search_page_size' => 50,
                'sync_page_size' => 200
            ],
            'default_location_id' => 'test-location-id'
        ]);

        Cache::flush();
        
        // Mock Log facade
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
    });

    test('architecture: classes can be instantiated without errors', function () {
        $authenticator = new LinnworksAuthenticator();
        $httpClient = new LinnworksHttpClient($authenticator);
        $inventoryService = new LinnworksInventoryService($httpClient);

        expect($authenticator)->toBeInstanceOf(LinnworksAuthenticator::class);
        expect($httpClient)->toBeInstanceOf(LinnworksHttpClient::class);
        expect($inventoryService)->toBeInstanceOf(LinnworksInventoryService::class);
    });

    test('authenticator: token cache management works', function () {
        $authenticator = new LinnworksAuthenticator();

        // Initially no token
        expect($authenticator->hasValidToken())->toBeFalse();
        expect($authenticator->getCachedToken())->toBeNull();

        // Set a token manually
        Cache::put('test_linnworks_token', 'test-token-123');

        // Now should detect the token
        expect($authenticator->hasValidToken())->toBeTrue();
        expect($authenticator->getCachedToken())->toBe('test-token-123');

        // Clear token
        $authenticator->clearToken();

        // Should be gone
        expect($authenticator->hasValidToken())->toBeFalse();
        expect($authenticator->getCachedToken())->toBeNull();
    });

    test('architecture: dependency injection works correctly', function () {
        $authenticator = new LinnworksAuthenticator();
        $httpClient = new LinnworksHttpClient($authenticator);
        $inventoryService = new LinnworksInventoryService($httpClient);

        // Test that the classes are properly connected
        // We can't test HTTP calls easily, but we can test that the dependency injection works

        // Using reflection to verify the internal structure
        $httpClientReflection = new ReflectionClass($httpClient);
        $authenticatorProperty = $httpClientReflection->getProperty('authenticator');
        $authenticatorProperty->setAccessible(true);
        
        expect($authenticatorProperty->getValue($httpClient))->toBe($authenticator);

        $inventoryReflection = new ReflectionClass($inventoryService);
        $httpClientProperty = $inventoryReflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        
        expect($httpClientProperty->getValue($inventoryService))->toBe($httpClient);
    });

    test('safety: inventory service has only read methods', function () {
        $inventoryService = new LinnworksInventoryService(
            new LinnworksHttpClient(new LinnworksAuthenticator())
        );

        $methods = array_filter(
            get_class_methods($inventoryService), 
            fn($method) => !str_starts_with($method, '__')
        );
        
        // Check that all methods are safe (read-only operations)
        $safeMethods = [
            'getInventory',
            'getInventoryCount',
            'searchStockItems',
            'getStockDetails',
            'getStockLevel',
            'getStockItemHistory',
            'getAllProducts',
            'getStockLocationsByProduct',
            'productExists',
            'getProductInfo'
        ];

        // Ensure no dangerous methods exist
        $dangerousMethods = [
            'updateStockLevel',
            'setStockLevel',
            'createProduct',
            'deleteProduct',
            'updateProduct',
            'transferStock'
        ];

        // Simply verify we have the expected number of safe methods
        expect(count($methods))->toBe(10, "Expected 10 methods, got " . count($methods));
        
        // Verify no dangerous methods exist
        foreach ($dangerousMethods as $method) {
            expect($methods)->not->toContain($method, "Found dangerous method: {$method}");
        }
        
        // Verify a few key safe methods exist
        expect($methods)->toContain('getInventory');
        expect($methods)->toContain('searchStockItems');
        expect($methods)->toContain('productExists');
    });

    test('architecture: classes are focused and have single responsibility', function () {
        // Test class method counts to ensure focused responsibility
        
        $authenticatorMethods = get_class_methods(LinnworksAuthenticator::class);
        $httpClientMethods = get_class_methods(LinnworksHttpClient::class);
        $inventoryMethods = get_class_methods(LinnworksInventoryService::class);

        // Authenticator should be focused on authentication only
        $authMethods = array_filter($authenticatorMethods, fn($method) => !str_starts_with($method, '__'));
        expect(count($authMethods))->toBeLessThan(10, 'Authenticator has too many methods - should be focused');

        // HttpClient should be focused on HTTP operations
        $httpMethods = array_filter($httpClientMethods, fn($method) => !str_starts_with($method, '__'));
        expect(count($httpMethods))->toBeLessThan(12, 'HttpClient has too many methods - should be focused');

        // InventoryService should be focused on read-only inventory operations
        $inventoryMethodsFiltered = array_filter($inventoryMethods, fn($method) => !str_starts_with($method, '__'));
        expect(count($inventoryMethodsFiltered))->toBeLessThan(15, 'InventoryService has too many methods - should be focused');
    });

    test('safety: no write operations in inventory service method names', function () {
        $inventoryService = new LinnworksInventoryService(
            new LinnworksHttpClient(new LinnworksAuthenticator())
        );

        $methods = get_class_methods($inventoryService);
        
        $writeKeywords = ['update', 'set', 'create', 'delete', 'insert', 'modify', 'change', 'edit', 'remove'];
        
        foreach ($methods as $method) {
            $methodLower = strtolower($method);
            foreach ($writeKeywords as $keyword) {
                expect($methodLower)->not->toContain($keyword, 
                    "Method '{$method}' contains write keyword '{$keyword}' - should be read-only");
            }
        }
    });

    test('configuration: all required config keys are properly loaded', function () {
        $authenticator = new LinnworksAuthenticator();
        $httpClient = new LinnworksHttpClient($authenticator);

        // Use reflection to verify config is loaded
        $authReflection = new ReflectionClass($authenticator);
        
        $appIdProperty = $authReflection->getProperty('appId');
        $appIdProperty->setAccessible(true);
        expect($appIdProperty->getValue($authenticator))->toBe('test-app-id');

        $cacheKeyProperty = $authReflection->getProperty('cacheKey');
        $cacheKeyProperty->setAccessible(true);
        expect($cacheKeyProperty->getValue($authenticator))->toBe('test_linnworks_token');

        $httpReflection = new ReflectionClass($httpClient);
        $baseUrlProperty = $httpReflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        expect($baseUrlProperty->getValue($httpClient))->toBe('https://api.linnworks.net/');
    });

    test('refactoring: improved separation of concerns', function () {
        // Original service had all responsibilities in one class
        // New architecture separates them properly

        $originalServiceMethods = get_class_methods(\App\Services\LinnworksApiService::class);
        $originalMethodCount = count(array_filter($originalServiceMethods, fn($m) => !str_starts_with($m, '__')));

        $authenticatorMethods = count(array_filter(
            get_class_methods(LinnworksAuthenticator::class), 
            fn($m) => !str_starts_with($m, '__')
        ));
        
        $httpClientMethods = count(array_filter(
            get_class_methods(LinnworksHttpClient::class), 
            fn($m) => !str_starts_with($m, '__')
        ));
        
        $inventoryMethods = count(array_filter(
            get_class_methods(LinnworksInventoryService::class), 
            fn($m) => !str_starts_with($m, '__')
        ));

        // Each refactored class should be smaller and more focused
        expect($authenticatorMethods)->toBeLessThan($originalMethodCount);
        expect($httpClientMethods)->toBeLessThan($originalMethodCount);
        expect($inventoryMethods)->toBeLessThan($originalMethodCount);

        // But together they should provide equivalent functionality
        $totalRefactoredMethods = $authenticatorMethods + $httpClientMethods + $inventoryMethods;
        expect($totalRefactoredMethods)->toBeGreaterThan($originalMethodCount * 0.8);
    });
});