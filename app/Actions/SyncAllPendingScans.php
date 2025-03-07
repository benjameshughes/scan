<?php

namespace App\Actions;

use App\Actions\Contracts\Action;
use App\Jobs\SyncBarcode;
use Illuminate\Support\Collection;

final class SyncAllPendingScans implements Action
{
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
        // Process jobs in chunks of 10 to avoid memory issues
        $this->scans->each(function ($scan) {
            SyncBarcode::dispatch($scan);
        })->chunk(10);
    }
}