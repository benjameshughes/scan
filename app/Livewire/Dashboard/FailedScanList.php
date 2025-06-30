<?php

namespace App\Livewire\Dashboard;

use App\Models\Scan;
use Livewire\Component;
use Livewire\WithPagination;

class FailedScanList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.dashboard.failed-scan-list', [
            'scans' => Scan::where(function ($query) {
                $query->whereNull('submitted_at')
                      ->orWhere('sync_status', 'failed');
            })->orderBy('created_at', 'desc')->paginate(10),
        ]);
    }
}
