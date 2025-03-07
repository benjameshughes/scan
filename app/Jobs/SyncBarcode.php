<?php

namespace App\Jobs;

use App\Models\Scan;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Actions\SyncBarcodeAction;

class SyncBarcode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public Scan $scan;

    /**
     * Create a new job instance.
     */
    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle()
    {
        // Try using the sync barcode action
        (new SyncBarcodeAction($this->scan))->handle();
    }
}