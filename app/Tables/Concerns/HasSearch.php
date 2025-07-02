<?php

namespace App\Tables\Concerns;

trait HasSearch
{
    public function hasSearch(): bool
    {
        // Check if any columns are searchable
        $hasSearchableColumns = collect($this->getTable()->getColumns())
            ->some(fn ($column) => $column->isSearchable());

        // Also check if table has searchable fields defined
        $hasTableSearchableFields = ! empty($this->getTable()->getSearchableColumns());

        return $hasSearchableColumns || $hasTableSearchableFields;
    }

    protected function applySearch($query)
    {
        if (empty($this->search) || strlen($this->search) < $this->searchMinLength) {
            return $query;
        }

        // Get searchable columns from column definitions
        $searchableColumns = collect($this->getTable()->getColumns())
            ->filter(fn ($column) => $column->isSearchable());

        // Get searchable fields from table configuration
        $tableSearchableFields = $this->getTable()->getSearchableColumns();

        return $query->where(function ($query) use ($searchableColumns, $tableSearchableFields) {
            // Apply column-level search
            foreach ($searchableColumns as $column) {
                if ($column->getSearchCallback()) {
                    // Use custom search callback
                    $column->getSearchCallback()($query, $this->search);
                } else {
                    // Default search behavior
                    $query->orWhere($column->getName(), 'like', '%'.$this->search.'%');
                }
            }

            // Apply table-level search
            foreach ($tableSearchableFields as $field) {
                $query->orWhere($field, 'like', '%'.$this->search.'%');
            }
        });
    }
}
