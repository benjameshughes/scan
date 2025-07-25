<?php

namespace App\Events;

use App\Models\StockMovement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefillOperationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public StockMovement $stockMovement,
        public string $errorMessage,
        public string $errorType = 'sync_failure'
    ) {}
}
