<?php

namespace App\Jobs;

use App\Events\JobStatus;
use App\Models\Product;
use App\Models\Scan;
use App\Services\LinnworksApiService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

class SyncBarcode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public int $scanId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $scanId)
    {
        $this->scanId = $scanId;;
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle(): void
    {
        // Initialise Linny
        $linnworks = new LinnworksApiService();

        // Load the scan and product data
        $scan = Scan::findOrFail($this->scanId);
        $sku = $scan->product->sku;
        Log::channel('sku_lookup')->info('Found SKU ' . $sku . 'for scan id ' . $scan->id);

        // Check to see if they are in the database
        if (!$scan || !$sku) {
            throw new \Exception('Product not found for barcode' . $scan->barcode);
        }

        // Get Linnworks Stock Level from SKU
        $lwStockLevel = $linnworks->getStockLevel($sku);
        Log::channel('sku_lookup')->info('Found Linnworks stock level ' . $lwStockLevel . ' for SKU ' . $sku. ' Scan quantity ' . $scan->quantity);

        // Minus the scan quantity from the stock level, if less than 0 set to 0
        (int)$quantity =  max(0, (int)$lwStockLevel - (int)$scan->quantity);
        Log::channel('sku_lookup')->info('Updated Linnworks stock level ' . $quantity . ' for SKU ' . $sku. ' Scan quantity ' . $scan->quantity);

        // Update the stock level
        $linnworks->updateStockLevel($sku, $quantity);

        // Mark the scan as submitted
        $scan->submitted = true;
        $scan->submitted_at = now();
        $scan->save();
    }

    /**
     * Retry the job three times before removing it from the queue.
     */
    public function tries(): int
    {
        return 3;
    }
}