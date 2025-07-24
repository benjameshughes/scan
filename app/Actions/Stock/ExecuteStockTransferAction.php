<?php

namespace App\Actions\Stock;

use App\Jobs\ProcessStockMovement;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ExecuteStockTransferAction
{
    public function __construct(
        private GetProductStockLocationsAction $getStockLocationsAction,
        private ValidateStockTransferAction $validateAction,
        private AutoSelectLocationAction $autoSelectAction,
        private ProcessStockTransferAction $processTransferAction,
        private CreateStockMovementRecordAction $createRecordAction
    ) {}

    public function handle(
        User $user,
        Product $product,
        int $quantity,
        string $operationType = 'refill',
        ?string $fromLocationId = null,
        ?string $toLocationId = null,
        ?string $notes = null,
        bool $autoSelectSource = true,
        array $additionalMetadata = []
    ): array {
        return DB::transaction(function () use (
            $user, $product, $quantity, $operationType, $fromLocationId, 
            $toLocationId, $notes, $autoSelectSource, $additionalMetadata
        ) {
            // Step 1: Get available stock locations
            // For refill operations, only show locations with stock > 0
            // For manual transfers, show all locations regardless of stock level
            $includeZeroStock = ($operationType !== 'refill');
            $stockLocations = $this->getStockLocationsAction->handle($product, $includeZeroStock);
            
            if (empty($stockLocations)) {
                throw ValidationException::withMessages([
                    'product' => ['No stock locations found for this product.']
                ]);
            }

            // Step 2: Handle location selection
            $sourceLocation = null;
            $targetLocation = null;

            if ($operationType === 'refill') {
                // For refill operations, target is default location
                $targetLocationId = $toLocationId ?? $this->processTransferAction->getDefaultTargetLocationId();
                
                if ($autoSelectSource && !$fromLocationId) {
                    $preferredSourceId = $this->processTransferAction->getPreferredSourceLocationId();
                    $sourceLocation = $this->autoSelectAction->handle(
                        $stockLocations,
                        $targetLocationId,
                        $preferredSourceId,
                        $quantity
                    );
                    
                    if (!$sourceLocation) {
                        throw ValidationException::withMessages([
                            'location' => ['No suitable source location found for refill operation.']
                        ]);
                    }
                    
                    $fromLocationId = $sourceLocation['id'];
                } else {
                    $sourceLocation = collect($stockLocations)->firstWhere('id', $fromLocationId);
                }
            } else {
                // For manual transfers, both locations must be specified
                if (!$fromLocationId || !$toLocationId) {
                    throw ValidationException::withMessages([
                        'location' => ['Both source and destination locations are required for manual transfers.']
                    ]);
                }
                
                $sourceLocation = collect($stockLocations)->firstWhere('id', $fromLocationId);
            }

            if (!$sourceLocation) {
                throw ValidationException::withMessages([
                    'location' => ['Invalid source location selected.']
                ]);
            }

            // Step 3: Validate the transfer
            $this->validateAction->handle(
                $user,
                $product,
                $quantity,
                $fromLocationId,
                $sourceLocation['stock_level'],
                $operationType
            );

            // Step 4: Calculate actual transfer quantity
            $maxQuantity = $this->autoSelectAction->getMaxTransferQuantity($sourceLocation, $quantity);
            
            if ($maxQuantity < $quantity) {
                Log::channel('inventory')->warning('Adjusting transfer quantity due to stock constraints', [
                    'requested' => $quantity,
                    'available' => $maxQuantity,
                    'location' => $sourceLocation['name'],
                ]);
                $quantity = $maxQuantity;
            }

            // Step 5: Create the stock movement record (with pending status)
            $stockMovement = $this->createMovementRecord(
                $product,
                $user,
                $quantity,
                $operationType,
                $sourceLocation,
                $targetLocationId ?? $toLocationId,
                $stockLocations,
                $notes,
                array_merge($additionalMetadata, [
                    'auto_selected_source' => $autoSelectSource && $sourceLocation,
                    'created_for_async_processing' => true,
                ])
            );

            // Step 6: Dispatch the job to process the stock transfer asynchronously
            ProcessStockMovement::dispatch($stockMovement);

            $successMessage = "Stock movement queued for processing. {$quantity} units will be transferred from {$sourceLocation['name']} soon.";

            Log::channel('inventory')->info('Stock movement queued for processing', [
                'movement_id' => $stockMovement->id,
                'operation_type' => $operationType,
                'product_sku' => $product->sku,
                'quantity_to_transfer' => $quantity,
                'from_location' => $sourceLocation['name'],
                'user_id' => $user->id,
                'job_dispatched' => true,
            ]);

            return [
                'success' => true,
                'message' => $successMessage,
                'stock_movement' => $stockMovement,
                'quantity_transferred' => $quantity,
                'source_location' => $sourceLocation,
                'auto_selected' => $autoSelectSource && $sourceLocation,
                'processing_async' => true,
            ];
        });
    }

    private function createMovementRecord(
        Product $product,
        User $user,
        int $quantity,
        string $operationType,
        array $sourceLocation,
        string $targetLocationId,
        array $allLocations,
        ?string $notes,
        array $metadata
    ): StockMovement {
        $targetLocation = collect($allLocations)->firstWhere('id', $targetLocationId);
        
        $movementType = match ($operationType) {
            'refill' => StockMovement::TYPE_BAY_REFILL,
            'adjustment' => StockMovement::TYPE_SCAN_ADJUSTMENT,
            default => StockMovement::TYPE_MANUAL_TRANSFER,
        };

        if ($operationType === 'refill') {
            return $this->createRecordAction->createBayRefillRecord(
                $product,
                $user,
                $quantity,
                $sourceLocation['name'],
                $targetLocation['name'] ?? 'Default',
                $sourceLocation['id'],
                $targetLocationId,
                $metadata
            );
        }

        return $this->createRecordAction->createManualTransferRecord(
            $product,
            $user,
            $quantity,
            $sourceLocation['name'],
            $targetLocation['name'] ?? 'Unknown',
            $sourceLocation['id'],
            $targetLocationId,
            $notes,
            $metadata
        );
    }
}