<?php

namespace App\Livewire;

use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Events\JobStatus;

class ScanView extends Component
{

    public $scan;
    public $jobStatus;

    #[On('syncedBarcode')]
    public function updateData()
    {
        $this->scan = Scan::findOrFail($this->scan->id);
        $this->jobStatus = $this->jobStatus ?? 'Not Submitted';
    }

    // Get job status
    public function jobStatusUpdate($status, $scan)
    {
        $this->jobStatus = $status;
        $this->scan = $scan;
    }

    // Sync the job by dispatching the sync job
    /**
     * Initiates barcode synchronization with a 1-minute delay to allow for potential batching
     * @throws \Exception If status update fails
     */
    public function sync()
    {
        $this->jobStatus = 'Syncing';
        try {
            if (!$this->scan->update(['sync_status' => 'Syncing'])) {
                throw new \Exception('Failed to update sync status');
            }
            SyncBarcode::dispatch($this->scan->id)->delay(now()->addMinute());
        } catch (\Exception $e) {
            $this->jobStatus = 'Error';
            throw $e;
        }
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
