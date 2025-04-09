<?php

namespace App\Livewire;

use App\Jobs\SyncBarcode;
use App\Models\Scan;
use App\Services\LinnworksApiService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class ScanView extends Component
{
    public $scan;
    public $jobStatus;
    public $stockHistory = null;
    public $isLoadingHistory = false;
    public $errorMessage = null;

    protected $linnworksService;

    public function boot(LinnworksApiService $linnworksService)
    {
        $this->linnworksService = $linnworksService;
    }

    #[On('syncedBarcode')]
    public function updateData()
    {
        $this->scan = Scan::findOrFail($this->scan->id);
    }

    /**
     * Initiates barcode synchronization with a 1-minute delay to allow for potential batching
     * @throws \Exception If status update fails
     */
    public function sync()
    {
        // Use the SyncBarcodeAction action to initiate the sync job
        SyncBarcode::dispatch($this->scan);
    }

    /**
     * Get stock item history for a SKU
     *
     * @param string $sku The product SKU
     */
    public function getStockItemHistory(string $sku)
    {
        $this->isLoadingHistory = true;
        $this->errorMessage = null;
        $this->stockHistory = null;

        try {
            Log::info("Fetching stock history for SKU: $sku");

            // Get the stock history from the Linnworks API
            $history = $this->linnworksService->getStockItemHistory($sku);

            // Store the history data
            $this->stockHistory = $history;

            Log::info("Retrieved stock history for SKU: $sku");
        } catch (\Exception $e) {
            Log::error("Failed to get stock history for SKU: $sku - " . $e->getMessage());
            $this->errorMessage = "Failed to load stock history: " . $e->getMessage();
        } finally {
            $this->isLoadingHistory = false;
        }
    }

    /**
     * Delete a scan record
     */
    public function delete($id)
    {
        try {
            // Find the scan
            $scan = Scan::findOrFail($id);

            // Check authorization
            if(auth()->user()->cannot('delete', $scan)) {
                $this->addError('delete', 'You are not authorized to delete this scan.');
                return;
            }

            // Delete the scan
            $scan->delete();

            // Redirect to dashboard
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            Log::error("Failed to delete scan ID: $id - " . $e->getMessage());
            $this->addError('delete', 'Failed to delete scan: ' . $e->getMessage());
        }
    }

    public function mount(Scan $scan): void
    {
        $this->scan = $scan;
    }

    public function render()
    {
        return view('livewire.scan-view');
    }
}
