<?php

namespace App\Tables\Concerns;

trait HasSearch
{
    public function hasSearch(): bool
    {
        return collect($this->getTable()->getColumns())
            ->some(fn($column) => $column->isSearchable());
    }

    protected function applySearch($query)
    {
        if (empty($this->search)) {
            return $query;
        }

        $searchableColumns = collect($this->getTable()->getColumns())
            ->filter(fn($column) => $column->isSearchable());

        return $query->where(function ($query) use ($searchableColumns) {
            foreach ($searchableColumns as $column) {
                if ($column->getSearchCallback()) {
                    // Use custom search callback
                    $column->getSearchCallback()($query, $this->search);
                } else {
                    // Default search behavior
                    $query->orWhere($column->getName(), 'like', '%' . $this->search . '%');
                }
            }
        });
    }
}