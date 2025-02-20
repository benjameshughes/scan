<?php

namespace App\Actions;


use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Illuminate\Support\Collection;

class SyncAllPendingScans {

    public Collection $scans;
    /**
     * Create a new action instance.
     */
    public function __construct(Collection $scans)
    {
        $this->scans = $scans;
    }

    /**
     * Handle the action.
     */
    public function handle()
    {
        // Process jobs in chunks of 100 to avoid memory issues
        $this->scans->each(function ($scan) {
            SyncBarcode::dispatch($scan->id)->delay(now()->addMinutes(1));
        })->chunk(10);
    }
}