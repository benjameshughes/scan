<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class LinnworksApiService
{
    protected readonly string $appId;
    protected readonly string $appSecret;
    protected readonly string $appToken;
    protected readonly string $baseUrl;
    protected readonly string $authUrl;
    protected Client $client;
    protected string $cacheKey = 'linnworks.session_token';

    public function __construct()
    {
        $this->client = new Client();
        $this->appId = config('linnworks.app_id');
        $this->appSecret = config('linnworks.app_secret');
        $this->appToken = config('linnworks.app_token');
        $this->baseUrl = config('linnworks.base_url');
        $this->authUrl = config('linnworks.auth_url');

        $this->ensureAuthorized();
    }

    /**
     * Ensure we have a valid session token
     */
    private function ensureAuthorized(): string
    {

        // Make a call to get the latest token and compare it to the token in the cache. If different save the new token in the cache
        $token = $this->authorizeByApplication();
        $cachedToken = Cache::get($this->cacheKey);

        // If for some reason there is no token get a new token
        if (Cache::missing($this->cacheKey)) {
            return $this->authorizeByApplication();
        }

        if ($token !== $cachedToken) {
            Cache::pull($this->cacheKey);
            return $this->authorizeByApplication();
        }
        Log::channel('lw_auth')->info('Checking the linnworks token');
        return Cache::get($this->cacheKey);
    }

    /**
     * Authorize with Linnworks API
     */
    private function authorizeByApplication(): string
    {
        $body = [
            "ApplicationId" => $this->appId,
            "ApplicationSecret" => $this->appSecret,
            "Token" => $this->appToken,
        ];

        try {
            $response = $this->makeRequest('POST', $this->authUrl . 'Auth/AuthorizeByApplication', [
                'body' => json_encode($body),
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            $sessionToken = $response['Token'];

            // Store the session token in the cache
            Cache::put($this->cacheKey, $sessionToken);
            Log::channel('lw_auth')->info('Authorized by application');

            return $sessionToken;
        } catch (Exception $e) {
            Log::channel('lw_auth')->error('Authorization failed: ' . $e->getMessage());
            throw new Exception('Unable to authorize by application: ' . $e->getMessage());
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
            return $this->makeRequest($method, $this->baseUrl . $endpoint, $options);
        } catch (Exception $e) {
            // If we get a 401, try to refresh the token and retry once
            if (str_contains($e->getMessage(), '401')) {
                Cache::forget($this->cacheKey);
                $token = $this->authorizeByApplication();

                $options['headers']['Authorization'] = $token;
                return $this->makeRequest($method, $this->baseUrl . $endpoint, $options);
            }

            throw $e;
        }
    }

    /**
     * Raw dawg the API with a request
     */
    protected function makeRequest(string $method, string $url, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $url, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::channel('lw_auth')->error("API request failed: {$method} {$url} - " . $e->getMessage());
            throw new Exception("API request failed: {$e->getMessage()}");
        }
    }

    /**
     * Refresh the session token
     */
    public function refreshToken(): string
    {
        Log::channel('lw_auth')->info('Refreshing token');
        return $this->ensureAuthorized();
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
                    'LocationId' => '00000000-0000-0000-0000-000000000000',
                    'Level' => $quantity,
                ]
            ]
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
    public function getInventory(int $pageNumber = 1, int $entriesPerPage = 200): array
    {
        $body = [
            "loadCompositeParents" => false,
            "loadVariationParents" => false,
            "entriesPerPage" => $entriesPerPage,
            "pageNumber" => $pageNumber,
            "dataRequirements" => ["StockLevels"],
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
        $response = $this->makeAuthenticatedRequest('GET', 'Inventory/GetInventoryItemsCount');
        return (int)$response;
    }

    /**
     * Get detailed stock information for a SKU
     */
    public function getStockDetails(string $sku): array
    {
        $data = $this->searchStockItems($sku, 1, ['StockLevels']);

        if (!empty($data)) {
            Log::channel('inventory')->info("Stock Details: " . ($data[0]['ItemTitle'] ?? 'Not found'));
            return $data[0];
        }

        return [];
    }

    /**
     * Search for stock items
     */
    protected function searchStockItems(
        string $keyword,
        int $entriesPerPage = 1,
        array $dataRequirements = ['StockLevels'],
        array $searchTypes = ["SKU", "Title", "Barcode"]
    ): array {
        $body = [
            'keyword' => trim($keyword),
            'loadCompositeParents' => false,
            'loadVariationParents' => false,
            'entriesPerPage' => $entriesPerPage,
            'pageNumber' => 1,
            "dataRequirements" => $dataRequirements,
            "searchTypes" => $searchTypes,
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
    public function getStockItemHistory(string $sku): array
    {
        $itemDetail = $this->getStockDetails($sku);

        if (empty($itemDetail)) {
            Log::channel('inventory')->warning("SKU not found: {$sku}");
            return [];
        }

        $itemId = $itemDetail['StockItemId'];
        Log::channel('inventory')->info("{$sku} - {$itemId} for stock item history search");

        $endpoint = "Stock/GetItemChangesHistory?stockItemId={$itemId}&locationId=00000000-0000-0000-0000-000000000000&entriesPerPage=0&pageNumber=0";

        $response = $this->makeAuthenticatedRequest('GET', $endpoint);
        Log::channel('lw_auth')->info('getStockItemHistory: ' . json_encode($response));

        return $response;
    }
}
