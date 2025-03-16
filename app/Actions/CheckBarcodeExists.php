<?php

namespace App\Actions;

use App\Actions\Contracts\Action;
use App\Models\Product;
use App\Models\Scan;

final class CheckBarcodeExists implements Action
{

    public Scan $scan;

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    /**
     * Takes a barcode and checks if it exists in the database.
     * If it does, return the product.
     * If no product is found then return null
     *
     * @return Product|null
     */
    public function handle()
    {
        // Find the product using the scan product relationship. Else it won't search the other barcodes...
        $product = $this->scan->product;

        if ($product) {
            return $product;
        }

        return null;
    }

}