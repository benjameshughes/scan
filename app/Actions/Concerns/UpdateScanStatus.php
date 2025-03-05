<?php

namespace App\Actions\Concerns;

use App\Models\Scan;

trait UpdateScanStatus {

    protected function markScanAsSuccessful(Scan $scan): Scan
    {
        $scan->update([
            'sync_status' => 'synced',
            'updated_at' => now(),
        ]);

        return $scan;
    }

    protected function markScanAsSyncing(Scan $scan): Scan
    {
        $scan->update([
            'sync_status' => 'syncing',
            'updated_at' => now(),
        ]);

        return $scan;
    }

    protected function markScanAsFailed(Scan $scan): Scan
    {
        $scan->update([
            'sync_status' => 'failed',
            'updated_at' => now(),
        ]);

        return $scan;
    }

}