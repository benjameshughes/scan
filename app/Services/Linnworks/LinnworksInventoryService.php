<?php

namespace App\Services\Linnworks;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Handles read-only inventory/product operations with Linnworks API
 *
 * This service is SAFE - it only reads data from Linnworks, never writes
 *
 * Responsibilities:
 * - Product catalog retrieval
 * - Product searches
 * - Stock level queries
 * - Product details and history
 */
class LinnworksInventoryService
{
    private LinnworksHttpClient $httpClient;

    public function __construct(LinnworksHttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get inventory with pagination
     */
    public function getInventory(int $pageNumber = 1, ?int $entriesPerPage = null): array
    {
        $entriesPerPage = $entriesPerPage ?? config('linnworks.pagination.inventory_page_size');

        $data = [
            'loadCompositeParents' => false,
            'loadVariationParents' => false,
            'entriesPerPage' => $entriesPerPage,
            'pageNumber' => $pageNumber,
            'dataRequirements' => ['StockLevels'],
        ];

        return $this->httpClient->post('Stock/GetStockItemsFull', $data);
    }

    /**
     * Get total inventory count
     */
    public function getInventoryCount(): int
    {
        try {
            // This endpoint returns a plain number, not JSON
            $response = $this->httpClient->getPlainText('Inventory/GetInventoryItemsCount');
            $count = (int) $response;

            Log::info("Retrieved inventory count: {$count}");

            return $count;
        } catch (Exception $e) {
            Log::channel('lw_auth')->error('Failed to get inventory count: '.$e->getMessage());
            throw new Exception('Failed to get inventory count: '.$e->getMessage());
        }
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

        $data = [
            'keyword' => trim($keyword),
            'loadCompositeParents' => false,
            'loadVariationParents' => false,
            'entriesPerPage' => $entriesPerPage,
            'pageNumber' => $pageNumber,
            'dataRequirements' => $dataRequirements,
            'searchTypes' => $searchTypes,
        ];

        return $this->httpClient->post('Stock/GetStockItemsFull', $data);
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
     * Get stock level for a SKU
     */
    public function getStockLevel(string $sku): int
    {
        $data = $this->searchStockItems($sku, 1, ['StockLevels']);

        if (empty($data) || ! isset($data[0]['StockLevels'][0]['StockLevel'])) {
            return 0;
        }

        return $data[0]['StockLevels'][0]['StockLevel'];
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

        $response = $this->httpClient->get($endpoint);
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
     * Validate that a product exists in Linnworks (read-only check)
     */
    public function productExists(string $sku): bool
    {
        try {
            $result = $this->searchStockItems($sku, 1);

            return ! empty($result);
        } catch (Exception $e) {
            Log::warning("Failed to validate product existence for SKU: {$sku}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get basic product information (safe read-only operation)
     */
    public function getProductInfo(string $sku): ?array
    {
        try {
            $stockData = $this->getStockDetails($sku);

            if (empty($stockData)) {
                return null;
            }

            return [
                'sku' => $sku,
                'title' => $stockData['ItemTitle'] ?? 'Unknown',
                'stock_item_id' => $stockData['StockItemId'] ?? null,
                'stock_levels' => $stockData['StockLevels'] ?? [],
                'created_date' => $stockData['CreatedDate'] ?? null,
                'modified_date' => $stockData['ModifiedDate'] ?? null,
            ];
        } catch (Exception $e) {
            Log::warning("Failed to get product info for SKU: {$sku}", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
