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

            // Use null for target to default to config location
            $defaultLocationId = config('linnworks.default_location_id');
            $targetLocationId = ($toLocationId === $defaultLocationId) ? null : $toLocationId;

            $result = $this->linnworksService->transferStockBetweenLocations(
                $product->sku,
                $fromLocationId,
                $quantity,
                $targetLocationId
            );

            Log::channel('inventory')->info('Stock transfer completed successfully', [
                'product_sku' => $product->sku,
                'quantity' => $quantity,
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
     * Get the preferred source location ID for refill operations
     */
    public function getPreferredSourceLocationId(): string
    {
        return config('linnworks.floor_location_id', '');
    }
}
