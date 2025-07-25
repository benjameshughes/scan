<?php

namespace App\Actions\Stock;

use App\Models\Product;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;

class GetProductStockLocationsAction
{
    public function __construct(
        private LinnworksApiService $linnworksService
    ) {}

    public function handle(Product $product, bool $includeZeroStock = false): array
    {
        try {
            // Choose method based on whether to include locations with 0 stock
            if ($includeZeroStock) {
                $locations = $this->linnworksService->getAllStockLocationsByProduct($product->sku);
                $logMessage = 'Fetched all stock locations for product (including 0 stock)';
            } else {
                $locations = $this->linnworksService->getStockLocationsByProduct($product->sku);
                $logMessage = 'Fetched stock locations for product (stock > 0 only)';
            }

            Log::channel('inventory')->info($logMessage, [
                'product_sku' => $product->sku,
                'locations_count' => count($locations),
                'include_zero_stock' => $includeZeroStock,
                'sample_location' => ! empty($locations) ? $locations[0] : 'no locations',
            ]);

            return $this->formatLocations($locations);
        } catch (\Exception $e) {
            Log::channel('inventory')->error('Failed to fetch stock locations', [
                'product_sku' => $product->sku,
                'include_zero_stock' => $includeZeroStock,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function formatLocations(array $locations): array
    {
        return collect($locations)->map(function ($location) {
            $locationData = $location['Location'] ?? [];

            return [
                'id' => $locationData['StockLocationId'] ?? null,
                'name' => $locationData['LocationName'] ?? '',
                'stock_level' => $location['StockLevel'] ?? 0,
                'available' => $location['Available'] ?? 0,
                'allocated' => $location['Allocated'] ?? 0,
                'on_order' => $location['OnOrder'] ?? 0,
                'minimum_level' => $location['MinimumLevel'] ?? 0,
                'raw_data' => $location, // Keep original data for backward compatibility
            ];
        })->toArray();
    }
}
