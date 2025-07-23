<?php

namespace App\Actions\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CreateStockMovementRecordAction
{
    public function handle(
        Product $product,
        User $user,
        int $quantity,
        string $type,
        ?string $fromLocationId = null,
        ?string $fromLocationCode = null,
        ?string $toLocationId = null,
        ?string $toLocationCode = null,
        ?string $notes = null,
        array $metadata = []
    ): StockMovement {
        try {
            $stockMovement = StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'type' => $type,
                'from_location_id' => $fromLocationId,
                'from_location_code' => $fromLocationCode,
                'to_location_id' => $toLocationId,
                'to_location_code' => $toLocationCode,
                'moved_at' => now(),
                'notes' => $notes,
                'sync_status' => 'pending', // Will be processed by job
                'sync_attempts' => 0,
                'metadata' => array_merge([
                    'created_via' => 'action_system',
                    'user_name' => $user->name,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                ], $metadata),
            ]);

            Log::channel('inventory')->info('Stock movement record created', [
                'movement_id' => $stockMovement->id,
                'product_sku' => $product->sku,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'type' => $type,
                'from_location' => $fromLocationCode,
                'to_location' => $toLocationCode,
            ]);

            return $stockMovement;

        } catch (\Exception $e) {
            Log::channel('inventory')->error('Failed to create stock movement record', [
                'product_sku' => $product->sku,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create a bay refill movement record
     */
    public function createBayRefillRecord(
        Product $product,
        User $user,
        int $quantity,
        string $fromLocationCode,
        string $toLocationCode = 'MAIN',
        ?string $fromLocationId = null,
        ?string $toLocationId = null,
        array $additionalMetadata = []
    ): StockMovement {
        return $this->handle(
            product: $product,
            user: $user,
            quantity: $quantity,
            type: StockMovement::TYPE_BAY_REFILL,
            fromLocationId: $fromLocationId,
            fromLocationCode: $fromLocationCode,
            toLocationId: $toLocationId,
            toLocationCode: $toLocationCode,
            notes: "Bay refill from {$fromLocationCode} to {$toLocationCode}",
            metadata: array_merge([
                'refill_operation' => true,
                'auto_selected_source' => $additionalMetadata['auto_selected'] ?? false,
            ], $additionalMetadata)
        );
    }

    /**
     * Create a manual transfer movement record
     */
    public function createManualTransferRecord(
        Product $product,
        User $user,
        int $quantity,
        string $fromLocationCode,
        string $toLocationCode,
        ?string $fromLocationId = null,
        ?string $toLocationId = null,
        ?string $notes = null,
        array $additionalMetadata = []
    ): StockMovement {
        return $this->handle(
            product: $product,
            user: $user,
            quantity: $quantity,
            type: StockMovement::TYPE_MANUAL_TRANSFER,
            fromLocationId: $fromLocationId,
            fromLocationCode: $fromLocationCode,
            toLocationId: $toLocationId,
            toLocationCode: $toLocationCode,
            notes: $notes ?? "Manual transfer from {$fromLocationCode} to {$toLocationCode}",
            metadata: array_merge([
                'manual_operation' => true,
            ], $additionalMetadata)
        );
    }
}