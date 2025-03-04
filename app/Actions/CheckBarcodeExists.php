<?php

namespace App\Actions;

use App\Actions\Contracts\Action;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use App\Notifications\NoSkuFound;
use Illuminate\Support\Facades\Log;
use App\Actions\Concerns\SendNotifications;
use App\Actions\Concerns\UpdateScanStatus;

final class CheckBarcodeExists implements Action
{
    use SendNotifications;
    use UpdateScanStatus;

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
        $product = Product::where('barcode', $this->scan->barcode)->first();

        if ($product) {
            return $product;
        }

        return null;
    }

}