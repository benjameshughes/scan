<?php

namespace App\Jobs;

use App\Events\JobStatus;
use App\Models\Scan;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Livewire\Livewire;

class SyncBarcode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    private Scan $scan;

    /**
     * Create a new job instance.
     */
    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Event Dispatch
        event(new JobStatus('Syncing', $this->scan->id));
        // Find the barcode in the database
        $scan = Scan::findOrFail($this->scan->id);

        // Update the submitted_at timestamp
        $scan->update([
            'submitted' => true,
            'submitted_at' => now(),
        ]);
        event(new JobStatus('Synced', $this->scan->id));
        // Dispatch a livewire event
        Livewire::dispatch('syncedBarcode', $this->scan);
    }
}