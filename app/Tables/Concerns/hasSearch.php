<?php

namespace App\Tables\Concerns;

trait hasSearch
{
    public function hasSearch(): bool
    {
        return method_exists($this->getTable(), 'getSearchableColumns')
            && !empty($this->getTable()->getSearchableColumns());
    }

    protected function applySearch($query)
    {
        if (empty($this->search)) {
            return $query;
        }

        $searchableColumns = $this->getTable()->getSearchableColumns();

        return $query->where(function ($query) use ($searchableColumns) {
            foreach ($searchableColumns as $column) {
                $query->search($column, $this->search);
            }
        });
    }
}