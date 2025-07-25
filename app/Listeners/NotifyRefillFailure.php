<?php

namespace App\Listeners;

use App\Events\RefillOperationFailed;
use App\Models\User;
use App\Notifications\RefillSyncFailedNotification;
use Illuminate\Support\Facades\Log;

class NotifyRefillFailure
{
    public function handle(RefillOperationFailed $event): void
    {
        // Get users with refill bays permission
        $refillUsers = User::permission('refill bays')->get();

        // Fallback to admins if no specific permission holders
        if ($refillUsers->isEmpty()) {
            $refillUsers = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();
        }

        Log::channel('inventory')->info('Notifying users about refill operation failure', [
            'stock_movement_id' => $event->stockMovement->id,
            'error_type' => $event->errorType,
            'user_count' => $refillUsers->count(),
        ]);

        $notification = new RefillSyncFailedNotification(
            $event->stockMovement,
            $event->errorMessage,
            $event->errorType
        );

        foreach ($refillUsers as $user) {
            $user->notify($notification);
        }
    }
}
