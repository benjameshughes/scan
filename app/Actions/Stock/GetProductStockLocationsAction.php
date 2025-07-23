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

    public function handle(Product $product): array
    {
        try {
            $locations = $this->linnworksService->getStockLocationsByProduct($product->sku);

            Log::channel('inventory')->info('Fetched stock locations for product', [
                'product_sku' => $product->sku,
                'locations_count' => count($locations),
                'sample_location' => !empty($locations) ? $locations[0] : 'no locations',
            ]);

            return $this->formatLocations($locations);
        } catch (\Exception $e) {
            Log::channel('inventory')->error('Failed to fetch stock locations', [
                'product_sku' => $product->sku,
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