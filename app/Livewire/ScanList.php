<?php

namespace App\Livewire;

use App\Models\Scan;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ScanList extends Component
{
    public object $scans;

    #[Computed()]
    public function aggregated()
    {
        $aggregatedScans = Scan::query()
            ->select('barcode')
            ->groupBy('barcode')
            ->get()
            ->map(function($scan) {
                // Manually sum the quantities for each barcode
                $scan->total_quantity = Scan::where('barcode', $scan->barcode)->sum('quantity');
                return $scan;
            });

        return $this->scans = $aggregatedScans;
    }

    #[Computed()]
    public function all()
    {
        return $this->scans = Scan::all();
    }

    // Selector between aggregated and all scans
    public function getSelected()
    {
        return $this->selected ?? 'aggregated';
    }

    public function mount()
    {
        $this->scans = Scan::all();
    }
    public function render()
    {
        return view('livewire.scan-list');
    }
}
