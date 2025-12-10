<?php

namespace App\Actions\Scanner;

use App\Models\Product;
use App\Models\User;
use App\Services\Scanner\LocationManagerService;
use Illuminate\Support\Facades\Log;

class PrepareRefillFormAction
{
    public function __construct(
        private LocationManagerService $locationManager,
    ) {}

    /**
     * Prepare refill form data for a product
     */
    public function handle(Product $product, User $user): array
    {
        Log::info('Preparing refill form', [
            'product_sku' => $product->sku,
            'user_id' => $user->id,
        ]);

        // Check user permissions
        if (! $user->can('refill bays')) {
            Log::warning('Refill form preparation failed - insufficient permissions', [
                'product_sku' => $product->sku,
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => 'You do not have permission to refill bays.',
                'showRefillForm' => false,
                'availableLocations' => [],
                'selectedLocationId' => '',
                'isProcessingRefill' => false,
            ];
        }

        // Validate product
        if (! $product) {
            return [
                'success' => false,
                'error' => 'No product selected for refill.',
                'showRefillForm' => false,
                'availableLocations' => [],
                'selectedLocationId' => '',
                'isProcessingRefill' => false,
            ];
        }

        try {
            // Get available locations using the service
            $locationResult = $this->locationManager->prepareRefillLocations($product);

            if (! $locationResult['success']) {
                Log::warning('Refill form preparation failed - no locations', [
                    'product_sku' => $product->sku,
                    'error' => $locationResult['error'],
                ]);

                return [
                    'success' => false,
                    'error' => $locationResult['error'],
                    'showRefillForm' => false,
                    'availableLocations' => [],
                    'selectedLocationId' => '',
                    'isProcessingRefill' => false,
                ];
            }

            Log::info('Refill form prepared successfully', [
                'product_sku' => $product->sku,
                'location_count' => count($locationResult['locations']),
                'auto_selected' => $locationResult['selectedLocationId'],
            ]);

            return [
                'success' => true,
                'error' => null,
                'showRefillForm' => true,
                'availableLocations' => $locationResult['locations'],
                'selectedLocationId' => $locationResult['selectedLocationId'],
                'isProcessingRefill' => false,
            ];

        } catch (\Exception $e) {
            Log::error('Refill form preparation failed with exception', [
                'product_sku' => $product->sku,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => "Failed to prepare refill form: {$e->getMessage()}",
                'showRefillForm' => false,
                'availableLocations' => [],
                'selectedLocationId' => '',
                'isProcessingRefill' => false,
            ];
        }
    }

    /**
     * Handle location change in refill form
     */
    public function handleLocationChange(string $locationId, array $availableLocations): array
    {
        Log::debug('Handling refill location change', ['location_id' => $locationId]);

        // Validate that the location exists in available locations
        $locationExists = collect($availableLocations)->contains(function ($location) use ($locationId) {
            $id = $location['Location']['StockLocationId'] ?? $location['LocationId'] ?? $location['id'];

            return $id == $locationId;
        });

        if (! $locationExists) {
            return [
                'success' => false,
                'error' => 'Selected location is not available.',
                'selectedLocationId' => '',
                'refillQuantity' => 1,
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'selectedLocationId' => $locationId,
            'refillQuantity' => 1, // Reset to safe value
        ];
    }

    /**
     * Validate refill quantity for selected location
     */
    public function validateRefillQuantity(int $quantity, string $locationId, array $availableLocations): array
    {
        return $this->locationManager->validateRefillQuantity($quantity, $locationId, $availableLocations);
    }

    /**
     * Get smart location selector data
     */
    public function getSmartLocationSelectorData(array $availableLocations): array
    {
        return $this->locationManager->getSmartLocationSelectorData($availableLocations)->toArray();
    }

    /**
     * Get maximum refill stock for location
     */
    public function getMaxRefillStock(string $locationId, array $availableLocations): int
    {
        return $this->locationManager->getMaxRefillStock($locationId, $availableLocations);
    }

    /**
     * Increment refill quantity
     */
    public function incrementRefillQuantity(int $currentQuantity, string $locationId, array $availableLocations): int
    {
        return $this->locationManager->incrementRefillQuantity($currentQuantity, $locationId, $availableLocations);
    }

    /**
     * Decrement refill quantity
     */
    public function decrementRefillQuantity(int $currentQuantity): int
    {
        return $this->locationManager->decrementRefillQuantity($currentQuantity);
    }

    /**
     * Cancel refill form and reset state
     */
    public function cancelRefill(): array
    {
        Log::debug('Cancelling refill form');

        return [
            'showRefillForm' => false,
            'selectedLocationId' => '',
            'refillQuantity' => 1,
            'availableLocations' => [],
            'isProcessingRefill' => false,
            'refillError' => '',
            'refillSuccess' => '',
        ];
    }

    /**
     * Clear refill error message
     */
    public function clearRefillError(): array
    {
        return [
            'refillError' => '',
        ];
    }

    /**
     * Get refill form initial state
     */
    public function getInitialState(): array
    {
        return [
            'showRefillForm' => false,
            'selectedLocationId' => '',
            'refillQuantity' => 1,
            'availableLocations' => [],
            'isProcessingRefill' => false,
            'refillError' => '',
            'refillSuccess' => '',
        ];
    }
}
