<?php

namespace App\Tables\Concerns;

trait HasFilters {

    public function hasFilters(): bool
    {
        return method_exists($this->getTable(), 'getFilters') &&
            !empty($this->getTable()->getFilters());
    }
    public function getFilters(): array
    {
        return [];
    }

    public function applyFilters($query)
    {
        foreach ($this->getFilters() as $filter) {
            $query->where($filter['key'], $filter['value']);
        }
        return $query;
    }

    public function getFilterOptions(): array
    {
        return [];
    }

    public function getFilterableColumns(): array
    {
        return [];
    }

    public function removeFilter(string $key): static
    {
        $this->filters = array_filter($this->filters, function ($filter) use ($key) {
            return $filter['key'] !== $key;
        });
        return $this;
    }

    public function addFilter(string $key, string $value): static
    {
        $this->filters[] = compact('key', 'value');
        return $this;
    }

    public function resetFilters(): static
    {
        $this->filters = null;
        return $this;
    }



}