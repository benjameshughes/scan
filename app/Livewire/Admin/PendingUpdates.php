<?php

namespace App\Livewire\Admin;

use App\Models\PendingProductUpdate;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PendingUpdates extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filter = 'pending';

    #[Url]
    public string $changeType = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedUpdates = [];

    public bool $selectAll = false;

    /**
     * Reset pagination when filters change
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selectedUpdates = [];
        $this->selectAll = false;
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
        $this->selectedUpdates = [];
        $this->selectAll = false;
    }

    public function updatedChangeType(): void
    {
        $this->resetPage();
        $this->selectedUpdates = [];
        $this->selectAll = false;
    }

    /**
     * Sort by field
     */
    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    /**
     * Update the select all checkbox
     */
    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedUpdates = $this->getFilteredQuery()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedUpdates = [];
        }
    }

    /**
     * Approve a single update
     */
    public function approveUpdate($updateId): void
    {
        $update = PendingProductUpdate::findOrFail($updateId);

        DB::transaction(function () use ($update) {
            $product = $update->product;
            $linnworksData = $update->linnworks_data;

            $updateData = [
                'name' => $linnworksData['ItemTitle'] ?? $product->name,
                'quantity' => $linnworksData['StockLevel'] ?? $product->quantity,
                'last_synced_at' => now(),
            ];

            if (isset($linnworksData['Barcode'])) {
                $updateData['barcode'] = $linnworksData['Barcode'];
            }

            $product->update($updateData);

            $update->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        session()->flash('message', "Approved: {$update->product->name}");
    }

    /**
     * Reject a single update
     */
    public function rejectUpdate($updateId): void
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
    public function bulkApprove(): void
    {
        $updates = PendingProductUpdate::whereIn('id', $this->selectedUpdates)
            ->where('status', 'pending')
            ->get();

        $count = $updates->count();

        DB::transaction(function () use ($updates) {
            foreach ($updates as $update) {
                $product = $update->product;
                $linnworksData = $update->linnworks_data;

                $updateData = [
                    'name' => $linnworksData['ItemTitle'] ?? $product->name,
                    'quantity' => $linnworksData['StockLevel'] ?? $product->quantity,
                    'last_synced_at' => now(),
                ];

                if (isset($linnworksData['Barcode'])) {
                    $updateData['barcode'] = $linnworksData['Barcode'];
                }

                $product->update($updateData);

                $update->update([
                    'status' => 'approved',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);
            }
        });

        $this->selectedUpdates = [];
        $this->selectAll = false;

        session()->flash('message', "Approved {$count} updates");
    }

    /**
     * Bulk reject selected updates
     */
    public function bulkReject(): void
    {
        $count = PendingProductUpdate::whereIn('id', $this->selectedUpdates)
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

        $this->selectedUpdates = [];
        $this->selectAll = false;

        session()->flash('message', "Rejected {$count} updates");
    }

    /**
     * Get available change types from current data
     */
    public function getChangeTypes(): array
    {
        return PendingProductUpdate::where('status', $this->filter)
            ->get()
            ->flatMap(fn ($update) => array_keys($update->changes_detected ?? []))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get filtered updates query
     */
    private function getFilteredQuery()
    {
        return PendingProductUpdate::query()
            ->with(['product', 'reviewer'])
            ->where('status', $this->filter)
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%")
                        ->orWhere('barcode', 'like', "%{$this->search}%");
                });
            })
            ->when($this->changeType, function ($query) {
                $query->whereJsonContainsKey("changes_detected->{$this->changeType}");
            });
    }

    public function render()
    {
        $updates = $this->getFilteredQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(20);

        return view('livewire.admin.pending-updates', [
            'updates' => $updates,
            'pendingCount' => PendingProductUpdate::where('status', 'pending')->count(),
            'autoAcceptedCount' => PendingProductUpdate::where('status', 'auto_accepted')->count(),
            'approvedCount' => PendingProductUpdate::where('status', 'approved')->count(),
            'rejectedCount' => PendingProductUpdate::where('status', 'rejected')->count(),
            'changeTypes' => $this->getChangeTypes(),
        ]);
    }
}
