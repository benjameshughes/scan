<?php

namespace App\Livewire\Scan;

use App\Models\Scan;
use Livewire\Component;

class Edit extends Component
{

    public Scan $scan;
    public array $scanData = [];

    /**
     * Fill in the details of the scan
     */
    public function update()
    {
        $this->validate();

        $this->scan->update($this->scanData);
    }

    public function mount(Scan $scan)
    {
        $this->scanData = $scan->only([
            'barcode',
            'sku',
            'status',
            'sync_status',
            'quantity'
        ]);
    }

    public function render()
    {
        return view('livewire.scan.edit');
    }
}
