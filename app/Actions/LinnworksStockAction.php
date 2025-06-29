<?php

namespace App\Actions;

use App\Models\Scan;

class LinnworksStockAction
{
    private int $currentStockLevel;

    private Scan $scan;

    public function __construct(Scan $scan, int $currentStockLevel)
    {
        $this->scan = $scan;
        $this->currentStockLevel = $currentStockLevel;
    }

    public function handle(): int
    {
        $action = $this->scan->action;

        if ($action === 'increase') {
            return $this->currentStockLevel + $this->scan->quantity;
        }

        // Default to decrease for 'decrease' or null action
        return max(0, $this->currentStockLevel - $this->scan->quantity);
    }
}
