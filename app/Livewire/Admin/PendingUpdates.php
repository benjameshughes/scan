<?php

namespace App\Livewire\Admin;

use App\Models\PendingProductUpdate;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PendingUpdates extends Component
{
    use WithPagination;

    public $selectedUpdates = [];

    public $filter = 'pending';

    public $selectAll = false;

    protected $queryString = ['filter'];

    /**
     * Update the select all checkbox
     */
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUpdates = $this->getFilteredUpdates()
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedUpdates = [];
        }
    }

    /**
     * Approve a single update
     */
    public function approveUpdate($updateId)
    {
        $update = PendingProductUpdate::findOrFail($updateId);

        DB::transaction(function () use ($update) {
            // Apply the changes
            $product = $update->product;
            $linnworksData = $update->linnworks_data;

            // Update product with Linnworks data
            $updateData = [
                'name' => $linnworksData['ItemTitle'] ?? $product->name,
                'quantity' => $linnworksData['StockLevel'] ?? $product->quantity,
                'last_synced_at' => now(),
            ];

            // Update barcodes if they exist in the Linnworks data
            if (isset($linnworksData['Barcode'])) {
                $updateData['barcode'] = $linnworksData['Barcode'];
            }

            $product->update($updateData);

            // Mark update as approved
            $update->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        session()->flash('message', "Updated product: {$update->product->name}");
    }

    /**
     * Reject a single update
     */
    public function rejectUpdate($updateId)
    {
        $update = PendingProductUpdate::findOrFail($updateId);

        $update->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        session()->flash('message', 'Update rejected');
    }

    /**
     * Bulk approve selected updates
     */
    public function bulkApprove()
    {
        $updates = PendingProductUpdate::whereIn('id', $this->selectedUpdates)
            ->where('status', 'pending')
            ->get();

        DB::transaction(function () use ($updates) {
            foreach ($updates as $update) {
                $this->approveUpdate($update->id);
            }
        });

        $this->selectedUpdates = [];
        $this->selectAll = false;

        session()->flash('message', "Approved {$updates->count()} updates");
    }

    /**
     * Bulk reject selected updates
     */
    public function bulkReject()
    {
        $updates = PendingProductUpdate::whereIn('id', $this->selectedUpdates)
            ->where('status', 'pending')
            ->get();

        foreach ($updates as $update) {
            $this->rejectUpdate($update->id);
        }

        $this->selectedUpdates = [];
        $this->selectAll = false;

        session()->flash('message', "Rejected {$updates->count()} updates");
    }

    /**
     * Get filtered updates query
     */
    private function getFilteredUpdates()
    {
        $query = PendingProductUpdate::with(['product', 'reviewer']);

        // Include accepter relationship for auto-accepted items
        if ($this->filter === 'auto_accepted') {
            $query->with('accepter');
        }

        return $query->where('status', $this->filter);
    }

    public function render()
    {
        $updates = $this->getFilteredUpdates()
            ->latest()
            ->paginate(20);

        $pendingCount = PendingProductUpdate::where('status', 'pending')->count();
        $autoAcceptedCount = PendingProductUpdate::where('status', 'auto_accepted')->count();
        $approvedCount = PendingProductUpdate::where('status', 'approved')->count();
        $rejectedCount = PendingProductUpdate::where('status', 'rejected')->count();

        return view('livewire.admin.pending-updates', [
            'updates' => $updates,
            'pendingCount' => $pendingCount,
            'autoAcceptedCount' => $autoAcceptedCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ]);
    }
}
