<?php

namespace App\Livewire;

use App\Models\Scan;
use Livewire\Component;

class ScanList extends Component
{
    public object $scans;

    public function mount()
    {
        $this->scans = Scan::all();
    }
    public function render()
    {
        return view('livewire.scan-list');
    }
}
