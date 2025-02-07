<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\LinnworksApiService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchLinnworksInvetory implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Dispatchable, SerializesModels;

    protected int $pageNumber;
    protected int $entriesPerPage;

    /**
     * Create a new job instance.
     */
    public function __construct(int $pageNumber, int $entriesPerPage = 200)
    {
        $this->pageNumber = $pageNumber;
        $this->entriesPerPage = $entriesPerPage;
    }

    /**
     * Execute the job.
     * @throws \Exception
     * @throws GuzzleException
     */
    public function handle(LinnworksApiService $linnworks): void
    {
        $inventoryPage = $linnworks->getInventory($this->pageNumber, $this->entriesPerPage);

        if($inventoryPage)
        {
            $this->storeInventory($inventoryPage);
        } else {
            throw new \Exception('Unable to fetch inventory');
        }
    }

    /**
     * Store the inventory in the database. Either creating a new record or updating an existing one based on SKU
     * @param array $inventory
     */

    protected function storeInventory(array $inventory): void
    {
        foreach($inventory as $inventoryItem) {
            Product::updateOrCreate(
                [
                    'sku' => $inventoryItem['ItemNumber'],
                ],
                [
                    'name' => $inventoryItem['ItemTitle'],
                    'barcode' => $inventoryItem['BarcodeNumber'],
                    'quantity' => $inventoryItem['StockLevels'][0]['StockLevel'] ?? 0,
                ]
            );
            Log::channel('inventory')->info('Updated product ' . $inventoryItem['ItemNumber']);
        }
    }
}
