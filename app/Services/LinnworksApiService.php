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
        // Simply return the cached token if it exists
        if (Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        // If no token in cache, get a new one
        Log::channel('lw_auth')->info('No token in cache, authorizing with Linnworks');

        return $this->authorizeByApplication();
    }

    /**
     * Validate the cached token against a fresh one from the API
     * This method should be called from your scheduled task
     */
    public function validateCachedToken(): bool
    {
        Log::channel('lw_auth')->info('Validating Linnworks token');

        // Get the cached token
        $cachedToken = Cache::get($this->cacheKey);

        if (! $cachedToken) {
            Log::channel('lw_auth')->warning('No cached token found during validation');
            $this->authorizeByApplication();

            return false;
        }

        try {
            // Get a fresh token from the API without updating the cache
            $freshToken = $this->getTokenFromApi();

            // Compare the tokens
            if ($freshToken === $cachedToken) {
                Log::channel('lw_auth')->info('Token validation successful - tokens match');

                return true;
            } else {
                Log::channel('lw_auth')->warning('Token mismatch detected, updating cached token');
                Cache::put($this->cacheKey, $freshToken);

                return false;
            }
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

            // Store the session token in the cache
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
    protected function makeAuthenticatedRequest(string $method, string $endpoint, array $options = []): array
    {
        $token = $this->ensureAuthorized();

        // Add authorization header
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => $token,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ]);

        try {
            return $this->makeRequest($method, $this->baseUrl.$endpoint, $options);
        } catch (Exception $e) {
            // If we get a 401, try to refresh the token and retry once
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
     * Make a raw API request
     */
    protected function makeRequest(string $method, string $url, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::channel('lw_auth')->error("API request failed: {$method} {$url} - ".$e->getMessage());

            // Transform specific Linnworks error messages to user-friendly messages
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

    /**
     * Update stock level for a SKU
     */
    public function updateStockLevel(string $sku, int $quantity): array
    {
        $body = [
            'stockLevels' => [
                [
                    'SKU' => $sku,
                    'LocationId' => config('linnworks.default_location_id'),
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
        $token = $this->ensureAuthorized();

        try {
            $response = $this->client->request('GET', $this->baseUrl.'Inventory/GetInventoryItemsCount', [
                'headers' => [
                    'Authorization' => $token,
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            // This endpoint returns a plain number, not JSON
            $count = (int) $response->getBody()->getContents();

            Log::info("Retrieved inventory count: {$count}");

            return $count;

        } catch (GuzzleException $e) {
            Log::channel('lw_auth')->error('Failed to get inventory count: '.$e->getMessage());

            // If we get a 401, try to refresh the token and retry once
            if (str_contains($e->getMessage(), '401')) {
                Log::channel('lw_auth')->warning('Received 401 error, refreshing token and retrying inventory count');
                Cache::forget($this->cacheKey);
                $token = $this->authorizeByApplication();

                $response = $this->client->request('GET', $this->baseUrl.'Inventory/GetInventoryItemsCount', [
                    'headers' => [
                        'Authorization' => $token,
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ],
                ]);

                return (int) $response->getBody()->getContents();
            }

            throw new Exception('Failed to get inventory count: '.$e->getMessage());
        }
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
        Log::channel(config('linnworks.logging.inventory_channel'))->info("{$sku} - {$itemId} for stock item history search");

        $endpoint = "Stock/GetItemChangesHistory?stockItemId={$itemId}&locationId={$locationId}&entriesPerPage={$entriesPerPage}&pageNumber={$page}";

        $response = $this->makeAuthenticatedRequest('GET', $endpoint);
        Log::channel('lw_auth')->info('getStockItemHistory: '.json_encode($response));

        return $response;
    }

    /**
     * Get all products from Linnworks with pagination
     */
    public function getAllProducts(int $page = 1, ?int $entriesPerPage = null): array
    {
        $entriesPerPage = $entriesPerPage ?? config('linnworks.pagination.sync_page_size');

        // Use the existing searchStockItems method which already works
        try {
            $response = $this->searchStockItems('', $entriesPerPage, ['StockLevels'], ['SKU', 'Title', 'Barcode'], $page);

            Log::info('getAllProducts response', [
                'count' => count($response),
                'page' => $page,
                'entriesPerPage' => $entriesPerPage,
                'sample' => array_slice($response, 0, 2),
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to get all products from Linnworks', [
                'error' => $e->getMessage(),
                'page' => $page,
                'entriesPerPage' => $entriesPerPage,
            ]);

            return [];
        }
    }

    /**
     * Get all locations from Linnworks using the dedicated locations endpoint
     */
    public function getLocations(): array
    {
        // Try the most likely working endpoints in order
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

            } catch (\Exception $e) {
                Log::channel('inventory')->warning("Endpoint {$endpoint} failed: {$e->getMessage()}");

                continue;
            }
        }

        // If all endpoints failed
        throw new \Exception('Failed to retrieve locations from any of the tried endpoints: '.implode(', ', $endpoints));
    }

    /**
     * Extract locations from inventory data as a fallback method
     */
    private function getLocationsFromInventory(): array
    {
        $locations = [];
        $locationIds = [];
        $page = 1;
        $maxPages = 10; // Limit to prevent infinite loops
        $itemsPerPage = 50; // Get more items per page

        Log::channel('inventory')->info('Starting comprehensive location extraction from inventory');

        // Scan through multiple pages of inventory to find all locations
        while ($page <= $maxPages) {
            try {
                $inventoryData = $this->getInventory($page, $itemsPerPage);

                if (empty($inventoryData)) {
                    Log::channel('inventory')->info("No more inventory data on page {$page}, stopping");
                    break;
                }

                $newLocationsFound = 0;

                foreach ($inventoryData as $item) {
                    if (isset($item['StockLevels']) && is_array($item['StockLevels'])) {
                        foreach ($item['StockLevels'] as $stockLevel) {
                            if (isset($stockLevel['Location']['StockLocationId'])) {
                                $locationId = $stockLevel['Location']['StockLocationId'];

                                // Avoid duplicates
                                if (! in_array($locationId, $locationIds)) {
                                    $locationIds[] = $locationId;
                                    $locations[] = [
                                        'StockLocationId' => $locationId,
                                        'LocationName' => $stockLevel['Location']['LocationName'] ?? 'Unknown Location',
                                        'BinRack' => $stockLevel['Location']['BinRack'] ?? '',
                                        'IsWarehouseManaged' => $stockLevel['Location']['IsWarehouseManaged'] ?? false,
                                    ];
                                    $newLocationsFound++;
                                }
                            }
                        }
                    }
                }

                Log::channel('inventory')->info("Page {$page}: Found {$newLocationsFound} new locations, total: ".count($locations));

                // If we didn't find any new locations on this page, we might have all of them
                if ($newLocationsFound === 0 && count($locations) > 0) {
                    Log::channel('inventory')->info("No new locations found on page {$page}, stopping early");
                    break;
                }

                $page++;

            } catch (\Exception $e) {
                Log::channel('inventory')->error("Error processing inventory page {$page}: ".$e->getMessage());
                break;
            }
        }

        Log::channel('inventory')->info('Completed location extraction from inventory', [
            'total_locations' => count($locations),
            'pages_scanned' => $page - 1,
            'location_ids' => $locationIds,
        ]);

        return $locations;
    }

    /**
     * Debug method to test different location endpoints
     */
    public function debugLocationEndpoints(): array
    {
        $results = [];
        $endpoints = [
            'Stock/GetStockLocationFull',
            'Stock/GetStockLocations',
            'Locations',
            'Locations/GetLocations',
            'Locations/GetLocation',
            'Inventory/GetStockLocations',
            'Inventory/GetInventoryLocations',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = $this->makeAuthenticatedRequest('GET', $endpoint);
                $results[$endpoint] = [
                    'success' => true,
                    'count' => is_array($response) ? count($response) : 'non-array',
                    'sample' => is_array($response) ? array_slice($response, 0, 2) : $response,
                    'all_keys' => is_array($response) && ! empty($response) ? array_keys($response[0] ?? []) : [],
                ];
                Log::channel('inventory')->info("Endpoint {$endpoint} succeeded", $results[$endpoint]);
            } catch (\Exception $e) {
                $results[$endpoint] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
                Log::channel('inventory')->error("Endpoint {$endpoint} failed", $results[$endpoint]);
            }
        }

        // Also test the fallback method
        try {
            $fallbackLocations = $this->getLocationsFromInventory();
            $results['FALLBACK_METHOD'] = [
                'success' => true,
                'count' => count($fallbackLocations),
                'sample' => array_slice($fallbackLocations, 0, 3),
                'all_locations' => $fallbackLocations,
            ];
        } catch (\Exception $e) {
            $results['FALLBACK_METHOD'] = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        return $results;
    }

    /**
     * Get stock levels for a product across all locations
     */
    public function getStockLocationsByProduct(string $sku): array
    {
        try {
            $stockItem = $this->getStockDetails($sku);

            if (empty($stockItem) || ! isset($stockItem['StockLevels'])) {
                return [];
            }

            // Filter to only locations with stock > 0
            $locationsWithStock = array_filter($stockItem['StockLevels'], function ($location) {
                return isset($location['StockLevel']) && $location['StockLevel'] > 0;
            });

            Log::channel('inventory')->info("Found stock locations for SKU: {$sku}", [
                'locations_with_stock' => count($locationsWithStock),
                'total_locations' => count($stockItem['StockLevels']),
            ]);

            return array_values($locationsWithStock);
        } catch (\Exception $e) {
            Log::channel('inventory')->error("Failed to get stock locations for SKU: {$sku}", [
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Failed to retrieve stock locations: {$e->getMessage()}");
        }
    }

    /**
     * Adjust stock level at a specific location
     */
    public function transferStockToDefaultLocation(string $sku, string $sourceLocationId, int $transferQuantity): array
    {
        try {
            // Get current stock levels for ALL locations (including those with 0 stock)
            $stockItem = $this->getStockDetails($sku);

            if (empty($stockItem) || ! isset($stockItem['StockLevels'])) {
                throw new \Exception("No stock information found for SKU: {$sku}");
            }

            $allLocations = $stockItem['StockLevels']; // Don't filter out 0 stock locations
            $sourceLocation = null;
            $defaultLocation = null;
            $defaultLocationId = config('linnworks.default_location_id');

            Log::channel('inventory')->info('Searching for locations in transfer', [
                'sku' => $sku,
                'source_location_id' => $sourceLocationId,
                'default_location_id' => $defaultLocationId,
                'total_locations' => count($allLocations),
                'all_location_ids' => array_map(function ($loc) {
                    return $loc['Location']['StockLocationId'] ?? 'no-id';
                }, $allLocations),
            ]);

            foreach ($allLocations as $location) {
                $currentLocationId = $location['Location']['StockLocationId'] ?? null;
                if ($currentLocationId === $sourceLocationId) {
                    $sourceLocation = $location;
                } elseif ($currentLocationId === $defaultLocationId) {
                    $defaultLocation = $location;
                }
            }

            if (! $sourceLocation) {
                throw new \Exception("Source location not found: {$sourceLocationId}");
            }

            if (! $defaultLocation) {
                throw new \Exception("Default location not found: {$defaultLocationId}");
            }

            $sourceCurrentStock = $sourceLocation['StockLevel'] ?? 0;
            $defaultCurrentStock = $defaultLocation['StockLevel'] ?? 0;

            // Calculate new stock levels
            $sourceNewStock = $sourceCurrentStock - $transferQuantity;
            $defaultNewStock = $defaultCurrentStock + $transferQuantity;

            // Validate we have enough stock in source
            if ($sourceNewStock < 0) {
                throw new \Exception("Insufficient stock in source location. Available: {$sourceCurrentStock}, Requested: {$transferQuantity}");
            }

            // Perform both stock updates in one API call
            $body = [
                'stockLevels' => [
                    [
                        'SKU' => $sku,
                        'LocationId' => $sourceLocationId,
                        'Level' => $sourceNewStock,
                    ],
                    [
                        'SKU' => $sku,
                        'LocationId' => $defaultLocationId,
                        'Level' => $defaultNewStock,
                    ],
                ],
            ];

            Log::channel('inventory')->info('Transferring stock from source to default location', [
                'sku' => $sku,
                'source_location_id' => $sourceLocationId,
                'default_location_id' => $defaultLocationId,
                'transfer_quantity' => $transferQuantity,
                'source_old_stock' => $sourceCurrentStock,
                'source_new_stock' => $sourceNewStock,
                'default_old_stock' => $defaultCurrentStock,
                'default_new_stock' => $defaultNewStock,
                'request_body' => $body,
                'json_body' => json_encode($body),
            ]);

            $response = $this->makeAuthenticatedRequest(
                'POST',
                'Stock/SetStockLevel',
                ['body' => json_encode($body)]
            );

            Log::channel('inventory')->info('Stock transfer completed', [
                'sku' => $sku,
                'source_location_id' => $sourceLocationId,
                'default_location_id' => $defaultLocationId,
                'transfer_quantity' => $transferQuantity,
                'response' => $response,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::channel('inventory')->error('Failed to transfer stock', [
                'sku' => $sku,
                'source_location_id' => $sourceLocationId,
                'transfer_quantity' => $transferQuantity,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Failed to transfer stock: {$e->getMessage()}");
        }
    }
}
