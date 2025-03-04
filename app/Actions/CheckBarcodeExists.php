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
     * If it doesn't, notify all users via the database notification system
     *
     * @return Product|null
     */
    public function handle()
    {
        $product = Product::where('barcode', $this->scan->product)->first();

        if ($product) {
            return $product;
        }

        // Notify users of no SKU found for a barcode
        $this->notifyAllUsers(new NoSkuFound($this->scan));
        $this->markScanAsFailed($this->scan);

        return null;
    }

}