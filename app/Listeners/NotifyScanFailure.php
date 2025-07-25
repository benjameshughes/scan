<?php

namespace App\Listeners;

use App\Events\ScanSyncFailed;
use App\Notifications\ScanSyncFailedNotification;
use Illuminate\Support\Facades\Log;

class NotifyScanFailure
{
    public function handle(ScanSyncFailed $event): void
    {
        // Always notify the person who performed the scan
        if ($event->scan->user) {
            Log::channel('inventory')->info('Notifying scanner about their scan sync failure', [
                'scan_id' => $event->scan->id,
                'user_id' => $event->scan->user->id,
                'error_type' => $event->errorType,
            ]);

            $event->scan->user->notify(
                new ScanSyncFailedNotification($event->scan, $event->errorMessage, $event->errorType)
            );
        }
    }
}
