<?php

namespace App\Actions\Stock;

use Illuminate\Support\Facades\Log;

class AutoSelectLocationAction
{
    public function handle(
        array $availableLocations,
        string $targetLocationId,
        ?string $preferredLocationId = null,
        int $minStockRequired = 1
    ): ?array {
        if (empty($availableLocations)) {
            Log::channel('inventory')->warning('No locations available for auto-selection');

            return null;
        }

        // Filter out the target location (can't transfer to itself)
        $sourceLocations = collect($availableLocations)->filter(function ($location) use ($targetLocationId) {
            return $location['id'] !== $targetLocationId && $location['stock_level'] >= 1;
        });

        if ($sourceLocations->isEmpty()) {
            Log::channel('inventory')->warning('No valid source locations after filtering', [
                'target_location_id' => $targetLocationId,
                'total_locations' => count($availableLocations),
            ]);

            return null;
        }

        // 1. Try preferred location first (e.g., floor location)
        if ($preferredLocationId) {
            $preferredLocation = $sourceLocations->first(function ($location) use ($preferredLocationId, $minStockRequired) {
                return $location['id'] === $preferredLocationId && $location['stock_level'] >= $minStockRequired;
            });

            if ($preferredLocation) {
                Log::channel('inventory')->info('Auto-selected preferred location', [
                    'selected_location' => $preferredLocation['name'],
                    'stock_level' => $preferredLocation['stock_level'],
                ]);

                return $preferredLocation;
            }
        }

        // 2. If only one location available, auto-select it
        if ($sourceLocations->count() === 1) {
            $singleLocation = $sourceLocations->first();
            Log::channel('inventory')->info('Auto-selected single available location', [
                'selected_location' => $singleLocation['name'],
                'stock_level' => $singleLocation['stock_level'],
            ]);

            return $singleLocation;
        }

        // 3. Select location with highest stock level
        $highestStockLocation = $sourceLocations->sortByDesc('stock_level')->first();

        Log::channel('inventory')->info('Auto-selected location with highest stock', [
            'selected_location' => $highestStockLocation['name'],
            'stock_level' => $highestStockLocation['stock_level'],
            'total_options' => $sourceLocations->count(),
        ]);

        return $highestStockLocation;
    }

    /**
     * Get the maximum transferable quantity from the selected location
     */
    public function getMaxTransferQuantity(array $selectedLocation, int $requestedQuantity): int
    {
        $availableStock = $selectedLocation['stock_level'] ?? 0;
        $maxQuantity = min($requestedQuantity, $availableStock);

        Log::channel('inventory')->debug('Calculated max transfer quantity', [
            'location' => $selectedLocation['name'] ?? 'unknown',
            'available_stock' => $availableStock,
            'requested_quantity' => $requestedQuantity,
            'max_quantity' => $maxQuantity,
        ]);

        return $maxQuantity;
    }
}
