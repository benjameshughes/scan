<?php

namespace App\Tables;

use AllowDynamicProperties;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use App\Tables\Concerns\HasColumns;
use App\Tables\Concerns\HasSearch;
use App\Tables\Concerns\HasFilters;

#[AllowDynamicProperties] class TableComponent extends Component
{
    use WithPagination;
    use HasColumns;
    use HasSearch;
    use HasFilters;

    public string $tableClass;
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    public ?array $filters = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function boot()
    {
        // This ensures the table is initialized before any other lifecycle methods
        $this->table = new ($this->tableClass)();
    }

    public function mount(string $tableClass)
    {
        $this->tableClass = $tableClass;
        $this->table = new $tableClass();
    }

    protected function getTable(): Table
    {
        // Ensure table is always initialized
        if (!isset($this->table)) {
            $this->table = new ($this->tableClass)();
        }
        return $this->table;
    }

    protected function getQuery(): Builder
    {
        $query = $this->getTable()->query();

        if ($this->search && method_exists($this->getTable(), 'getSearchableColumns')) {
            $query = $this->applySearch($query);
        }

        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function render()
    {
        $data = $this->getQuery()->paginate($this->perPage);

        return view('components.tables.table', [
            'data' => $data,
            'columns' => $this->getTable()->columns(),
        ]);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function hasSearch(): bool
    {
        return method_exists($this->getTable(), 'getSearchableColumns') &&
            !empty($this->getTable()->getSearchableColumns());
    }

    public function hasFilters(): bool
    {
        return method_exists($this->getTable(), 'getFilterableColumns') &&
            !empty($this->getTable()->getFilters());
    }

    public function getPerPageOptions(): array
    {
        $this->perPageOptions = $perPageOptions;
    }
}