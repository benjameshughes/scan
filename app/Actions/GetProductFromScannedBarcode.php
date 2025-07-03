<?php

namespace App\Actions;

use App\Models\Product;

class GetProductFromScannedBarcode
{
    protected string $barcode;

    public function __construct($barcode)
    {
        $this->barcode = (string) $barcode;
    }

    public function handle()
    {
        // Search for product by checking all barcode fields
        $product = Product::where('barcode', $this->barcode)
            ->orWhere('barcode_2', $this->barcode)
            ->orWhere('barcode_3', $this->barcode)
            ->first();

        return $product;
    }
}
