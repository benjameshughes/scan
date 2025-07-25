<?php

namespace App\Actions\Stock;

use App\Models\Product;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;

class ProcessStockTransferAction
{
    public function __construct(
        private LinnworksApiService $linnworksService
    ) {}

    public function handle(
        Product $product,
        string $fromLocationId,
        string $toLocationId,
        int $quantity,
        string $reason = 'Stock transfer'
    ): array {
        try {

            Log::channel('inventory')->info('Processing stock transfer via Linnworks', [
                'product_sku' => $product->sku,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'quantity' => $quantity,
                'reason' => $reason,
            ]);

            // Execute the transfer via Linnworks API
            // Use different methods based on whether we're transferring to default location
            $defaultLocationId = config('linnworks.default_location_id');

            if ($toLocationId === $defaultLocationId) {
                // Use the specialized method for transfers to default location (handles null GUID)
                $result = $this->linnworksService->transferStockToDefaultLocation(
                    $product->sku,
                    $fromLocationId,
                    $quantity
                );
            } else {
                // Use generic method for transfers between specific locations
                $result = $this->linnworksService->transferStockBetweenLocations(
                    $product->sku,
                    $fromLocationId,
                    $toLocationId,
                    $quantity
                );
            }

            Log::channel('inventory')->info('Stock transfer completed successfully', [
                'product_sku' => $product->sku,
                'quantity' => $quantity,
                'linnworks_result' => $result,
            ]);

            return [
                'success' => true,
                'message' => "Successfully transferred {$quantity} units",
                'linnworks_result' => $result,
                'transferred_quantity' => $quantity,
            ];

        } catch (\Exception $e) {
            Log::channel('inventory')->error('Stock transfer failed', [
                'product_sku' => $product->sku,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Transfer failed: '.$e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the default target location ID for refill operations
     */
    public function getDefaultTargetLocationId(): string
    {
        return config('linnworks.default_location_id', '');
    }

    /**
     * Get the preferred source location ID for refill operations (e.g., floor location)
     */
    public function getPreferredSourceLocationId(): string
    {
        return config('linnworks.floor_location_id', '');
    }
}
