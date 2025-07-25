<?php

use App\Services\Linnworks\LinnworksAuthenticator;
use App\Services\Linnworks\LinnworksHttpClient;
use App\Services\Linnworks\LinnworksInventoryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

describe('Refactored Linnworks Services', function () {

    beforeEach(function () {
        Config::set('linnworks', [
            'app_id' => 'test-app-id',
            'app_secret' => 'test-app-secret',
            'app_token' => 'test-app-token',
            'auth_url' => 'https://auth.linnworks.net/',
            'base_url' => 'https://api.linnworks.net/',
            'cache' => [
                'session_token_key' => 'linnworks_session_token',
            ],
            'pagination' => [
                'inventory_page_size' => 100,
                'search_page_size' => 50,
                'sync_page_size' => 200,
            ],
            'default_location_id' => 'test-location-id',
        ]);

        Cache::flush();

        // Mock Log facade to prevent errors
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
    });

    describe('LinnworksAuthenticator', function () {

        test('it can get a valid token from cache', function () {
            Cache::put('linnworks_session_token', 'cached-token');

            $authenticator = new LinnworksAuthenticator;
            $token = $authenticator->getValidToken();

            expect($token)->toBe('cached-token');
        });

        test('it checks if token exists', function () {
            Cache::put('linnworks_session_token', 'cached-token');

            $authenticator = new LinnworksAuthenticator;

            expect($authenticator->hasValidToken())->toBeTrue();
            expect($authenticator->getCachedToken())->toBe('cached-token');
        });

        test('it can clear token', function () {
            Cache::put('linnworks_session_token', 'cached-token');

            $authenticator = new LinnworksAuthenticator;
            $authenticator->clearToken();

            expect($authenticator->hasValidToken())->toBeFalse();
            expect($authenticator->getCachedToken())->toBeNull();
        });

        test('it refreshes token when cache is empty', function () {
            Http::fake([
                'auth.linnworks.net/Auth/AuthorizeByApplication' => Http::response([
                    'Token' => 'new-fresh-token',
                ], 200),
            ]);

            $authenticator = new LinnworksAuthenticator;
            $token = $authenticator->getValidToken();

            expect($token)->toBe('new-fresh-token');
            expect(Cache::get('linnworks_session_token'))->toBe('new-fresh-token');
        });

        test('it handles authentication failure gracefully', function () {
            Http::fake([
                'auth.linnworks.net/Auth/AuthorizeByApplication' => Http::response([], 401),
            ]);

            $authenticator = new LinnworksAuthenticator;

            expect(fn () => $authenticator->getValidToken())
                ->toThrow(Exception::class, 'Unable to authorize by application');
        });
    });

    describe('LinnworksHttpClient', function () {

        test('it can make authenticated requests', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/test-endpoint' => Http::response(['success' => true], 200),
            ]);

            $authenticator = new LinnworksAuthenticator;
            $httpClient = new LinnworksHttpClient($authenticator);

            $result = $httpClient->get('test-endpoint');

            expect($result)->toBe(['success' => true]);

            Http::assertSent(function ($request) {
                return $request->hasHeader('Authorization', 'valid-token') &&
                       $request->url() === 'https://api.linnworks.net/test-endpoint';
            });
        });

        test('it provides convenience methods', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/post-endpoint' => Http::response(['posted' => true], 200),
                'api.linnworks.net/put-endpoint' => Http::response(['updated' => true], 200),
                'api.linnworks.net/delete-endpoint' => Http::response(['deleted' => true], 200),
            ]);

            $authenticator = new LinnworksAuthenticator;
            $httpClient = new LinnworksHttpClient($authenticator);

            $postResult = $httpClient->post('post-endpoint', ['data' => 'test']);
            $putResult = $httpClient->put('put-endpoint', ['data' => 'updated']);
            $deleteResult = $httpClient->delete('delete-endpoint');

            expect($postResult)->toBe(['posted' => true]);
            expect($putResult)->toBe(['updated' => true]);
            expect($deleteResult)->toBe(['deleted' => true]);
        });

        test('it handles 401 errors by refreshing token', function () {
            Cache::put('linnworks_session_token', 'expired-token');

            Http::fake([
                'api.linnworks.net/protected-endpoint' => Http::sequence()
                    ->push([], 401) // First request fails
                    ->push(['success' => true], 200), // Second request succeeds
                'auth.linnworks.net/Auth/AuthorizeByApplication' => Http::response([
                    'Token' => 'refreshed-token',
                ], 200),
            ]);

            $authenticator = new LinnworksAuthenticator;
            $httpClient = new LinnworksHttpClient($authenticator);

            $result = $httpClient->get('protected-endpoint');

            expect($result)->toBe(['success' => true]);
            expect(Cache::get('linnworks_session_token'))->toBe('refreshed-token');
        });
    });

    describe('LinnworksInventoryService (READ-ONLY)', function () {

        test('it can search for stock items safely', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/Stock/GetStockItemsFull' => Http::response([
                    ['SKU' => 'SEARCH-SKU', 'ItemTitle' => 'Found Product'],
                ], 200),
            ]);

            $authenticator = new LinnworksAuthenticator;
            $httpClient = new LinnworksHttpClient($authenticator);
            $inventoryService = new LinnworksInventoryService($httpClient);

            $result = $inventoryService->searchStockItems('search-term');

            expect($result)->toHaveCount(1);
            expect($result[0]['SKU'])->toBe('SEARCH-SKU');

            Http::assertSent(function ($request) {
                $body = json_decode($request->body(), true);

                return $request->url() === 'https://api.linnworks.net/Stock/GetStockItemsFull' &&
                       $body['keyword'] === 'search-term' &&
                       $request->method() === 'POST';
            });
        });

        test('it can get stock details safely', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/Stock/GetStockItemsFull' => Http::response([
                    [
                        'SKU' => 'TEST-SKU',
                        'ItemTitle' => 'Test Product',
                        'StockLevels' => [['StockLevel' => 50]],
                    ],
                ], 200),
            ]);

            $authenticator = new LinnworksAuthenticator;
            $httpClient = new LinnworksHttpClient($authenticator);
            $inventoryService = new LinnworksInventoryService($httpClient);

            $details = $inventoryService->getStockDetails('TEST-SKU');
            $stockLevel = $inventoryService->getStockLevel('TEST-SKU');

            expect($details['SKU'])->toBe('TEST-SKU');
            expect($details['ItemTitle'])->toBe('Test Product');
            expect($stockLevel)->toBe(50);
        });

        test('it validates product existence safely', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/Stock/GetStockItemsFull' => Http::sequence()
                    ->push([['SKU' => 'EXISTS']], 200) // Product exists
                    ->push([], 200), // Product doesn't exist
            ]);

            $authenticator = new LinnworksAuthenticator;
            $httpClient = new LinnworksHttpClient($authenticator);
            $inventoryService = new LinnworksInventoryService($httpClient);

            expect($inventoryService->productExists('EXISTS'))->toBeTrue();
            expect($inventoryService->productExists('NOT-EXISTS'))->toBeFalse();
        });

        test('it gets product info safely', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/Stock/GetStockItemsFull' => Http::response([
                    [
                        'SKU' => 'INFO-SKU',
                        'ItemTitle' => 'Info Product',
                        'StockItemId' => '12345',
                        'StockLevels' => [['Location' => 'Default', 'StockLevel' => 25]],
                    ],
                ], 200),
            ]);

            $authenticator = new LinnworksAuthenticator;
            $httpClient = new LinnworksHttpClient($authenticator);
            $inventoryService = new LinnworksInventoryService($httpClient);

            $info = $inventoryService->getProductInfo('INFO-SKU');

            expect($info['sku'])->toBe('INFO-SKU');
            expect($info['title'])->toBe('Info Product');
            expect($info['stock_item_id'])->toBe('12345');
            expect($info['stock_levels'])->toHaveCount(1);
        });

        test('it handles errors gracefully', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/Stock/GetStockItemsFull' => Http::response([], 500),
            ]);

            $authenticator = new LinnworksAuthenticator;
            $httpClient = new LinnworksHttpClient($authenticator);
            $inventoryService = new LinnworksInventoryService($httpClient);

            expect($inventoryService->productExists('ERROR-SKU'))->toBeFalse();
            expect($inventoryService->getProductInfo('ERROR-SKU'))->toBeNull();
        });
    });

    test('integration: services work together', function () {
        Cache::put('linnworks_session_token', 'valid-token');

        Http::fake([
            'api.linnworks.net/Stock/GetStockItemsFull' => Http::response([
                [
                    'SKU' => 'INTEGRATION-SKU',
                    'ItemTitle' => 'Integration Test Product',
                    'StockLevels' => [['StockLevel' => 100]],
                ],
            ], 200),
        ]);

        // Create services
        $authenticator = new LinnworksAuthenticator;
        $httpClient = new LinnworksHttpClient($authenticator);
        $inventoryService = new LinnworksInventoryService($httpClient);

        // Test the complete flow
        expect($authenticator->hasValidToken())->toBeTrue();

        $products = $inventoryService->searchStockItems('INTEGRATION-SKU');
        expect($products)->toHaveCount(1);

        $stockLevel = $inventoryService->getStockLevel('INTEGRATION-SKU');
        expect($stockLevel)->toBe(100);

        $info = $inventoryService->getProductInfo('INTEGRATION-SKU');
        expect($info['title'])->toBe('Integration Test Product');
    });
});
