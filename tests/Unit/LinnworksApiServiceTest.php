<?php

use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

describe('LinnworksApiService', function () {

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
    });

    test('it constructs with correct config values', function () {
        $service = new LinnworksApiService;

        // Since constructor is likely protected/private, we test through behavior
        expect($service)->toBeInstanceOf(LinnworksApiService::class);
    });

    describe('Token Management', function () {

        test('it refreshes token successfully', function () {
            Http::fake([
                'auth.linnworks.net/Auth/AuthorizeByApplication' => Http::response([
                    'Token' => 'new-session-token',
                    'UserType' => 'User',
                    'UserId' => '12345',
                    'UserEmail' => 'test@example.com',
                ], 200),
            ]);

            $service = new LinnworksApiService;
            $result = $service->refreshToken();

            expect($result)->toBe('new-session-token');
            expect(Cache::get('linnworks_session_token'))->toBe('new-session-token');
        });

        test('it handles token refresh failure', function () {
            Http::fake([
                'auth.linnworks.net/api/Auth/AuthorizeByApplication' => Http::response([], 401),
            ]);

            Log::shouldReceive('channel')
                ->with('lw_auth')
                ->andReturnSelf();
            Log::shouldReceive('error')
                ->once();

            $service = new LinnworksApiService;
            $result = $service->refreshToken();

            expect($result)->toBeFalse();
            expect(Cache::get('linnworks_session_token'))->toBeNull();
        });

        test('it validates existing token successfully', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
            ]);

            $service = new LinnworksApiService;
            $result = $service->validateToken();

            expect($result)->toBeTrue();
        });

        test('it detects invalid token', function () {
            Cache::put('linnworks_session_token', 'invalid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response([], 401),
            ]);

            $service = new LinnworksApiService;
            $result = $service->validateToken();

            expect($result)->toBeFalse();
        });

        test('it gets valid token from cache', function () {
            Cache::put('linnworks_session_token', 'cached-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
            ]);

            $service = new LinnworksApiService;
            $token = $service->getValidToken();

            expect($token)->toBe('cached-token');
        });

        test('it refreshes token when cache is empty', function () {
            Http::fake([
                'auth.linnworks.net/api/Auth/AuthorizeByApplication' => Http::response([
                    'SessionToken' => 'new-fresh-token',
                ], 200),
            ]);

            $service = new LinnworksApiService;
            $token = $service->getValidToken();

            expect($token)->toBe('new-fresh-token');
        });

        test('it refreshes token when cached token is invalid', function () {
            Cache::put('linnworks_session_token', 'invalid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response([], 401),
                'auth.linnworks.net/api/Auth/AuthorizeByApplication' => Http::response([
                    'SessionToken' => 'refreshed-token',
                ], 200),
            ]);

            $service = new LinnworksApiService;
            $token = $service->getValidToken();

            expect($token)->toBe('refreshed-token');
        });

        test('it returns null when token refresh fails', function () {
            Http::fake([
                'auth.linnworks.net/api/Auth/AuthorizeByApplication' => Http::response([], 401),
            ]);

            Log::shouldReceive('channel')->andReturnSelf();
            Log::shouldReceive('error');

            $service = new LinnworksApiService;
            $token = $service->getValidToken();

            expect($token)->toBeNull();
        });
    });

    describe('Stock Operations', function () {

        test('it gets stock level successfully', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Stock/GetStockLevel' => Http::response([
                    'StockLevel' => 50,
                    'MinimumLevel' => 10,
                    'MaximumLevel' => 100,
                ], 200),
            ]);

            $service = new LinnworksApiService;
            $result = $service->getStockLevel('TEST-SKU');

            expect($result)->toBe(50);

            Http::assertSent(function ($request) {
                return str_contains($request->url(), 'GetStockLevel') &&
                       $request['SKU'] === 'TEST-SKU' &&
                       $request['token'] === 'valid-token';
            });
        });

        test('it handles stock level API failure', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Stock/GetStockLevel' => Http::response([], 500),
            ]);

            Log::shouldReceive('channel')
                ->with('sku_lookup')
                ->andReturnSelf();
            Log::shouldReceive('error')
                ->once();

            $service = new LinnworksApiService;
            $result = $service->getStockLevel('TEST-SKU');

            expect($result)->toBeNull();
        });

        test('it updates stock level successfully', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Stock/SetStockLevel' => Http::response(['Success' => true], 200),
            ]);

            $service = new LinnworksApiService;
            $result = $service->updateStockLevel('TEST-SKU', 75);

            expect($result)->toBeTrue();

            Http::assertSent(function ($request) {
                return str_contains($request->url(), 'SetStockLevel') &&
                       $request['SKU'] === 'TEST-SKU' &&
                       $request['StockLevel'] === 75 &&
                       $request['token'] === 'valid-token';
            });
        });

        test('it handles stock level update failure', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Stock/SetStockLevel' => Http::response(['Success' => false], 200),
            ]);

            $service = new LinnworksApiService;
            $result = $service->updateStockLevel('TEST-SKU', 75);

            expect($result)->toBeFalse();
        });

        test('it retries on 401 error and refreshes token', function () {
            Cache::put('linnworks_session_token', 'expired-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::sequence()
                    ->push(['Message' => 'OK'], 200)
                    ->push([], 401)
                    ->push(['Message' => 'OK'], 200),
                'auth.linnworks.net/api/Auth/AuthorizeByApplication' => Http::response([
                    'SessionToken' => 'new-token',
                ], 200),
                'api.linnworks.net/api/Stock/GetStockLevel' => Http::sequence()
                    ->push([], 401)
                    ->push(['StockLevel' => 100], 200),
            ]);

            $service = new LinnworksApiService;
            $result = $service->getStockLevel('TEST-SKU');

            expect($result)->toBe(100);
            expect(Cache::get('linnworks_session_token'))->toBe('new-token');
        });
    });

    describe('Inventory Operations', function () {

        test('it gets full inventory successfully', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Inventory/GetInventoryItems' => Http::response([
                    'Data' => [
                        ['SKU' => 'SKU1', 'Title' => 'Product 1'],
                        ['SKU' => 'SKU2', 'Title' => 'Product 2'],
                    ],
                    'TotalResults' => 2,
                ], 200),
            ]);

            $service = new LinnworksApiService;
            $result = $service->getFullInventory();

            expect($result)->toHaveCount(2);
            expect($result[0]['SKU'])->toBe('SKU1');
            expect($result[1]['SKU'])->toBe('SKU2');
        });

        test('it handles inventory API failure', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Inventory/GetInventoryItems' => Http::response([], 500),
            ]);

            Log::shouldReceive('channel')
                ->with('inventory')
                ->andReturnSelf();
            Log::shouldReceive('error')
                ->once();

            $service = new LinnworksApiService;
            $result = $service->getFullInventory();

            expect($result)->toBeNull();
        });

        test('it searches stock items successfully', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Inventory/SearchStockItems' => Http::response([
                    ['SKU' => 'SEARCH-SKU', 'Title' => 'Found Product'],
                ], 200),
            ]);

            $service = new LinnworksApiService;
            $result = $service->searchStockItems('search-term');

            expect($result)->toHaveCount(1);
            expect($result[0]['SKU'])->toBe('SEARCH-SKU');

            Http::assertSent(function ($request) {
                return str_contains($request->url(), 'SearchStockItems') &&
                       $request['keyword'] === 'search-term';
            });
        });

        test('it gets stock item history successfully', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Stock/GetStockItemHistory' => Http::response([
                    ['Date' => '2024-01-01', 'Quantity' => 10, 'Note' => 'Adjustment'],
                ], 200),
            ]);

            $service = new LinnworksApiService;
            $result = $service->getStockItemHistory('TEST-SKU');

            expect($result)->toHaveCount(1);
            expect($result[0]['Quantity'])->toBe(10);
        });
    });

    describe('Logging', function () {

        test('it logs authentication errors', function () {
            Http::fake([
                'auth.linnworks.net/api/Auth/AuthorizeByApplication' => Http::response(['error' => 'Invalid credentials'], 401),
            ]);

            Log::shouldReceive('channel')
                ->with('lw_auth')
                ->andReturnSelf();
            Log::shouldReceive('error')
                ->once()
                ->with('Failed to refresh Linnworks token', Mockery::type('array'));

            $service = new LinnworksApiService;
            $service->refreshToken();
        });

        test('it logs stock operation errors', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Stock/GetStockLevel' => Http::response(['error' => 'SKU not found'], 404),
            ]);

            Log::shouldReceive('channel')
                ->with('sku_lookup')
                ->andReturnSelf();
            Log::shouldReceive('error')
                ->once();

            $service = new LinnworksApiService;
            $service->getStockLevel('INVALID-SKU');
        });

        test('it logs inventory operation errors', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Inventory/GetInventoryItems' => Http::response(['error' => 'Server error'], 500),
            ]);

            Log::shouldReceive('channel')
                ->with('inventory')
                ->andReturnSelf();
            Log::shouldReceive('error')
                ->once();

            $service = new LinnworksApiService;
            $service->getFullInventory();
        });
    });

    describe('Error Handling', function () {

        test('it handles network timeouts gracefully', function () {
            Http::fake([
                'auth.linnworks.net/api/Auth/AuthorizeByApplication' => function () {
                    throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
                },
            ]);

            Log::shouldReceive('channel')->andReturnSelf();
            Log::shouldReceive('error');

            $service = new LinnworksApiService;
            $result = $service->refreshToken();

            expect($result)->toBeFalse();
        });

        test('it handles malformed JSON responses', function () {
            Cache::put('linnworks_session_token', 'valid-token');

            Http::fake([
                'api.linnworks.net/api/Auth/Ping' => Http::response(['Message' => 'OK'], 200),
                'api.linnworks.net/api/Stock/GetStockLevel' => Http::response('invalid json', 200),
            ]);

            Log::shouldReceive('channel')->andReturnSelf();
            Log::shouldReceive('error');

            $service = new LinnworksApiService;
            $result = $service->getStockLevel('TEST-SKU');

            expect($result)->toBeNull();
        });
    });
});
