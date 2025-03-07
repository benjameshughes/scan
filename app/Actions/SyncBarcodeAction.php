<?php

namespace App\Actions;

use App\Actions\Concerns\SendNotifications;
use App\Actions\Concerns\UpdateScanStatus;
use App\Actions\Contracts\Action;
use App\Models\Scan;
use App\Notifications\NoSkuFound;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;

final class SyncBarcodeAction implements Action
{
    use SendNotifications, UpdateScanStatus;

    public Scan $scan;

    public LinnworksApiService $linnworks;

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
        $this->linnworks = new LinnworksApiService;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        // Let's first check the scan has not already been submitted as resubmitted would be a bit silly
        if ($this->scan->submitted === true) {
            return;
        }

        $this->updateScanStatus($this->scan, 'boobies');

        // Check barcode exists and has a SKU, if null stop
        $product = (new CheckBarcodeExists($this->scan))->handle();
        Log::channel('inventory')->info($product);

        if ($product === null) {
            // Notify users of no SKU found for a barcode
            $this->notifyAllUsers(new NoSkuFound($this->scan));
            $this->markScanAsFailed($this->scan);
            Log::channel('inventory')->info('No product found for ' . $this->scan->barcode);

            return;
        }

        // Get the SKU of the product
        $sku = $product->sku;
        Log::channel('sku_lookup')->info($sku . ' ' . now());

        // Get the stock level from Linnworks using the SKU
        $lwStockLevel = $this->linnworks->getStockLevel($sku);
        Log::channel('sku_lookup')->info('Found Linnworks stock level '.$lwStockLevel.' for SKU '.$sku.' Scan quantity '.$this->scan->quantity);

        // Minus the scan quantity from the stock level, if less than 0 set to 0
        (int) $quantity = max(0, (int) $lwStockLevel - (int) $this->scan->quantity);
        Log::channel('sku_lookup')->info('Updated Linnworks stock level '.$quantity.' for SKU '.$sku.' Scan quantity '.$this->scan->quantity);

        // Update the stock level
        $this->linnworks->updateStockLevel($sku, $quantity);

        // Update scan as synced
        $this->markScanAsSuccessful($this->scan);

    }
}
