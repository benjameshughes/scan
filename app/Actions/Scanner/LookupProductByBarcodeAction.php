<?php

namespace App\Actions\Scanner;

use App\Models\Product;

class LookupProductByBarcodeAction
{
    /**
     * Lookup a product by barcode
     *
     * Uses the Product model's byBarcode scope to search across all barcode fields.
     * Returns the first matching product or null if not found.
     */
    public function handle(string $barcode): ?Product
    {
        if (empty($barcode)) {
            return null;
        }

        return Product::byBarcode($barcode)->first();
    }

    /**
     * Check if a barcode exists in the system
     */
    public function exists(string $barcode): bool
    {
        return $this->handle($barcode) !== null;
    }

    /**
     * Lookup product and return ID only (useful for serialization)
     */
    public function getProductId(string $barcode): ?int
    {
        $product = $this->handle($barcode);

        return $product?->id;
    }

    /**
     * Lookup multiple barcodes at once (batch operation)
     */
    public function handleBatch(array $barcodes): array
    {
        $products = [];

        foreach ($barcodes as $barcode) {
            $product = $this->handle($barcode);

            if ($product) {
                $products[$barcode] = $product;
            }
        }

        return $products;
    }
}
