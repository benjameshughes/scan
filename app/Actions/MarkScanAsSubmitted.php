<?php

namespace App\Actions;

use App\Models\Scan;
use App\Actions\Contracts\Action;

/**
 * Gives users the ability to mark a scan as submitted.
 */
final class MarkScanAsSubmitted implements Action
{

    public Scan $scan;

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    public function handle()
    {
        // Mark the scan as submitted.
        return $this->scan->update([
            'submitted' => true,
            'submitted_at' => now(),
            'sync_status' => 'synced',
        ]);
    }

}