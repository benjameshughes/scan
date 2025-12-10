<?php

namespace App\Actions;

use App\Models\Product;

class GetProductFromScannedBarcode
{
    public function handle(string $barcode): ?Product
    {
        // Search for product by checking all barcode fields
        $product = Product::where('barcode', $barcode)
            ->orWhere('barcode_2', $barcode)
            ->orWhere('barcode_3', $barcode)
            ->first();

        return $product;
    }
}
