<?php

namespace App\Actions;

use App\Actions\Contracts\Action;
use App\Models\Scan;
use App\Notifications\NoSkuFound;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;

final class SyncBarcode implements Action {

    private Scan $scan;
    private LinnworksApiService $linnworks;

    public function __construct(Scan $scan) {
        $this->scan = $scan;
        $this->linnworks = new LinnworksApiService();
    }

    /**
     * @throws \Exception
     */
    public function handle() {

        // Let's first check the scan has not already been submitted as resubmitted would be a bit silly
        if($this->scan->submitted === true)
        {
            return;
        }

        // Check barcode exists and has a SKU
        $product = (new CheckBarcodeExists($this->scan->barcode))->handle();

        if(!$product)
        {
            $this->scan->update([
                'sync_status' => 'failed',
            ]);

            auth()->user()->notify(new NoSkuFound($this->scan->id));

            return;
        }

        $sku = $product->sku;

        // Update the scan sync status
        $this->scan->update([
            'sync_status' => 'syncing',
        ]);

        // Get the stock level from Linnworks using the SKU
        $lwStockLevel = $this->linnworks->getStockLevel($sku);
        Log::channel('sku_lookup')->info('Found Linnworks stock level ' . $lwStockLevel . ' for SKU ' . $sku. ' Scan quantity ' . $this->scan->quantity);

        // Minus the scan quantity from the stock level, if less than 0 set to 0
        (int)$quantity =  max(0, (int)$lwStockLevel - (int)$this->scan->quantity);
        Log::channel('sku_lookup')->info('Updated Linnworks stock level ' . $quantity . ' for SKU ' . $sku. ' Scan quantity ' . $this->scan->quantity);

        // Update the stock level
        $this->linnworks->updateStockLevel($sku, $quantity);

        // Mark the scan as submitted
        $this->scan->update(['submitted' => true, 'submitted_at' => now(),'sync_status' => 'synced']);
        $this->scan->save();


    }

}