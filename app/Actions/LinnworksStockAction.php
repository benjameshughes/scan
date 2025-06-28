<?php

namespace App\Actions;

use App\Models\Scan;

class LinnworksStockAction {

    private int $quantity;

    private Scan $scan;

    public function __construct(Scan $scan, int $quantity)
    {
        $this->scan = $scan;
        $this->quantity = $quantity;
    }

    public function handle(Scan $scan)
    {
        $this->quantity = $scan->quantity;
        $action = $scan->action;
        if($action === 'increase')
        {
            $this->quantity + $this->scan->quantity;
        }

        if($action === 'decrease')
        {
            max(0,$this->quantity - $this->scan->quantity);
        }

        return $this->quantity;
    }

}