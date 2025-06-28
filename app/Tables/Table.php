<?php

namespace App\Tables;

use Illuminate\Database\Eloquent\Builder;

class Table
{
    protected $query;
    protected array $columns = [];
    protected array $searchableColumns = [];
    protected string $defaultSortField = 'id';
    protected string $defaultSortDirection = 'asc';

    public static function make(): self
    {
        return new static();
    }

    public function query(\Closure $callback): self
    {
        $this->query = $callback;
        return $this;
    }

    public function columns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function searchable(array $columns): self
    {
        $this->searchableColumns = $columns;
        return $this;
    }

    public function defaultSort(string $field, string $direction = 'asc'): self
    {
        $this->defaultSortField = $field;
        $this->defaultSortDirection = $direction;
        return $this;
    }

    // Getters
    public function getQuery(): Builder
    {
        return call_user_func($this->query);
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    public function getDefaultSortField(): string
    {
        return $this->defaultSortField;
    }

    public function getDefaultSortDirection(): string
    {
        return $this->defaultSortDirection;
    }
}