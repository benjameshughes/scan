<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LinnworksApiService
{
    protected readonly string $appId;

    protected readonly string $appSecret;

    protected readonly string $appToken;

    protected readonly string $baseUrl;

    protected readonly string $authUrl;

    protected Client $client;

    protected string $cacheKey;

    public function __construct()
    {
        $this->client = new Client;
        $this->appId = config('linnworks.app_id');
        $this->appSecret = config('linnworks.app_secret');
        $this->appToken = config('linnworks.app_token');
        $this->baseUrl = config('linnworks.base_url');
        $this->authUrl = config('linnworks.auth_url');
        $this->cacheKey = config('linnworks.cache.session_token_key');

        $this->ensureAuthorized();
    }

    /**
     * Ensure we have a valid session token
     */
    private function ensureAuthorized(): string
    {
        if (Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        Log::channel('lw_auth')->info('No token in cache, authorizing with Linnworks');

        return $this->authorizeByApplication();
    }

    /**
     * Validate the cached token against a fresh one from the API
     */
    public function validateCachedToken(): bool
    {
        Log::channel('lw_auth')->info('Validating Linnworks token');

        $cachedToken = Cache::get($this->cacheKey);

        if (! $cachedToken) {
            Log::channel('lw_auth')->warning('No cached token found during validation');
            $this->authorizeByApplication();

            return false;
        }

        try {
            $freshToken = $this->getTokenFromApi();

            if ($freshToken === $cachedToken) {
                Log::channel('lw_auth')->info('Token validation successful - tokens match');

                return true;
            }

            Log::channel('lw_auth')->warning('Token mismatch detected, updating cached token');
            Cache::put($this->cacheKey, $freshToken);

            return false;
        } catch (Exception $e) {
            Log::channel('lw_auth')->error('Token validation failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get a token from the API without caching it
     */
    private function getTokenFromApi(): string
    {
        $body = [
            'ApplicationId' => $this->appId,
            'ApplicationSecret' => $this->appSecret,
            'Token' => $this->appToken,
        ];

        $response = $this->makeRequest('POST', $this->authUrl.'Auth/AuthorizeByApplication', [
            'body' => json_encode($body),
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        return $response['Token'];
    }

    /**
     * Authorize with Linnworks API and update the cache
     */
    private function authorizeByApplication(): string
    {
        try {
            $token = $this->getTokenFromApi();

            Cache::put($this->cacheKey, $token);
            Log::channel('lw_auth')->info('Authorized by application and updated cache');

            return $token;
        } catch (Exception $e) {
            Log::channel('lw_auth')->error('Authorization failed: '.$e->getMessage());
            throw new Exception('Unable to authorize by application: '.$e->getMessage());
        }
    }

    /**
     * Make an authenticated API request to Linnworks
     */
    private function makeAuthenticatedRequest(string $method, string $endpoint, array $options = []): array
    {
        $token = $this->ensureAuthorized();

        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => $token,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ]);

        try {
            return $this->makeRequest($method, $this->baseUrl.$endpoint, $options);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), '401')) {
                Log::channel('lw_auth')->warning('Received 401 error, refreshing token and retrying request');
                Cache::forget($this->cacheKey);
                $token = $this->authorizeByApplication();

                $options['headers']['Authorization'] = $token;

                return $this->makeRequest($method, $this->baseUrl.$endpoint, $options);
            }

            throw $e;
        }
    }

    /**
     * Make an authenticated request that returns plain text (not JSON)
     */
    private function makeAuthenticatedPlainTextRequest(string $method, string $endpoint): string
    {
        $token = $this->ensureAuthorized();

        $options = [
            'headers' => [
                'Authorization' => $token,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ];

        try {
            $response = $this->client->request($method, $this->baseUrl.$endpoint, $options);

            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            if (str_contains($e->getMessage(), '401')) {
                Log::channel('lw_auth')->warning('Received 401 error, refreshing token and retrying request');
                Cache::forget($this->cacheKey);
                $token = $this->authorizeByApplication();

                $options['headers']['Authorization'] = $token;
                $response = $this->client->request($method, $this->baseUrl.$endpoint, $options);

                return $response->getBody()->getContents();
            }

            throw new Exception("API request failed: {$e->getMessage()}");
        }
    }

    /**
     * Make a raw API request
     */
    private function makeRequest(string $method, string $url, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::channel('lw_auth')->error("API request failed: {$method} {$url} - ".$e->getMessage());

            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'No items found with given filter')) {
                throw new Exception('Product not found in Linnworks');
            }

            throw new Exception("API request failed: {$errorMessage}");
        }
    }

    /**
     * Refresh the session token
     */
    public function refreshToken(): string
    {
        Log::channel('lw_auth')->info('Manually refreshing token');
        Cache::forget($this->cacheKey);

        return $this->authorizeByApplication();
    }

    // =========================================================================
    // STOCK OPERATIONS
    // =========================================================================

    /**
     * Update stock level for a SKU at a specific location
     */
    public function updateStockLevel(string $sku, int $quantity, ?string $locationId = null): array
    {
        $body = [
            'stockLevels' => [
                [
                    'SKU' => $sku,
                    'LocationId' => $locationId ?? config('linnworks.default_location_id'),
                    'Level' => $quantity,
                ],
            ],
        ];

        return $this->makeAuthenticatedRequest(
            'POST',
            'Stock/SetStockLevel',
            ['body' => json_encode($body)]
        );
    }

    /**
     * Get stock level for a SKU
     */
    public function getStockLevel(string $sku): int
    {
        $data = $this->searchStockItems($sku, 1, ['StockLevels']);

        return $data[0]['StockLevels'][0]['StockLevel'];
    }

    /**
     * Transfer stock between two locations
     */
    public function transferStockBetweenLocations(
        string $sku,
        string $sourceLocationId,
        int $transferQuantity,
        ?string $targetLocationId = null
    ): array {
        $targetLocationId = $targetLocationId ?? config('linnworks.default_location_id');

        $stockItem = $this->getStockDetails($sku);
        if (empty($stockItem) || ! isset($stockItem['StockLevels'])) {
            throw new Exception("No stock information found for SKU: {$sku}");
        }

        $allLocations = $stockItem['StockLevels'];
        $sourceLocation = null;
        $targetLocation = null;

        foreach ($allLocations as $location) {
            $currentLocationId = $location['Location']['StockLocationId'] ?? null;
            if ($currentLocationId === $sourceLocationId) {
                $sourceLocation = $location;
            } elseif ($currentLocationId === $targetLocationId) {
                $targetLocation = $location;
            }
        }

        // Treat missing locations as having 0 stock
        $sourceCurrentStock = $sourceLocation['StockLevel'] ?? 0;
        $targetCurrentStock = $targetLocation['StockLevel'] ?? 0;

        $sourceNewStock = $sourceCurrentStock - $transferQuantity;
        $targetNewStock = $targetCurrentStock + $transferQuantity;

        if ($sourceNewStock < 0) {
            throw new Exception("Insufficient stock in source location. Available: {$sourceCurrentStock}, Requested: {$transferQuantity}");
        }

        $body = [
            'stockLevels' => [
                [
                    'SKU' => $sku,
                    'LocationId' => $sourceLocationId,
                    'Level' => $sourceNewStock,
                ],
                [
                    'SKU' => $sku,
                    'LocationId' => $targetLocationId,
                    'Level' => $targetNewStock,
                ],
            ],
        ];

        Log::channel('inventory')->info('Transferring stock between locations', [
            'sku' => $sku,
            'source' => $sourceLocationId,
            'target' => $targetLocationId,
            'quantity' => $transferQuantity,
            'source_stock' => "{$sourceCurrentStock} → {$sourceNewStock}",
            'target_stock' => "{$targetCurrentStock} → {$targetNewStock}",
        ]);

        $response = $this->makeAuthenticatedRequest(
            'POST',
            'Stock/SetStockLevel',
            ['body' => json_encode($body)]
        );

        Log::channel('inventory')->info('Stock transfer completed', [
            'sku' => $sku,
            'source' => $sourceLocationId,
            'target' => $targetLocationId,
        ]);

        return $response;
    }

    // =========================================================================
    // INVENTORY & PRODUCTS
    // =========================================================================

    /**
     * Get inventory with pagination
     */
    public function getInventory(int $pageNumber = 1, ?int $entriesPerPage = null): array
    {
        $entriesPerPage = $entriesPerPage ?? config('linnworks.pagination.inventory_page_size');

        $body = [
            'loadCompositeParents' => false,
            'loadVariationParents' => false,
            'entriesPerPage' => $entriesPerPage,
            'pageNumber' => $pageNumber,
            'dataRequirements' => ['StockLevels'],
        ];

        return $this->makeAuthenticatedRequest(
            'POST',
            'Stock/GetStockItemsFull',
            ['body' => json_encode($body)]
        );
    }

    /**
     * Get total inventory count
     */
    public function getInventoryCount(): int
    {
        $response = $this->makeAuthenticatedPlainTextRequest('GET', 'Inventory/GetInventoryItemsCount');
        $count = (int) $response;

        Log::info("Retrieved inventory count: {$count}");

        return $count;
    }

    /**
     * Get detailed stock information for a SKU
     */
    public function getStockDetails(string $sku): array
    {
        $data = $this->searchStockItems($sku, 1, ['StockLevels']);

        if (! empty($data)) {
            Log::channel('inventory')->info('Stock Details: '.($data[0]['ItemTitle'] ?? 'Not found'));

            return $data[0];
        }

        return [];
    }

    /**
     * Search for stock items
     */
    public function searchStockItems(
        string $keyword,
        ?int $entriesPerPage = null,
        array $dataRequirements = ['StockLevels'],
        array $searchTypes = ['SKU', 'Title', 'Barcode'],
        int $pageNumber = 1
    ): array {
        $entriesPerPage = $entriesPerPage ?? config('linnworks.pagination.search_page_size');

        $body = [
            'keyword' => trim($keyword),
            'loadCompositeParents' => false,
            'loadVariationParents' => false,
            'entriesPerPage' => $entriesPerPage,
            'pageNumber' => $pageNumber,
            'dataRequirements' => $dataRequirements,
            'searchTypes' => $searchTypes,
        ];

        return $this->makeAuthenticatedRequest(
            'POST',
            'Stock/GetStockItemsFull',
            ['body' => json_encode($body)]
        );
    }

    /**
     * Get stock item history for a SKU
     */
    public function getStockItemHistory(string $sku, int $page = 1, ?int $entriesPerPage = null): array
    {
        $entriesPerPage = $entriesPerPage ?? config('linnworks.stock_history.page_size');
        $locationId = config('linnworks.default_location_id');

        $itemDetail = $this->getStockDetails($sku);

        if (empty($itemDetail)) {
            Log::channel(config('linnworks.logging.inventory_channel'))->warning("SKU not found: {$sku}");

            return [];
        }

        $itemId = $itemDetail['StockItemId'];

        $endpoint = "Stock/GetItemChangesHistory?stockItemId={$itemId}&locationId={$locationId}&entriesPerPage={$entriesPerPage}&pageNumber={$page}";

        return $this->makeAuthenticatedRequest('GET', $endpoint);
    }

    /**
     * Get all products from Linnworks with pagination
     */
    public function getAllProducts(int $page = 1, ?int $entriesPerPage = null): array
    {
        $entriesPerPage = $entriesPerPage ?? config('linnworks.pagination.sync_page_size');

        try {
            $response = $this->searchStockItems('', $entriesPerPage, ['StockLevels'], ['SKU', 'Title', 'Barcode'], $page);

            Log::info('getAllProducts response', [
                'count' => count($response),
                'page' => $page,
            ]);

            return $response;
        } catch (Exception $e) {
            Log::error('Failed to get all products from Linnworks', [
                'error' => $e->getMessage(),
                'page' => $page,
            ]);

            return [];
        }
    }

    // =========================================================================
    // LOCATIONS
    // =========================================================================

    /**
     * Get all locations from Linnworks
     */
    public function getLocations(): array
    {
        $endpoints = [
            'Stock/GetStockLocationFull',
            'Stock/GetStockLocations',
            'Inventory/GetStockLocations',
            'Locations',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = $this->makeAuthenticatedRequest('GET', $endpoint);

                Log::channel('inventory')->info('Successfully retrieved locations', [
                    'count' => count($response),
                    'endpoint' => $endpoint,
                ]);

                return $response;
            } catch (Exception $e) {
                Log::channel('inventory')->warning("Endpoint {$endpoint} failed: {$e->getMessage()}");

                continue;
            }
        }

        throw new Exception('Failed to retrieve locations from any endpoint');
    }

    /**
     * Get stock levels for a product across all locations (only locations with stock > 0)
     */
    public function getStockLocationsByProduct(string $sku): array
    {
        $stockItem = $this->getStockDetails($sku);

        if (empty($stockItem) || ! isset($stockItem['StockLevels'])) {
            return [];
        }

        $locationsWithStock = array_filter($stockItem['StockLevels'], function ($location) {
            return isset($location['StockLevel']) && $location['StockLevel'] > 0;
        });

        Log::channel('inventory')->info("Found stock locations for SKU: {$sku}", [
            'locations_with_stock' => count($locationsWithStock),
            'total_locations' => count($stockItem['StockLevels']),
        ]);

        return array_values($locationsWithStock);
    }

    /**
     * Get ALL stock locations for a product (including locations with 0 stock)
     */
    public function getAllStockLocationsByProduct(string $sku): array
    {
        $stockItem = $this->getStockDetails($sku);

        if (empty($stockItem) || ! isset($stockItem['StockLevels'])) {
            return [];
        }

        return array_values($stockItem['StockLevels']);
    }
}
