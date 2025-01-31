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

    public function mount(Scan $scan): void
    {
        $this->updateData();
    }
    public function render()
    {
        return view('livewire.scan-view');
    }
}
