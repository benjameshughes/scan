<?php

namespace App\Livewire\Scans;

use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use Livewire\Component;

class Show extends Component
{
    public Scan $scan;

    public ?Product $product = null;

    public function mount(Scan $scan)
    {
        $this->scan = $scan;
        $this->loadProduct();
    }

    public function loadProduct()
    {
        $this->product = Product::where('barcode', $this->scan->barcode)
            ->orWhere('barcode_2', $this->scan->barcode)
            ->orWhere('barcode_3', $this->scan->barcode)
            ->first();
    }

    public function resync()
    {
        // Only allow resync for failed or pending scans
        if (in_array($this->scan->sync_status, ['failed', 'pending', null])) {
            // Update status to pending
            $this->scan->update(['sync_status' => 'pending']);

            // Dispatch sync job
            SyncBarcode::dispatch($this->scan);

            session()->flash('message', 'Scan queued for resync successfully!');

            // Refresh the scan to get updated status
            $this->scan->refresh();
        } else {
            session()->flash('error', 'Only failed or pending scans can be resynced.');
        }
    }

    public function render()
    {
        return view('livewire.scans.show');
    }
}
