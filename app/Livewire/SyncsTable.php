<?php

namespace App\Livewire;

use App\Models\Scan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SyncsTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortField = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    // Filters
    #[Url]
    public string $syncStatus = '';

    #[Url]
    public string $errorType = '';

    #[Url]
    public string $submitted = '';

    #[Url]
    public string $action = '';

    #[Url]
    public ?string $dateFrom = null;

    #[Url]
    public ?string $dateTo = null;

    // Bulk selection
    public array $selected = [];

    public bool $selectAll = false;

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSyncStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selected = $this->getQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'syncStatus', 'errorType', 'submitted', 'action', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    // Bulk Actions
    public function retrySync(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Scan::whereIn('id', $this->selected)->update([
            'sync_status' => 'pending',
            'sync_error_message' => null,
            'sync_error_type' => null,
        ]);

        session()->flash('message', count($this->selected).' scans queued for retry.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function retryFailedOnly(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Scan::whereIn('id', $this->selected)
            ->where('sync_status', 'failed')
            ->update([
                'sync_status' => 'pending',
                'sync_error_message' => null,
                'sync_error_type' => null,
            ]);

        session()->flash('message', $count.' failed scans queued for retry.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function markSynced(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Scan::whereIn('id', $this->selected)->update([
            'sync_status' => 'synced',
            'synced_at' => now(),
            'sync_error_message' => null,
            'sync_error_type' => null,
        ]);

        session()->flash('message', count($this->selected).' scans marked as synced.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function clearErrors(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Scan::whereIn('id', $this->selected)->update([
            'sync_error_message' => null,
            'sync_error_type' => null,
        ]);

        session()->flash('message', 'Error information cleared for '.count($this->selected).' scans.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Scan::whereIn('id', $this->selected)->delete();

        session()->flash('message', count($this->selected).' scans deleted.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function delete(int $id): void
    {
        Scan::find($id)?->delete();
        session()->flash('message', 'Scan deleted.');
    }

    protected function getQuery()
    {
        return Scan::query()
            ->when($this->search, fn ($q) => $q->where('barcode', 'like', "%{$this->search}%"))
            ->when($this->syncStatus, fn ($q) => $q->where('sync_status', $this->syncStatus))
            ->when($this->errorType, fn ($q) => $q->where('sync_error_type', $this->errorType))
            ->when($this->submitted !== '', fn ($q) => $q->where('submitted', $this->submitted))
            ->when($this->action, fn ($q) => $q->where('action', $this->action))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $scans = $this->getQuery()->paginate(15);

        return view('livewire.syncs-table', [
            'scans' => $scans,
        ]);
    }
}
