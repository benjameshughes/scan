<?php

namespace App\Livewire\Dashboard;

use App\Models\Scan;
use Livewire\Component;
use Livewire\WithPagination;

class FailedScanList extends Component
{
    use WithPagination;

    public function markAsSubmitted(int $id): void
    {
        $scan = Scan::find($id);

        if ($scan) {
            $scan->update([
                'submitted_at' => now(),
                'sync_status' => 'synced',
            ]);

            session()->flash('message', 'Scan marked as submitted.');
        }
    }

    public function retrySync(int $id): void
    {
        $scan = Scan::find($id);

        if ($scan) {
            $scan->update([
                'sync_status' => 'pending',
                'sync_error_message' => null,
                'sync_error_type' => null,
            ]);

            session()->flash('message', 'Scan queued for retry.');
        }
    }

    public function render()
    {
        return view('livewire.dashboard.failed-scan-list', [
            'scans' => Scan::where(function ($query) {
                $query->whereNull('submitted_at')
                    ->orWhere('sync_status', 'failed');
            })
                ->with('product')
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ]);
    }
}
