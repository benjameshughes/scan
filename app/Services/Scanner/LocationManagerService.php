<?php

namespace App\Services\Scanner;

use App\Actions\Stock\AutoSelectLocationAction;
use App\Actions\Stock\GetProductStockLocationsAction;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class LocationManagerService
{
    public function __construct(
        private GetProductStockLocationsAction $getLocationsAction,
        private AutoSelectLocationAction $autoSelectAction,
    ) {}

    /**
     * Prepare refill form locations for a product
     */
    public function prepareRefillLocations(Product $product): array
    {
        try {
            Log::debug('Preparing refill locations', ['product_sku' => $product->sku]);

            // Get available locations from the API
            $locations = $this->getLocationsAction->handle($product);

            if (empty($locations)) {
                return [
                    'success' => false,
                    'error' => 'No locations with stock found for this product.',
                    'locations' => [],
                    'selectedLocationId' => '',
                ];
            }

            // Convert to legacy format for backward compatibility
            $formattedLocations = $this->formatLocationsForLegacyView($locations);

            // Auto-select location
            $selectedLocationId = $this->autoSelectLocation($locations);

            Log::debug('Refill locations prepared', [
                'product_sku' => $product->sku,
                'location_count' => count($locations),
                'auto_selected' => $selectedLocationId,
            ]);

            return [
                'success' => true,
                'error' => null,
                'locations' => $formattedLocations,
                'selectedLocationId' => $selectedLocationId,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to prepare refill locations', [
                'product_sku' => $product->sku,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => "Failed to load locations: {$e->getMessage()}",
                'locations' => [],
                'selectedLocationId' => '',
            ];
        }
    }

    /**
     * Get formatted locations for smart location selector
     */
    public function getSmartLocationSelectorData(array $availableLocations): Collection
    {
        if (empty($availableLocations)) {
            return collect([]);
        }

        return collect($availableLocations)->map(function ($location) {
            // Handle different API response structures
            $locationData = $location['Location'] ?? $location;

            return [
                'StockLocationId' => $locationData['StockLocationId'] ?? $locationData['LocationId'] ?? $locationData['id'],
                'LocationName' => $locationData['LocationName'] ?? $locationData['Name'] ?? 'Unknown Location',
                'Quantity' => $location['Quantity'] ?? $location['Available'] ?? $location['Stock'] ?? 0,
            ];
        })->filter(function ($location) {
            // Only include locations with stock and valid ID
            return ! empty($location['StockLocationId']) && $location['Quantity'] > 0;
        })->values();
    }

    /**
     * Validate refill quantity against location stock
     */
    public function validateRefillQuantity(int $quantity, string $locationId, array $availableLocations): array
    {
        if (empty($availableLocations)) {
            return [
                'valid' => false,
                'error' => 'No locations available for validation.',
                'maxStock' => 0,
            ];
        }

        $selectedLocation = $this->findLocationById($locationId, $availableLocations);

        if (! $selectedLocation) {
            return [
                'valid' => false,
                'error' => 'Selected location not found.',
                'maxStock' => 0,
            ];
        }

        $maxStock = $this->getLocationMaxStock($selectedLocation);

        if ($quantity > $maxStock) {
            return [
                'valid' => false,
                'error' => "Maximum available quantity is {$maxStock} units.",
                'maxStock' => $maxStock,
            ];
        }

        if ($quantity < 1) {
            return [
                'valid' => false,
                'error' => 'Minimum quantity is 1 unit.',
                'maxStock' => $maxStock,
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'maxStock' => $maxStock,
        ];
    }

    /**
     * Get maximum stock for a location
     */
    public function getMaxRefillStock(string $locationId, array $availableLocations): int
    {
        if (empty($availableLocations)) {
            return 0;
        }

        $selectedLocation = $this->findLocationById($locationId, $availableLocations);

        if (! $selectedLocation) {
            return 0;
        }

        return $this->getLocationMaxStock($selectedLocation);
    }

    /**
     * Increment refill quantity with stock validation
     */
    public function incrementRefillQuantity(int $currentQuantity, string $locationId, array $availableLocations): int
    {
        $maxStock = $this->getMaxRefillStock($locationId, $availableLocations);

        if ($currentQuantity < $maxStock) {
            return $currentQuantity + 1;
        }

        return $currentQuantity;
    }

    /**
     * Decrement refill quantity with minimum validation
     */
    public function decrementRefillQuantity(int $currentQuantity): int
    {
        return max(1, $currentQuantity - 1);
    }

    /**
     * Format locations for legacy view compatibility
     */
    private function formatLocationsForLegacyView(array $locations): array
    {
        return collect($locations)->map(function ($location) {
            return [
                'Location' => [
                    'StockLocationId' => $location['id'],
                    'LocationName' => $location['name'],
                ],
                'StockLevel' => $location['stock_level'],
                'Available' => $location['available'],
                'Allocated' => $location['allocated'],
                'OnOrder' => $location['on_order'],
                'MinimumLevel' => $location['minimum_level'],
            ];
        })->toArray();
    }

    /**
     * Auto-select the best location for refill
     */
    private function autoSelectLocation(array $locations): string
    {
        $defaultLocationId = config('linnworks.default_location_id');
        $preferredLocationId = config('linnworks.floor_location_id');

        $autoSelected = $this->autoSelectAction->handle(
            $locations,
            $defaultLocationId,
            $preferredLocationId,
            1 // Default quantity for selection
        );

        if ($autoSelected) {
            Log::debug('Auto-selected location for refill', [
                'location_id' => $autoSelected['id'],
                'location_name' => $autoSelected['name'],
                'stock_level' => $autoSelected['stock_level'],
            ]);

            return $autoSelected['id'];
        }

        return '';
    }

    /**
     * Find location by ID in available locations array
     */
    private function findLocationById(string $locationId, array $availableLocations): ?array
    {
        return collect($availableLocations)->first(function ($location, $index) use ($locationId) {
            $id = $location['Location']['StockLocationId']
                ?? $location['LocationId']
                ?? $location['locationId']
                ?? $location['id']
                ?? $index;

            return $id == $locationId;
        });
    }

    /**
     * Get max stock for a location
     */
    private function getLocationMaxStock(array $location): int
    {
        return $location['StockLevel']
            ?? $location['stockLevel']
            ?? $location['stock']
            ?? 0;
    }
}
