<?php

namespace App\Actions\Stock;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ValidateStockTransferAction
{
    public function handle(
        User $user,
        Product $product,
        int $quantity,
        ?string $fromLocationId = null,
        ?int $availableStock = null,
        string $operationType = 'refill'
    ): array {
        $this->validatePermissions($user, $operationType);
        $this->validateQuantity($quantity, $availableStock);
        $this->validateProduct($product);
        $this->validateLocation($fromLocationId);

        Log::channel('inventory')->info('Stock transfer validation passed', [
            'user_id' => $user->id,
            'product_sku' => $product->sku,
            'quantity' => $quantity,
            'from_location_id' => $fromLocationId,
            'available_stock' => $availableStock,
            'operation_type' => $operationType,
        ]);

        return [
            'valid' => true,
            'user' => $user,
            'product' => $product,
            'quantity' => $quantity,
            'from_location_id' => $fromLocationId,
            'available_stock' => $availableStock,
        ];
    }

    private function validatePermissions(User $user, string $operationType): void
    {
        $requiredPermissions = [
            'refill' => 'refill bays',
            'transfer' => 'create stock movements',
            'adjustment' => 'create stock movements',
        ];

        $permission = $requiredPermissions[$operationType] ?? 'create stock movements';

        if (! $user->can($permission)) {
            throw ValidationException::withMessages([
                'permissions' => ["You do not have permission to perform {$operationType} operations."],
            ]);
        }
    }

    private function validateQuantity(int $quantity, ?int $availableStock = null): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => ['Quantity must be at least 1.'],
            ]);
        }

        if ($availableStock !== null && $quantity > $availableStock) {
            throw ValidationException::withMessages([
                'quantity' => ["Quantity cannot exceed available stock ({$availableStock})."],
            ]);
        }
    }

    private function validateProduct(Product $product): void
    {
        if (! $product->sku || trim($product->sku) === '') {
            throw ValidationException::withMessages([
                'product' => ['Product must have a valid SKU.'],
            ]);
        }
    }

    private function validateLocation(?string $fromLocationId): void
    {
        if ($fromLocationId && ! is_string($fromLocationId)) {
            throw ValidationException::withMessages([
                'location' => ['From location ID must be a valid string.'],
            ]);
        }
    }
}
