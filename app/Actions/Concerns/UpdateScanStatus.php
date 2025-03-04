<?php

namespace App\Actions\Concerns;

use App\Models\Scan;

trait UpdateScanStatus {

    protected function markScanAsSuccessful(Scan $scan): Scan
    {
        $scan->update([
            'sync_status' => 'synced',
        ]);

        return $scan;
    }

    protected function markScanAsSyncing(Scan $scan): Scan
    {
        $scan->update([
            'sync_status' => 'syncing',
        ]);

        return $scan;
    }

    protected function markScanAsFailed(Scan $scan): Scan
    {
        $scan->update([
            'sync_status' => 'failed'
        ]);

        return $scan;
    }

}