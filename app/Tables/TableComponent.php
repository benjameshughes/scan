<?php

namespace App\Tables;

use Livewire\Component;
use Livewire\WithPagination;
use App\Tables\Concerns\HasSearch;

abstract class TableComponent extends Component
{
    use WithPagination, HasSearch;

    public string $search = '';
    public int $perPage = 10;
    public string $sortField = '';
    public string $sortDirection = 'asc';
    public array $selectedRecords = [];
    public array $filters = [];

    // Each table must implement this Filament-style
    abstract public function table(Table $table): Table;

    protected function getTable(): Table
    {
        return $this->table(Table::make());
    }

    public function mount(): void
    {
        $table = $this->getTable();

        if (empty($this->sortField)) {
            $this->sortField = $table->getDefaultSortField();
            $this->sortDirection = $table->getDefaultSortDirection();
        }
    }

    protected function getQuery()
    {
        $query = $this->getTable()->getQuery();

        // Apply search if available
        if ($this->hasSearch() && !empty($this->search)) {
            $query = $this->applySearch($query);
        }

        // Apply sorting
        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query;
    }

    public function render()
    {
        $data = $this->getQuery()->paginate($this->perPage);

        return view('components.tables.table', [
            'data' => $data,
            'table' => $this->getTable(),
        ]);
    }
}