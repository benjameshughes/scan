<?php

namespace App\Actions;

use App\Models\Scan;

class GetProductFromScannedBarcode {

    protected int $barcode;

    public function __construct($barcode)
    {
        $this->barcode = $barcode;
    }

    public function handle()
    {
        $tempScan = new Scan(['barcode' => $this->barcode]);
        $product = $tempScan->product;

        if (!$product) {
            return null;
        }

        return $product;
    }

}