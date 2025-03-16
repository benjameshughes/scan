<?php

namespace App\Livewire;

use App\Jobs\SyncBarcode;
use App\Models\Scan;
use App\Services\LinnworksApiService;
use GuzzleHttp\Exception\GuzzleException;
use Livewire\Attributes\On;
use Livewire\Component;

class ScanView extends Component
{
    public $scan;
    public $jobStatus;
    public $stockHistory;

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
     * @throws GuzzleException
     */
    public function getStockItemHistory(string $sku)
    {
        // Linnworks
        return LinnworksApiService::getStockItemHistory($sku);
    }

    // Delete sync
    public function delete($id, \Request $request)
    {
        // Find the scan
        $scan = Scan::findOrFail($id);

        if($request->user()->cannot('delete', $scan))
        {
            abort(403);
        }

        // Can the user perform this action?
        $scan->delete();

        redirect('dashboard');
    }

    public function mount(Scan $scan): void
    {
        $this->updateData();
    }
    public function render()
    {
        return view('livewire.scan-view');
    }
}
