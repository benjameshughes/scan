<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortField = 'name';

    #[Url]
    public string $sortDirection = 'asc';

    // Filters
    #[Url]
    public string $hasBarcode2 = '';

    #[Url]
    public ?string $updatedAfter = null;

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

    public function updatedHasBarcode2(): void
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
        $this->reset(['search', 'hasBarcode2', 'updatedAfter']);
        $this->resetPage();
    }

    // Bulk Actions
    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Product::whereIn('id', $this->selected)->delete();

        session()->flash('message', count($this->selected).' products deleted.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function syncSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // TODO: Dispatch sync jobs for selected products
        session()->flash('message', count($this->selected).' products queued for sync.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function delete(int $id): void
    {
        Product::find($id)?->delete();
        session()->flash('message', 'Product deleted.');
    }

    protected function getQuery()
    {
        return Product::query()
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('sku', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%")
                    ->orWhere('barcode_2', 'like', "%{$this->search}%")
                    ->orWhere('barcode_3', 'like', "%{$this->search}%");
            }))
            ->when($this->hasBarcode2 === '1', fn ($q) => $q->whereNotNull('barcode_2'))
            ->when($this->hasBarcode2 === '0', fn ($q) => $q->whereNull('barcode_2'))
            ->when($this->updatedAfter, fn ($q) => $q->whereDate('updated_at', '>=', $this->updatedAfter))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $products = $this->getQuery()->paginate(15);

        return view('livewire.products-table', [
            'products' => $products,
        ]);
    }
}
