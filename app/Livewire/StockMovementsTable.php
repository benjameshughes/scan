<?php

namespace App\Livewire;

use App\Models\StockMovement;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class StockMovementsTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortField = 'moved_at';

    #[Url]
    public string $sortDirection = 'desc';

    // Filters
    #[Url]
    public ?string $dateFrom = null;

    #[Url]
    public ?string $dateTo = null;

    #[Url]
    public string $movementType = '';

    #[Url]
    public string $locationFilter = '';

    public function mount(): void
    {
        if (! auth()->user()?->can('view stock movements')) {
            abort(403, 'You do not have permission to view stock movements.');
        }

        // Set default date range (last 30 days)
        if (is_null($this->dateFrom)) {
            $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        }
        if (is_null($this->dateTo)) {
            $this->dateTo = now()->format('Y-m-d');
        }
    }

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

    public function clearFilters(): void
    {
        $this->reset(['search', 'movementType', 'locationFilter']);
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function view(int $id): void
    {
        if (! auth()->user()?->can('view stock movements')) {
            abort(403, 'You do not have permission to view stock movements.');
        }

        $this->redirect(route('locations.movements.show', $id), navigate: true);
    }

    public function edit(int $id): void
    {
        if (! auth()->user()?->can('edit stock movements')) {
            abort(403, 'You do not have permission to edit stock movements.');
        }

        $this->redirect(route('locations.movements.edit', $id), navigate: true);
    }

    protected function getQuery()
    {
        return StockMovement::query()
            ->with(['product', 'user', 'fromLocation', 'toLocation'])
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->whereHas('product', fn ($p) => $p->where('sku', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%"))
                    ->orWhere('from_location_code', 'like', "%{$this->search}%")
                    ->orWhere('to_location_code', 'like', "%{$this->search}%");
            }))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('moved_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('moved_at', '<=', $this->dateTo))
            ->when($this->movementType, fn ($q) => $q->where('type', $this->movementType))
            ->when($this->locationFilter, fn ($q) => $q->where(function ($query) {
                $query->where('from_location_code', 'like', "%{$this->locationFilter}%")
                    ->orWhere('to_location_code', 'like', "%{$this->locationFilter}%");
            }))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $movements = $this->getQuery()->paginate(20);

        return view('livewire.stock-movements-table', [
            'movements' => $movements,
            'canEdit' => auth()->user()?->can('edit stock movements'),
            'canCreate' => auth()->user()?->can('create stock movements'),
        ]);
    }
}
