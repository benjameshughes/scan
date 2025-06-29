<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public string $search = '';

    public string $sortField = 'sku';

    public string $sortDirection = 'asc';

    protected array $queryString = ['search', 'sortField', 'sortDirection'];

    public array $perPageOptions = [10, 25, 50, 100];

    public $filters = '';

    public function sortBy($field)
    {
        if ($this->sortField = $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

        } else {
            $this->sortDirection = 'asc;';
        }
    }

    public function render()
    {
        $products = Product::search(['name', 'sku', 'barcode'], $this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.products.index', [
            'products' => $products,
            'actions' => $this->getActions(),
            'columns' => $this->getColumns(),
            'perPageOptions' => $this->perPageOptions,
            'sortDirection' => $this->sortDirection,
            'filters' => $this->getFilters(),
        ]);
    }

    public function getFilters()
    {
        return [
            //
        ];
    }

    private function getActions()
    {
        return [
            ['url' => route('products.show', '1'), 'label' => 'View', 'button-colour' => 'blue'],
            ['url' => route('products.edit', '1'), 'label' => 'Edit', 'button-colour' => 'orange'],
        ];
    }

    private function getColumns()
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'sku', 'label' => 'SKU'],
            ['key' => 'barcode', 'label' => 'Barcode'],
            ['key' => 'quantity', 'label' => 'Quantity'],
        ];
    }
}
